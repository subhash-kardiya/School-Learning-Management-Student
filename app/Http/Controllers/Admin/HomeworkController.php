<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Homework;
use App\Models\HomeworkSubmission;
use App\Models\Classes;
use App\Models\Section;
use App\Models\Subject;
use App\Models\TeacherMapping;
use App\Models\Student;
use App\Models\ParentModel;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HomeworkController extends Controller
{
    private function hasRole($user, string $role): bool
    {
        return $user && method_exists($user, 'hasRole') && $user->hasRole($role);
    }

    private function isTeacher($user): bool
    {
        return $this->hasRole($user, 'teacher');
    }

    private function isAdminLike($user): bool
    {
        return $this->hasRole($user, 'admin') || $this->hasRole($user, 'superadmin');
    }

    private function hasPermission($user, string $permission): bool
    {
        return $user && method_exists($user, 'hasPermission') && $user->hasPermission($permission);
    }

    private function canManageHomeworkFromAdmin($user): bool
    {
        if (!$user) {
            return false;
        }

        if ($this->isAdminLike($user)) {
            return true;
        }

        // Fallback for custom admin role names that still carry homework permissions.
        return !$this->isTeacher($user) && (
            $this->hasPermission($user, 'homework_list') ||
            $this->hasPermission($user, 'homework_submission')
        );
    }

    private function selectedAcademicYearId(): ?int
    {
        $selected = session('selected_academic_year_id');
        if (!empty($selected)) {
            return (int) $selected;
        }

        return optional(AcademicYear::where('is_active', 1)->first())->id
            ?? optional(AcademicYear::orderBy('name')->latest('id')->first())->id;
    }

    private function teacherScopeClassSectionIds($user, ?int $yearId): array
    {
        if (!$this->isTeacher($user)) {
            return [collect(), collect()];
        }

        $mappingQuery = TeacherMapping::query()
            ->with('section:id,class_id')
            ->where('teacher_id', $user->id);

        if ($yearId) {
            $mappingQuery->whereHas('section.class', function ($q) use ($yearId) {
                $q->where('academic_year_id', $yearId);
            });
        }

        $mappings = $mappingQuery->get();
        $sectionIds = $mappings->pluck('section_id')->filter()->unique()->values();
        $classIds = $mappings->pluck('section.class_id')->filter()->unique()->values();

        return [$classIds, $sectionIds];
    }

    public function create()
    {
        $user = Auth::user();
        if (!$this->isTeacher($user)) {
            abort(403, 'Unauthorized access');
        }

        $selectedYearId = $this->selectedAcademicYearId();
        if (!$selectedYearId) {
            abort(422, 'No active academic year found.');
        }

        $teacherMappings = TeacherMapping::with([
            'section:id,class_id,name',
            'subject:id,class_id,name',
        ])
            ->where('teacher_id', $user->id)
            ->whereHas('section.class', function ($q) use ($selectedYearId) {
                $q->where('academic_year_id', $selectedYearId);
            })
            ->get();

        $mappedClassIds = $teacherMappings
            ->pluck('section.class_id')
            ->filter()
            ->unique()
            ->values();
        $mappedSectionIds = $teacherMappings
            ->pluck('section_id')
            ->filter()
            ->unique()
            ->values();

        $mappedSubjectOptions = $teacherMappings
            ->filter(fn($m) => $m->section && $m->subject)
            ->map(function ($m) {
                return [
                    'section_id' => (int) $m->section_id,
                    'class_id' => (int) $m->section->class_id,
                    'subject_id' => (int) $m->subject_id,
                    'subject_name' => $m->subject->name,
                ];
            })
            ->unique(fn($item) => $item['section_id'] . ':' . $item['subject_id'])
            ->values();

        $classes = Classes::query()
            ->where('academic_year_id', $selectedYearId)
            ->whereIn('id', $mappedClassIds)
            ->orderBy('name')
            ->get();
        $sections = Section::query()
            ->whereIn('id', $mappedSectionIds)
            ->whereIn('class_id', $mappedClassIds)
            ->orderBy('name')
            ->get();
        $selectedYearName = (string) optional(AcademicYear::find($selectedYearId))->name;

        return view('homework.create', compact('classes', 'sections', 'mappedSubjectOptions', 'selectedYearId', 'selectedYearName'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        if (!$this->isTeacher($user)) {
            abort(403, 'Unauthorized access');
        }

        $selectedYearId = $this->selectedAcademicYearId();
        if (!$selectedYearId) {
            abort(422, 'No active academic year found.');
        }

        $validated = $request->validate([
            'class_id' => 'required|exists:classes,id',
            'section_id' => 'required|exists:sections,id',
            'subject_id' => 'required|exists:subjects,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'required|date',
            'action_type' => 'required|in:draft,publish',
        ]);

        if (!Classes::whereKey($validated['class_id'])
            ->where('academic_year_id', $selectedYearId)
            ->exists()) {
            return back()->withErrors(['class_id' => 'Selected class does not belong to active academic year.'])->withInput();
        }
        if (!Section::whereKey($validated['section_id'])->where('class_id', $validated['class_id'])->exists()) {
            return back()->withErrors(['section_id' => 'Selected section is not mapped to selected class.'])->withInput();
        }
        if (!Subject::whereKey($validated['subject_id'])->where('class_id', $validated['class_id'])->exists()) {
            return back()->withErrors(['subject_id' => 'Selected subject is not mapped to selected class.'])->withInput();
        }
        $hasMapping = TeacherMapping::where('teacher_id', $user->id)
            ->where('section_id', $validated['section_id'])
            ->where('subject_id', $validated['subject_id'])
            ->whereHas('section', function ($q) use ($validated) {
                $q->where('class_id', $validated['class_id']);
            })
            ->whereHas('section.class', function ($q) use ($selectedYearId) {
                $q->where('academic_year_id', $selectedYearId);
            })
            ->exists();
        if (!$hasMapping) {
            return back()->withErrors(['subject_id' => 'Selected subject is not mapped to your selected class/section.'])->withInput();
        }

        Homework::create([
            'teacher_id' => $user->id,
            'academic_year_id' => $selectedYearId,
            'class_id' => $validated['class_id'],
            'section_id' => $validated['section_id'],
            'subject_id' => $validated['subject_id'],
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'due_date' => $validated['due_date'],
            'status' => $validated['action_type'] === 'publish' ? 1 : 0,
        ]);

        $message = $validated['action_type'] === 'publish'
            ? 'Homework published successfully.'
            : 'Homework saved as draft.';

        return redirect()->route('teacher.homework.list')->with('success', $message);
    }

    public function list(Request $request)
    {
        $user = Auth::user();
        $yearId = $this->selectedAcademicYearId();
        [$teacherClassIds, $teacherSectionIds] = $this->teacherScopeClassSectionIds($user, $yearId);
        $isAdminLikeUser = $this->canManageHomeworkFromAdmin($user);
        $query = Homework::with(['class', 'section', 'subject', 'teacher', 'submissions'])
            ->when($yearId, fn($q) => $q->where('academic_year_id', $yearId))
            ->latest();

        if ($this->isTeacher($user)) {
            if ($teacherClassIds->isEmpty() || $teacherSectionIds->isEmpty()) {
                $query->whereRaw('1=0');
            } else {
                $query->whereIn('class_id', $teacherClassIds)->whereIn('section_id', $teacherSectionIds);
            }
        }

        if ($request->filled('class_id')) {
            $query->where('class_id', (int) $request->class_id);
        }
        if ($request->filled('q')) {
            $query->where('title', 'like', '%' . trim($request->q) . '%');
        }
        if ($request->filled('section_id')) {
            $query->where('section_id', (int) $request->section_id);
        }

        $homeworks = $query->get();

        $pairs = $homeworks
            ->map(fn($hw) => $hw->class_id . ':' . $hw->section_id)
            ->unique()
            ->values();

        $studentCountMap = [];
        if ($pairs->isNotEmpty()) {
            $classIds = $homeworks->pluck('class_id')->unique()->values();
            $sectionIds = $homeworks->pluck('section_id')->unique()->values();
            $counts = Student::query()
                ->select('class_id', 'section_id', DB::raw('COUNT(*) as total'))
                ->whereIn('class_id', $classIds)
                ->whereIn('section_id', $sectionIds)
                ->groupBy('class_id', 'section_id')
                ->get();

            foreach ($counts as $count) {
                $studentCountMap[$count->class_id . ':' . $count->section_id] = (int) $count->total;
            }
        }

        $today = now()->startOfDay();
        $homeworks = $homeworks->map(function ($hw) use ($studentCountMap, $today, $user, $isAdminLikeUser) {
            $pairKey = $hw->class_id . ':' . $hw->section_id;
            $totalStudents = $studentCountMap[$pairKey] ?? 0;
            $submitted = $hw->submissions->pluck('student_id')->unique()->count();
            $pending = max($totalStudents - $submitted, 0);
            $percent = $totalStudents > 0 ? (int) round(($submitted / $totalStudents) * 100) : 0;
            $isTeacherOwner = $this->isTeacher($user) && (int) $hw->teacher_id === (int) $user->id;

            $hw->total_students = $totalStudents;
            $hw->submitted_count = $submitted;
            $hw->pending_count = $pending;
            $hw->submission_percent = $percent;
            $hw->is_overdue = $hw->due_date && $hw->due_date->lt($today) && (bool) $hw->status;
            $hw->can_toggle = $isAdminLikeUser || $isTeacherOwner;
            $hw->can_delete = $isAdminLikeUser;

            return $hw;
        });
        $classes = Classes::query()
            ->when($yearId, fn($q) => $q->where('academic_year_id', $yearId))
            ->when(
                $this->isTeacher($user),
                fn($q) => $teacherClassIds->isNotEmpty() ? $q->whereIn('id', $teacherClassIds) : $q->whereRaw('1=0')
            )
            ->orderBy('name')
            ->get(['id', 'name']);
        $sections = Section::query()
            ->when($yearId, fn($q) => $q->whereHas('class', fn($cq) => $cq->where('academic_year_id', $yearId)))
            ->when(
                $this->isTeacher($user),
                fn($q) => $teacherSectionIds->isNotEmpty() ? $q->whereIn('id', $teacherSectionIds) : $q->whereRaw('1=0')
            )
            ->orderBy('name')
            ->get(['id', 'class_id', 'name']);

        return view('homework.list', compact('homeworks', 'classes', 'sections'));
    }

    public function destroy($id)
    {
        $homework = Homework::findOrFail($id);
        $user = Auth::user();
        if (!$this->canManageHomeworkFromAdmin($user)) {
            abort(403, 'Unauthorized access');
        }

        $homework->delete();
        return back()->with('success', 'Homework deleted successfully.');
    }

    public function toggleStatus($id)
    {
        $user = Auth::user();
        $homework = Homework::findOrFail($id);
        $isTeacherOwner = $this->isTeacher($user) && (int) $homework->teacher_id === (int) $user->id;
        if (!$this->canManageHomeworkFromAdmin($user) && !$isTeacherOwner) {
            abort(403, 'Unauthorized access');
        }

        $homework->status = !$homework->status;
        $homework->save();

        return back()->with('success', 'Homework status updated.');
    }

    public function submissionReport($id)
    {
        $user = Auth::user();
        $homework = Homework::with(['class', 'section', 'subject', 'teacher'])->findOrFail($id);
        $yearId = $this->selectedAcademicYearId();
        [$teacherClassIds, $teacherSectionIds] = $this->teacherScopeClassSectionIds($user, $yearId);
        if ($this->isTeacher($user)) {
            $allowed = $teacherClassIds->contains((int) $homework->class_id)
                && $teacherSectionIds->contains((int) $homework->section_id);
            if (!$allowed) {
                abort(403, 'Unauthorized access');
            }
        }

        $homeworkQuery = Homework::with(['class', 'section'])
            ->when($yearId, fn($q) => $q->where('academic_year_id', $yearId))
            ->latest();

        if ($this->isTeacher($user)) {
            if ($teacherClassIds->isEmpty() || $teacherSectionIds->isEmpty()) {
                $homeworkQuery->whereRaw('1=0');
            } else {
                $homeworkQuery->whereIn('class_id', $teacherClassIds)->whereIn('section_id', $teacherSectionIds);
            }
        }

        $homeworks = $homeworkQuery->get();
        $pairKeys = $homeworks->map(fn($hw) => $hw->class_id . ':' . $hw->section_id)->unique();
        $studentCountMap = [];
        if ($pairKeys->isNotEmpty()) {
            $classIds = $homeworks->pluck('class_id')->unique()->values();
            $sectionIds = $homeworks->pluck('section_id')->unique()->values();
            $counts = Student::query()
                ->select('class_id', 'section_id', DB::raw('COUNT(*) as total'))
                ->whereIn('class_id', $classIds)
                ->whereIn('section_id', $sectionIds)
                ->groupBy('class_id', 'section_id')
                ->get();
            foreach ($counts as $count) {
                $studentCountMap[$count->class_id . ':' . $count->section_id] = (int) $count->total;
            }
        }

        $homeworks = $homeworks->map(function ($hw) use ($studentCountMap) {
            $pairKey = $hw->class_id . ':' . $hw->section_id;
            $hw->submitted_count = $hw->submissions()->distinct('student_id')->count('student_id');
            $hw->total_students = $studentCountMap[$pairKey] ?? 0;
            return $hw;
        });

        $hasExplicitFilter = request()->hasAny(['class_id', 'section_id', 'homework_id']);
        $selectedClassId = (int) request()->get('class_id', $homework->class_id);
        $selectedSectionId = (int) request()->get('section_id', $homework->section_id);
        $selectedHomeworkId = (int) request()->get('homework_id', $homework->id);

        $filteredHomeworks = $homeworks->filter(function ($hw) use ($selectedClassId, $selectedSectionId) {
            return (int) $hw->class_id === $selectedClassId && (int) $hw->section_id === $selectedSectionId;
        })->values();

        if ($hasExplicitFilter) {
            $requestedInFilter = $filteredHomeworks->firstWhere('id', $selectedHomeworkId);
            $target = $requestedInFilter ?: $filteredHomeworks->first();

            if ($target) {
                $selectedHomeworkId = (int) $target->id;
            }

            if ($target && (int) $target->id !== (int) $homework->id) {
                $routeName = $this->isTeacher($user) ? 'teacher.homework.submission.report' : 'homework.submission.report';
                return redirect()->route($routeName, [
                    'id' => $target->id,
                    'class_id' => $selectedClassId,
                    'section_id' => $selectedSectionId,
                    'homework_id' => $target->id,
                ]);
            }
        } else {
            $selectedClassId = (int) $homework->class_id;
            $selectedSectionId = (int) $homework->section_id;
            $selectedHomeworkId = (int) $homework->id;
            $filteredHomeworks = $homeworks->filter(function ($hw) use ($homework) {
                return (int) $hw->class_id === (int) $homework->class_id
                    && (int) $hw->section_id === (int) $homework->section_id;
            })->values();
        }

        $filterClasses = $homeworks
            ->map(fn($hw) => $hw->class)
            ->filter()
            ->unique('id')
            ->sortBy('name')
            ->values();

        $filterSections = $homeworks
            ->map(fn($hw) => $hw->section)
            ->filter()
            ->unique('id')
            ->sortBy('name')
            ->values();

        $students = Student::with('role')
            ->where('class_id', $homework->class_id)
            ->where('section_id', $homework->section_id)
            ->when($yearId, fn($q) => $q->where('academic_year_id', $yearId))
            ->orderBy('role_id')
            ->orderByRaw('CAST(COALESCE(roll_no, 0) AS UNSIGNED)')
            ->orderBy('student_name')
            ->get();

        $submissionRows = HomeworkSubmission::where('homework_id', $homework->id)->get()->keyBy('student_id');

        $rows = $students->map(function ($student) use ($submissionRows) {
            $submission = $submissionRows->get($student->id);
            return [
                'student' => $student,
                'submitted' => (bool) $submission,
                'submitted_at' => $submission?->submitted_at,
                'roll_no' => $student->roll_no,
                'role_name' => $student->role?->name ?? 'Student',
            ];
        });

        $summary = [
            'total' => $rows->count(),
            'submitted' => $rows->where('submitted', true)->count(),
            'not_submitted' => $rows->where('submitted', false)->count(),
        ];
        $summary['percent'] = $summary['total'] > 0 ? round(($summary['submitted'] / $summary['total']) * 100) : 0;

        return view('homework.report', compact(
            'homework',
            'rows',
            'summary',
            'filterClasses',
            'filterSections',
            'filteredHomeworks',
            'selectedClassId',
            'selectedSectionId',
            'selectedHomeworkId'
        ));
    }

    // Student
    public function studentList()
    {
        $user = Auth::user();
        if (!$user || !$this->hasRole($user, 'student')) {
            abort(403, 'Unauthorized access');
        }

        $student = Student::findOrFail($user->id);
        $yearId = $this->selectedAcademicYearId();
        $homeworks = Homework::with(['subject'])
            ->with(['teacher'])
            ->where('class_id', $student->class_id)
            ->where('section_id', $student->section_id)
            ->where('status', 1)
            ->when($yearId, fn($q) => $q->where('academic_year_id', $yearId))
            ->latest()
            ->get();

        $submissionMap = HomeworkSubmission::where('student_id', $student->id)
            ->get()
            ->keyBy('homework_id');

        return view('homework.list', compact('homeworks', 'student', 'submissionMap'));
    }

    public function submit($homeworkId)
    {
        $user = Auth::user();
        if (!$user || !$this->hasRole($user, 'student')) {
            abort(403, 'Unauthorized access');
        }

        $student = Student::findOrFail($user->id);
        $homework = Homework::findOrFail($homeworkId);
        if ((int) $homework->class_id !== (int) $student->class_id || (int) $homework->section_id !== (int) $student->section_id) {
            abort(403, 'Unauthorized access');
        }

        HomeworkSubmission::updateOrCreate(
            [
                'homework_id' => $homework->id,
                'student_id' => $student->id,
            ],
            [
                'submitted_at' => now(),
                'status' => 'Submitted',
            ]
        );

        return back()->with('success', 'Homework submitted.');
    }

    public function parentList()
    {
        $user = Auth::user();
        if (!$user || !$this->hasRole($user, 'parent')) {
            abort(403, 'Unauthorized access');
        }

        $parent = ParentModel::with('students')->findOrFail($user->id);
        $yearId = $this->selectedAcademicYearId();
        $rows = collect();

        foreach ($parent->students as $student) {
            $homeworks = Homework::query()
                ->with(['teacher', 'subject', 'class', 'section'])
                ->where('class_id', $student->class_id)
                ->where('section_id', $student->section_id)
                ->where('status', 1)
                ->when($yearId, fn($q) => $q->where('academic_year_id', $yearId))
                ->latest()
                ->get();

            $submittedHomeworkIds = HomeworkSubmission::where('student_id', $student->id)
                ->pluck('homework_id')
                ->flip();

            foreach ($homeworks as $hw) {
                $rows->push([
                    'student_name' => $student->student_name,
                    'class_section' => ($hw->class?->name ?? '-') . '-' . ($hw->section?->name ?? '-'),
                    'subject' => $hw->subject?->name ?? '-',
                    'homework_title' => $hw->title,
                    'assigned_by' => $hw->teacher?->name ?? '-',
                    'due_date' => $hw->due_date,
                    'status' => isset($submittedHomeworkIds[$hw->id]) ? 'Yes' : 'No',
                ]);
            }
        }

        $rows = $rows->sortBy([
            ['due_date', 'asc'],
            ['student_name', 'asc'],
        ])->values();

        return view('homework.parent', compact('rows'));
    }
}

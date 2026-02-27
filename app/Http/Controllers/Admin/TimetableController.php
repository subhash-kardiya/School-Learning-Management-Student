<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Timetable;
use App\Models\Classes;
use App\Models\Section;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherMapping;
use App\Models\AcademicYear;
use App\Models\TimetableSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TimetableController extends Controller
{
    private function canPermission(string $permission): bool
    {
        $user = Auth::user();
        if (!$user || !method_exists($user, 'hasPermission')) {
            return false;
        }
        return $user->hasPermission($permission);
    }

    private function hasTimeConflict(array $data, ?int $ignoreId = null): ?string
    {
        $classOverlap = Timetable::query()
            ->where('day_of_week', $data['day_of_week'])
            ->where('class_id', $data['class_id'])
            ->where('section_id', $data['section_id'])
            ->where('academic_year_id', $data['academic_year_id'] ?? null)
            ->where(function ($q) use ($data) {
                $q->where('start_time', '<', $data['end_time'])
                    ->where('end_time', '>', $data['start_time']);
            });

        if ($ignoreId) {
            $classOverlap->where('id', '!=', $ignoreId);
        }

        if ($classOverlap->exists()) {
            return 'Class time clash detected for the selected slot.';
        }

        if (empty($data['teacher_id']) && empty($data['room'])) {
            return null;
        }

        $query = Timetable::query()
            ->where('day_of_week', $data['day_of_week'])
            ->where('academic_year_id', $data['academic_year_id'] ?? null)
            ->where(function ($q) use ($data) {
                if (!empty($data['teacher_id'])) {
                    $q->where('teacher_id', $data['teacher_id']);
                }
                if (!empty($data['room'])) {
                    if (!empty($data['teacher_id'])) {
                        $q->orWhere('room', $data['room']);
                    } else {
                        $q->where('room', $data['room']);
                    }
                }
            })
            ->where(function ($q) use ($data) {
                $q->where('start_time', '<', $data['end_time'])
                    ->where('end_time', '>', $data['start_time']);
            });

        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        $conflict = $query->first();
        if (!$conflict) {
            return null;
        }

        if ($conflict->teacher_id === (int) $data['teacher_id']) {
            return 'Teacher time clash detected for the selected slot.';
        }
        return 'Room time clash detected for the selected slot.';
    }

    private function getSetting(?int $yearId, ?int $classId, ?int $sectionId): ?TimetableSetting
    {
        if (!$yearId || !$classId || !$sectionId) {
            return null;
        }
        return TimetableSetting::where('academic_year_id', $yearId)
            ->where('class_id', $classId)
            ->where('section_id', $sectionId)
            ->first();
    }

    public function classIndex(Request $request)
    {
        if (!$this->canPermission('timetable.manage_all')) {
            abort(403, 'Unauthorized access');
        }

        $query = Timetable::with(['class', 'section', 'subject', 'teacher', 'academicYear'])
            ->latest();

        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }
        if ($request->filled('section_id')) {
            $query->where('section_id', $request->section_id);
        }

        $classes = Classes::with('sections')->get();
        $sections = Section::all();
        $subjects = Subject::orderBy('name')->get();
        $teachers = Teacher::orderBy('name')->get();
        $subjectMappings = TeacherMapping::with(['subject', 'teacher', 'section'])
            ->get()
            ->map(function ($map) {
                return [
                    'section_id' => $map->section_id,
                    'subject_id' => $map->subject_id,
                    'subject_name' => $map->subject?->name,
                    'teacher_id' => $map->teacher_id,
                    'teacher_name' => $map->teacher?->name,
                    'class_id' => $map->section?->class_id,
                ];
            });
        $academicYears = AcademicYear::where('is_active', 1)->orderBy('name')->get();

        return view('timetable.class', compact(
            'classes',
            'sections',
            'subjects',
            'teachers',
            'academicYears',
            'subjectMappings'
        ));
    }

    public function store(Request $request)
    {
        if (!$this->canPermission('timetable.manage_all')) {
            abort(403, 'Unauthorized access');
        }

        $payload = $request->validate([
            'class_id' => 'required|exists:classes,id',
            'section_id' => 'required|exists:sections,id',
            'subject_id' => 'required|exists:subjects,id',
            'teacher_id' => 'nullable|exists:teachers,id',
            'academic_year_id' => 'nullable|exists:academic_years,id',
            'day_of_week' => 'required|string',
            'start_time' => 'required',
            'end_time' => 'required',
            'room' => 'nullable|string|max:50',
            'status' => 'required',
        ]);

        $payload['status'] = filter_var($payload['status'], FILTER_VALIDATE_BOOLEAN) ? 1 : 0;

        $setting = $this->getSetting(
            (int) ($payload['academic_year_id'] ?? 0),
            (int) $payload['class_id'],
            (int) $payload['section_id']
        );
        if (!$setting) {
            return back()->withErrors(['schedule' => 'Please configure time slots first.'])->withInput();
        }
        if ($setting->status === 'published') {
            abort(403, 'Timetable is published and locked.');
        }

        $payload['type'] = 'lecture';
        $subject = Subject::find($payload['subject_id']);
        if (!$subject || (int) $subject->class_id !== (int) $payload['class_id']) {
            return back()->withErrors(['subject_id' => 'Subject must be mapped to the selected class.'])->withInput();
        }
        $mapping = TeacherMapping::where('section_id', $payload['section_id'])
            ->where('subject_id', $payload['subject_id'])
            ->first();
        if (!$mapping) {
            return back()->withErrors(['subject_id' => 'No teacher mapping found for this subject/section.'])->withInput();
        }
        $payload['teacher_id'] = $mapping->teacher_id;

        $slots = collect($setting->slots ?? [])->values();
        $slotIndex = $slots->search(function ($slot) use ($payload) {
            $s1 = date('H:i', strtotime($slot['start'] ?? ''));
            $s2 = date('H:i', strtotime($payload['start_time']));
            $e1 = date('H:i', strtotime($slot['end'] ?? ''));
            $e2 = date('H:i', strtotime($payload['end_time']));
            return $s1 === $s2 && $e1 === $e2;
        });
        if ($slotIndex === false) {
            return back()->withErrors(['schedule' => 'Selected time slot is not in schedule settings.'])->withInput();
        }

        $slot = $slots[$slotIndex] ?? null;
        if (!$slot || ($slot['type'] ?? 'period') !== 'period') {
            return back()->withErrors(['schedule' => 'Selected slot must be a period.'])->withInput();
        }

        $payload['start_time'] = $slot['start'];
        $payload['end_time'] = $slot['end'];
        $conflictMessage = $this->hasTimeConflict($payload);
        if ($conflictMessage) {
            return back()->withErrors(['schedule' => $conflictMessage])->withInput();
        }

        Timetable::create($payload);

        return redirect()->route('timetable.class')->with('success', 'Timetable created.');
    }

    public function edit($id)
    {
        if (!$this->canPermission('timetable.manage_all')) {
            abort(403, 'Unauthorized access');
        }

        $timetable = Timetable::findOrFail($id);
        $classes = Classes::with('sections')->get();
        $sections = Section::all();
        $subjects = Subject::orderBy('name')->get();
        $teachers = Teacher::orderBy('name')->get();
        $subjectMappings = TeacherMapping::with(['subject', 'teacher', 'section'])
            ->get()
            ->map(function ($map) {
                return [
                    'section_id' => $map->section_id,
                    'subject_id' => $map->subject_id,
                    'subject_name' => $map->subject?->name,
                    'teacher_id' => $map->teacher_id,
                    'teacher_name' => $map->teacher?->name,
                    'class_id' => $map->section?->class_id,
                ];
            });
        $academicYears = AcademicYear::where('is_active', 1)->orderBy('name')->get();

        return view('timetable.edit', compact(
            'timetable',
            'classes',
            'sections',
            'subjects',
            'teachers',
            'academicYears',
            'subjectMappings'
        ));
    }

    public function update(Request $request, $id)
    {
        if (!$this->canPermission('timetable.manage_all')) {
            abort(403, 'Unauthorized access');
        }

        $payload = $request->validate([
            'class_id' => 'required|exists:classes,id',
            'section_id' => 'required|exists:sections,id',
            'subject_id' => 'required|exists:subjects,id',
            'teacher_id' => 'nullable|exists:teachers,id',
            'academic_year_id' => 'nullable|exists:academic_years,id',
            'day_of_week' => 'required|string',
            'start_time' => 'required',
            'end_time' => 'required',
            'room' => 'nullable|string|max:50',
            'status' => 'required',
        ]);

        $payload['status'] = filter_var($payload['status'], FILTER_VALIDATE_BOOLEAN) ? 1 : 0;
        $payload['start_time'] = date('H:i', strtotime($payload['start_time']));
        $payload['end_time'] = date('H:i', strtotime($payload['end_time']));

        $setting = $this->getSetting(
            (int) ($payload['academic_year_id'] ?? 0),
            (int) $payload['class_id'],
            (int) $payload['section_id']
        );
        if (!$setting) {
            return back()->withErrors(['schedule' => 'Please configure time slots first.'])->withInput();
        }
        if ($setting->status === 'published') {
            abort(403, 'Timetable is published and locked.');
        }

        $payload['type'] = 'lecture';
        $subject = Subject::find($payload['subject_id']);
        if (!$subject || (int) $subject->class_id !== (int) $payload['class_id']) {
            return back()->withErrors(['subject_id' => 'Subject must be mapped to the selected class.'])->withInput();
        }
        $mapping = TeacherMapping::where('section_id', $payload['section_id'])
            ->where('subject_id', $payload['subject_id'])
            ->first();
        if (!$mapping) {
            return back()->withErrors(['subject_id' => 'No teacher mapping found for this subject/section.'])->withInput();
        }
        $payload['teacher_id'] = $mapping->teacher_id;

        $conflictMessage = $this->hasTimeConflict($payload, (int) $id);
        if ($conflictMessage) {
            return back()->withErrors(['schedule' => $conflictMessage])->withInput();
        }

        $timetable = Timetable::findOrFail($id);
        $timetable->update($payload);

        return redirect()->route('timetable.class')->with('success', 'Timetable updated.');
    }

    public function destroy($id)
    {
        if (!$this->canPermission('timetable.manage_all')) {
            abort(403, 'Unauthorized access');
        }

        $timetable = Timetable::findOrFail($id);
        $setting = $this->getSetting($timetable->academic_year_id, $timetable->class_id, $timetable->section_id);
        if ($setting && $setting->status === 'published') {
            abort(403, 'Timetable is published and locked.');
        }

        $timetable->delete();
        return redirect()->route('timetable.class')->with('success', 'Timetable deleted.');
    }

    public function teacherIndex()
    {
        if (!$this->canPermission('timetable.view_own')) {
            abort(403, 'Unauthorized access');
        }

        $teacherId = Auth::id();
        if (!$teacherId && session('role') === 'teacher') {
            $teacherId = session('auth_id');
        }
        $isAdminView = $this->canPermission('timetable.manage_all');

        $timetables = Timetable::with(['class', 'section', 'subject'])
            ->latest();
        if (!$isAdminView) {
            $timetables->where('teacher_id', $teacherId);
        }
        $timetables = $timetables->get();

        $classIds = $timetables->pluck('class_id')->unique()->filter();
        $sectionIds = $timetables->pluck('section_id')->unique()->filter();
        $classes = $classIds->isEmpty() ? collect() : Classes::whereIn('id', $classIds)->get();
        $sections = $sectionIds->isEmpty() ? collect() : Section::whereIn('id', $sectionIds)->get();

        $teachers = $isAdminView
            ? Teacher::orderBy('name')->get()
            : Teacher::where('id', $teacherId)->get();
        $defaultTeacherId = $isAdminView ? null : $teacherId;

        return view('timetable.teacher', compact('timetables', 'classes', 'sections', 'teachers', 'defaultTeacherId'));
    }

    public function studentIndex()
    {
        if (!$this->canPermission('timetable.view_class')) {
            abort(403, 'Unauthorized access');
        }

        $student = Auth::user();
        if (!$student) {
            abort(403, 'Unauthorized access');
        }

        return view('timetable.student');
    }

    public function parentIndex(Request $request)
    {
        if (!$this->canPermission('timetable.view_child')) {
            abort(403, 'Unauthorized access');
        }

        $parent = Auth::user();
        if (!$parent || !method_exists($parent, 'students')) {
            abort(403, 'Unauthorized access');
        }

        $students = $parent->students()->get();
        $selectedStudentId = (int) $request->get('student_id');
        if ($selectedStudentId && $students->where('id', $selectedStudentId)->isEmpty()) {
            abort(403, 'Unauthorized access');
        }

        if (!$selectedStudentId && $students->count() > 0) {
            $selectedStudentId = (int) $students->first()->id;
        }

        return view('timetable.parent', compact('students', 'selectedStudentId'));
    }

    public function data(Request $request)
    {
        if (!$this->canPermission('timetable.manage_all')) {
            abort(403, 'Unauthorized access');
        }

        $query = Timetable::with(['class', 'section', 'subject', 'teacher', 'academicYear'])
            ->latest();

        if (!$request->filled('class_id') || !$request->filled('section_id') || !$request->filled('academic_year_id')) {
            return response()->json(['entries' => [], 'settings' => null]);
        }

        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }
        if ($request->filled('section_id')) {
            $query->where('section_id', $request->section_id);
        }
        if ($request->filled('teacher_id')) {
            $query->where('teacher_id', $request->teacher_id);
        }
        if ($request->filled('academic_year_id')) {
            $query->where('academic_year_id', $request->academic_year_id);
        }

        $canEdit = $this->canPermission('timetable.manage_all');
        $canDelete = $this->canPermission('timetable.manage_all');

        $rows = $query->get()->map(function ($row) use ($canEdit, $canDelete) {
            $row->can_edit = $canEdit;
            $row->can_delete = $canDelete;
            $row->edit_url = $canEdit ? route('timetable.edit', $row->id) : null;
            $row->update_url = $canEdit ? route('timetable.update', $row->id) : null;
            $row->delete_url = $canDelete ? route('timetable.destroy', $row->id) : null;
            return $row;
        });

        $setting = $this->getSetting((int) $request->academic_year_id, (int) $request->class_id, (int) $request->section_id);
        return response()->json([
            'entries' => $rows,
            'settings' => $setting,
        ]);
    }

    public function saveSettings(Request $request)
    {
        if (!$this->canPermission('timetable.manage_all')) {
            abort(403, 'Unauthorized access');
        }

        $data = $request->validateWithBag('settings', [
            'academic_year_id' => 'required|exists:academic_years,id',
            'class_id' => 'required|exists:classes,id',
            'section_id' => 'required|exists:sections,id',
            'days' => 'required|array|min:1',
            'days.*' => 'string',
            'slots' => 'required|array|min:1',
            'slots.*.start' => 'required|string',
            'slots.*.end' => 'required|string',
            'slots.*.type' => 'required|in:period,break,lunch',
            'status' => 'required|in:draft,published',
        ]);

        $setting = $this->getSetting((int) $data['academic_year_id'], (int) $data['class_id'], (int) $data['section_id']);
        if ($setting && $setting->status === 'published') {
            abort(403, 'Timetable is published and locked.');
        }

        $slotKeys = collect($data['slots'])->map(function ($slot) {
            return ($slot['start'] ?? '') . '-' . ($slot['end'] ?? '');
        })->filter()->values();

        $outOfRange = Timetable::where('academic_year_id', $data['academic_year_id'])
            ->where('class_id', $data['class_id'])
            ->where('section_id', $data['section_id'])
            ->get()
            ->filter(function ($row) use ($slotKeys) {
                $key = $row->start_time . '-' . $row->end_time;
                return !$slotKeys->contains($key);
            });

        if ($outOfRange->isNotEmpty()) {
            $ids = $outOfRange->pluck('id')->all();
            Timetable::whereIn('id', $ids)->delete();
        }

        TimetableSetting::updateOrCreate(
            [
                'academic_year_id' => $data['academic_year_id'],
                'class_id' => $data['class_id'],
                'section_id' => $data['section_id'],
            ],
            [
                'days' => $data['days'],
                'slots' => $data['slots'],
                'status' => $data['status'],
            ]
        );

        return redirect()->route('timetable.class')->with('success', 'Time slots saved.');
    }

    public function teacherData(Request $request)
    {
        if (!$this->canPermission('timetable.view_own')) {
            abort(403, 'Unauthorized access');
        }

        $teacherId = Auth::id();
        if (!$teacherId && session('role') === 'teacher') {
            $teacherId = session('auth_id');
        }
        $isAdminView = $this->canPermission('timetable.manage_all');

        $query = Timetable::with(['class', 'section', 'subject', 'teacher', 'academicYear'])
            ->where('status', 1)
            ->latest();

        if ($isAdminView) {
            if ($request->filled('teacher_id')) {
                $query->where('teacher_id', $request->teacher_id);
            }
        } else {
            $query->where('teacher_id', $teacherId);
        }

        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }
        if ($request->filled('section_id')) {
            $query->where('section_id', $request->section_id);
        }

        $rows = $query->get()->map(function ($row) {
            $row->can_edit = false;
            $row->can_delete = false;
            return $row;
        });

        $first = $rows->first();
        $setting = $first
            ? $this->getSetting($first->academic_year_id, $first->class_id, $first->section_id)
            : null;

        return response()->json([
            'entries' => $rows,
            'settings' => $setting,
        ]);
    }

    public function studentData()
    {
        if (!$this->canPermission('timetable.view_class')) {
            abort(403, 'Unauthorized access');
        }

        $student = Auth::user();
        if (!$student) {
            abort(403, 'Unauthorized access');
        }

        $query = Timetable::with(['class', 'section', 'subject', 'teacher', 'academicYear'])
            ->where('status', 1)
            ->latest();

        if (!empty($student->class_id)) {
            $query->where('class_id', $student->class_id);
        }
        if (!empty($student->section_id)) {
            $query->where('section_id', $student->section_id);
        }

        $rows = $query->get()->map(function ($row) {
            $row->can_edit = false;
            $row->can_delete = false;
            return $row;
        });

        $first = $rows->first();
        $setting = $first
            ? $this->getSetting($first->academic_year_id, $first->class_id, $first->section_id)
            : null;

        return response()->json([
            'entries' => $rows,
            'settings' => $setting,
        ]);
    }

    public function parentData(Request $request)
    {
        if (!$this->canPermission('timetable.view_child')) {
            abort(403, 'Unauthorized access');
        }

        $parent = Auth::user();
        if (!$parent || !method_exists($parent, 'students')) {
            abort(403, 'Unauthorized access');
        }

        $students = $parent->students()->get();
        $selectedStudentId = (int) $request->get('student_id');
        if (!$selectedStudentId && $students->count() > 0) {
            $selectedStudentId = (int) $students->first()->id;
        }
        if (!$selectedStudentId || $students->where('id', $selectedStudentId)->isEmpty()) {
            return response()->json([]);
        }

        $student = $students->where('id', $selectedStudentId)->first();
        $query = Timetable::with(['class', 'section', 'subject', 'teacher', 'academicYear'])
            ->where('status', 1)
            ->latest();

        if (!empty($student->class_id)) {
            $query->where('class_id', $student->class_id);
        }
        if (!empty($student->section_id)) {
            $query->where('section_id', $student->section_id);
        }

        $rows = $query->get()->map(function ($row) {
            $row->can_edit = false;
            $row->can_delete = false;
            return $row;
        });

        $first = $rows->first();
        $setting = $first
            ? $this->getSetting($first->academic_year_id, $first->class_id, $first->section_id)
            : null;

        return response()->json([
            'entries' => $rows,
            'settings' => $setting,
        ]);
    }
}

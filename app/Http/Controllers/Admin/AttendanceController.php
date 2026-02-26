<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Classes;
use App\Models\Section;
use App\Models\Student;
use App\Models\ParentModel;
use App\Models\TeacherMapping;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;

class AttendanceController extends Controller
{
    private function applyStudentOrdering(Builder $query): Builder
    {
        // Keep numeric roll numbers in natural order, then fallback to lexical roll/name.
        return $query
            ->orderByRaw("CASE WHEN roll_no REGEXP '^[0-9]+$' THEN CAST(roll_no AS UNSIGNED) ELSE 999999 END ASC")
            ->orderBy('roll_no')
            ->orderBy('student_name');
    }

    private function canPermission(string $permission): bool
    {
        $user = Auth::user();
        if (!$user || !method_exists($user, 'hasPermission')) {
            return false;
        }
        return $user->hasPermission($permission);
    }

    private function isTeacher($user): bool
    {
        return $user && method_exists($user, 'hasRole') && $user->hasRole('teacher');
    }

    private function isStudent($user): bool
    {
        return $user && method_exists($user, 'hasRole') && $user->hasRole('student');
    }

    private function isParent($user): bool
    {
        return $user && method_exists($user, 'hasRole') && $user->hasRole('parent');
    }

    private function getTeacherSections($teacherId): Collection
    {
        return Section::whereIn('id', TeacherMapping::where('teacher_id', $teacherId)->pluck('section_id'))
            ->with('class')
            ->whereHas('class', function($q) {
                $q->when(session('selected_academic_year_id'), function($sq) {
                    $sq->where('academic_year_id', session('selected_academic_year_id'));
                });
            })
            ->get();
    }

    private function monthContext(int $month, int $year): array
    {
        $start = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $days = [];
        for ($day = 1; $day <= $start->daysInMonth; $day++) {
            $date = $start->copy()->day($day);
            $days[] = [
                'day' => $day,
                'date' => $date->toDateString(),
                'label' => $date->format('d M'),
                'isWeekend' => $date->isSunday(),
                'isToday' => $date->isToday(),
            ];
        }
        return [
            'start' => $start,
            'end' => $start->copy()->endOfMonth(),
            'days' => $days,
        ];
    }

    private function buildMonthlyData(Collection $students, int $month, int $year): array
    {
        $context = $this->monthContext($month, $year);
        $start = $context['start'];
        $end = $context['end'];
        $attendanceMap = [];
        $counts = [
            'present' => 0,
            'absent' => 0,
        ];

        if ($students->isNotEmpty()) {
            $records = Attendance::whereBetween('date', [$start->toDateString(), $end->toDateString()])
                ->whereIn('student_id', $students->pluck('id'))
                ->get();

            foreach ($records as $record) {
                $attendanceMap[$record->student_id][$record->date] = $record->status;
                if (isset($counts[$record->status])) {
                    $counts[$record->status]++;
                }
            }
        }

        return [
            'days' => $context['days'],
            'start' => $start,
            'end' => $end,
            'attendanceMap' => $attendanceMap,
            'counts' => $counts,
        ];
    }

    public function markForm(Request $request)
    {
        if (!$this->canPermission('attendance_mark')) {
            abort(403, 'Unauthorized access');
        }

        $request->validate([
            'month' => 'nullable|integer|min:1|max:12',
            'year' => 'nullable|integer|min:2000|max:2100',
            'class_id' => 'nullable|exists:classes,id',
            'section_id' => 'nullable|exists:sections,id',
        ]);

        $user = Auth::user();
        $month = (int) $request->get('month', Carbon::today()->month);
        $year = (int) $request->get('year', Carbon::today()->year);
        $selectedClass = $request->get('class_id');
        $selectedSection = $request->get('section_id');

        if ($this->isTeacher($user)) {
            $sections = $this->getTeacherSections($user->id);
            $classes = $sections->pluck('class')->filter()->unique('id')->values();
        } else {
            $classes = Classes::with('sections')
                ->when(session('selected_academic_year_id'), function($q) {
                    $q->where('academic_year_id', session('selected_academic_year_id'));
                })
                ->get();
            $sections = Section::whereHas('class', function($q) {
                $q->when(session('selected_academic_year_id'), function($sq) {
                    $sq->where('academic_year_id', session('selected_academic_year_id'));
                });
            })->get();
        }

        if (!$selectedClass && $classes->isNotEmpty()) {
            $defaultClass = $classes->firstWhere('name', 'Class 1') ?? $classes->firstWhere('id', 1) ?? $classes->first();
            $selectedClass = $defaultClass?->id;
        }

        if (!$selectedSection && $selectedClass) {
            $preferredSection = $sections
                ->where('class_id', $selectedClass)
                ->firstWhere('name', 'A');
            $selectedSection = $preferredSection?->id
                ?? $sections->where('class_id', $selectedClass)->first()?->id;
        }

        $students = collect();

        if ($selectedClass && $selectedSection) {
            $students = $this->applyStudentOrdering(Student::where('class_id', $selectedClass)
                ->where('section_id', $selectedSection)
            )->get();
        }

        $monthlyData = $this->buildMonthlyData($students, $month, $year);
        $daysInMonth = count($monthlyData['days']);
        $totalSlots = $daysInMonth * max($students->count(), 1);
        $presentCount = $monthlyData['counts']['present'];
        $percent = $totalSlots > 0 ? round(($presentCount / $totalSlots) * 100, 1) : 0;

        return view('attendance.mark', compact(
            'classes',
            'sections',
            'students',
            'selectedClass',
            'selectedSection',
            'month',
            'year',
            'monthlyData',
            'percent'
        ));
    }

    public function markSave(Request $request)
    {
        if (!$this->canPermission('attendance_mark')) {
            abort(403, 'Unauthorized access');
        }

        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'section_id' => 'required|exists:sections,id',
            'date' => 'required|date',
            'attendance' => 'required|array',
            'attendance.*' => 'required|in:present,absent',
        ]);

        $date = $request->date;
        foreach ($request->attendance as $studentId => $status) {
            Attendance::updateOrCreate(
                [
                    'student_id' => $studentId,
                    'date' => $date,
                ],
                [
                    'status' => $status,
                ]
            );
        }

        return redirect()->back()->with('success', 'Attendance saved successfully.');
    }

    public function grid(Request $request)
    {
        if (!$this->canPermission('attendance_mark')) {
            abort(403, 'Unauthorized access');
        }

        $request->validate([
            'month' => 'nullable|integer|min:1|max:12',
            'year' => 'nullable|integer|min:2000|max:2100',
            'class_id' => 'nullable|exists:classes,id',
            'section_id' => 'nullable|exists:sections,id',
        ]);

        $user = Auth::user();
        $month = (int) $request->get('month', Carbon::today()->month);
        $year = (int) $request->get('year', Carbon::today()->year);
        $classId = $request->get('class_id');
        $sectionId = $request->get('section_id');

        if ($this->isTeacher($user)) {
            $allowedSections = $this->getTeacherSections($user->id)->pluck('id')->toArray();
            if ($sectionId && !in_array((int) $sectionId, $allowedSections, true)) {
                return response()->json([
                    'summary' => view('attendance.partials.monthly-summary', [
                        'daysInMonth' => 0,
                        'counts' => ['present' => 0, 'absent' => 0],
                        'percent' => 0,
                    ])->render(),
                    'html' => view('attendance.partials.monthly-grid-empty')->render(),
                ]);
            }
        }

        $students = collect();
        if ($classId && $sectionId) {
            $students = $this->applyStudentOrdering(Student::where('class_id', $classId)
                ->where('section_id', $sectionId)
            )->get();
        }

        $monthlyData = $this->buildMonthlyData($students, $month, $year);
        $daysInMonth = count($monthlyData['days']);
        $totalSlots = $daysInMonth * max($students->count(), 1);
        $presentCount = $monthlyData['counts']['present'];
        $percent = $totalSlots > 0 ? round(($presentCount / $totalSlots) * 100, 1) : 0;

        $summaryHtml = view('attendance.partials.monthly-summary', [
            'daysInMonth' => $daysInMonth,
            'counts' => $monthlyData['counts'],
            'percent' => $percent,
        ])->render();

        $gridHtml = view('attendance.partials.monthly-grid', [
            'students' => $students,
            'days' => $monthlyData['days'],
            'attendanceMap' => $monthlyData['attendanceMap'],
            'editableDate' => Carbon::today()->toDateString(),
            'canEdit' => true,
        ])->render();

        return response()->json([
            'summary' => $summaryHtml,
            'html' => $gridHtml,
        ]);
    }

    public function updateCell(Request $request)
    {
        if (!$this->canPermission('attendance_mark')) {
            abort(403, 'Unauthorized access');
        }

        $request->validate([
            'student_id' => 'required|exists:students,id',
            'date' => 'required|date',
            'status' => 'required|in:present,absent',
        ]);

        $user = Auth::user();
        $date = Carbon::parse($request->date)->toDateString();
        if ($this->isTeacher($user) && $date !== Carbon::today()->toDateString()) {
            return response()->json(['message' => 'Only same-day attendance is editable.'], 403);
        }

        $attendance = Attendance::updateOrCreate(
            [
                'student_id' => $request->student_id,
                'date' => $date,
            ],
            [
                'status' => $request->status,
            ]
        );

        return response()->json([
            'message' => 'Attendance saved successfully.',
            'status' => $attendance->status,
        ]);
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        if ($user && method_exists($user, 'hasRole') && $user->hasRole(['student', 'parent', 'teacher'])) {
            abort(403, 'Unauthorized access');
        }
        if (!$this->canPermission('attendance_view')) {
            abort(403, 'Unauthorized access');
        }

        $request->validate([
            'month' => 'nullable|integer|min:1|max:12',
            'year' => 'nullable|integer|min:2000|max:2100',
            'class_id' => 'nullable|exists:classes,id',
            'section_id' => 'nullable|exists:sections,id',
        ]);

        $classes = Classes::with('sections')
            ->when(session('selected_academic_year_id'), function($q) {
                $q->where('academic_year_id', session('selected_academic_year_id'));
            })
            ->get();
        $sections = Section::whereHas('class', function($q) {
            $q->when(session('selected_academic_year_id'), function($sq) {
                $sq->where('academic_year_id', session('selected_academic_year_id'));
            });
        })->get();
        $month = (int) $request->get('month', Carbon::today()->month);
        $year = (int) $request->get('year', Carbon::today()->year);
        $selectedClass = $request->get('class_id');
        $selectedSection = $request->get('section_id');

        if (!$selectedClass && $classes->isNotEmpty()) {
            $defaultClass = $classes->firstWhere('name', 'Class 1') ?? $classes->firstWhere('id', 1) ?? $classes->first();
            $selectedClass = $defaultClass?->id;
        }

        if (!$selectedSection && $selectedClass) {
            $preferredSection = $sections
                ->where('class_id', $selectedClass)
                ->firstWhere('name', 'A');
            $selectedSection = $preferredSection?->id
                ?? $sections->where('class_id', $selectedClass)->first()?->id;
        }

        $students = collect();
        if ($selectedClass && $selectedSection) {
            $students = $this->applyStudentOrdering(Student::where('class_id', $selectedClass)
                ->where('section_id', $selectedSection)
            )->get();
        }

        $monthlyData = $this->buildMonthlyData($students, $month, $year);
        $daysInMonth = count($monthlyData['days']);
        $totalSlots = $daysInMonth * max($students->count(), 1);
        $presentCount = $monthlyData['counts']['present'];
        $percent = $totalSlots > 0 ? round(($presentCount / $totalSlots) * 100, 1) : 0;

        return view('attendance.index', compact(
            'classes',
            'sections',
            'students',
            'selectedClass',
            'selectedSection',
            'month',
            'year',
            'monthlyData',
            'percent'
        ));
    }

    public function reportGrid(Request $request)
    {
        $user = Auth::user();
        if ($user && method_exists($user, 'hasRole') && $user->hasRole(['student', 'parent', 'teacher'])) {
            abort(403, 'Unauthorized access');
        }
        if (!$this->canPermission('attendance_view')) {
            abort(403, 'Unauthorized access');
        }

        $request->validate([
            'month' => 'nullable|integer|min:1|max:12',
            'year' => 'nullable|integer|min:2000|max:2100',
            'class_id' => 'nullable|exists:classes,id',
            'section_id' => 'nullable|exists:sections,id',
        ]);

        $month = (int) $request->get('month', Carbon::today()->month);
        $year = (int) $request->get('year', Carbon::today()->year);
        $classId = $request->get('class_id');
        $sectionId = $request->get('section_id');

        $students = collect();
        if ($classId && $sectionId) {
            $students = $this->applyStudentOrdering(Student::where('class_id', $classId)
                ->where('section_id', $sectionId)
            )->get();
        }

        $monthlyData = $this->buildMonthlyData($students, $month, $year);
        $daysInMonth = count($monthlyData['days']);
        $totalSlots = $daysInMonth * max($students->count(), 1);
        $presentCount = $monthlyData['counts']['present'];
        $percent = $totalSlots > 0 ? round(($presentCount / $totalSlots) * 100, 1) : 0;

        $summaryHtml = view('attendance.partials.monthly-summary', [
            'daysInMonth' => $daysInMonth,
            'counts' => $monthlyData['counts'],
            'percent' => $percent,
        ])->render();

        $gridHtml = view('attendance.partials.monthly-grid', [
            'students' => $students,
            'days' => $monthlyData['days'],
            'attendanceMap' => $monthlyData['attendanceMap'],
            'editableDate' => Carbon::today()->toDateString(),
            'canEdit' => false,
        ])->render();

        return response()->json([
            'summary' => $summaryHtml,
            'html' => $gridHtml,
        ]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        if ($user && method_exists($user, 'hasRole') && $user->hasRole(['student', 'parent', 'teacher'])) {
            abort(403, 'Unauthorized access');
        }
        if (!$this->canPermission('attendance_report')) {
            abort(403, 'Unauthorized access');
        }

        $request->validate([
            'attendance_id' => 'required|exists:attendances,id',
            'status' => 'required|in:present,absent',
        ]);

        $attendance = Attendance::findOrFail($request->attendance_id);
        $attendance->update(['status' => $request->status]);

        return back()->with('success', 'Attendance updated.');
    }

    public function studentView()
    {
        $user = Auth::user();
        if (!$user || !method_exists($user, 'hasRole') || !$user->hasRole('student')) {
            abort(403, 'Unauthorized access');
        }

        $student = Student::findOrFail($user->id);
        $month = (int) request()->get('month', Carbon::today()->month);
        $year = (int) request()->get('year', Carbon::today()->year);
        $students = collect([$student]);
        $monthlyData = $this->buildMonthlyData($students, $month, $year);
        $daysInMonth = count($monthlyData['days']);
        $presentCount = $monthlyData['counts']['present'];
        $percent = $daysInMonth > 0 ? round(($presentCount / $daysInMonth) * 100, 1) : 0;

        return view('attendance.student', compact('student', 'month', 'year', 'monthlyData', 'percent'));
    }

    public function parentView()
    {
        $user = Auth::user();
        if (!$user || !method_exists($user, 'hasRole') || !$user->hasRole('parent')) {
            abort(403, 'Unauthorized access');
        }

        $parent = ParentModel::with('students')->findOrFail($user->id);
        $children = $parent->students;
        $month = (int) request()->get('month', Carbon::today()->month);
        $year = (int) request()->get('year', Carbon::today()->year);
        $selectedStudentId = (int) request()->get('student_id', $children->first()?->id);
        $selectedStudent = $children->firstWhere('id', $selectedStudentId);
        $monthlyData = $selectedStudent ? $this->buildMonthlyData(collect([$selectedStudent]), $month, $year) : null;
        $daysInMonth = $monthlyData ? count($monthlyData['days']) : 0;
        $presentCount = $monthlyData ? $monthlyData['counts']['present'] : 0;
        $percent = $daysInMonth > 0 ? round(($presentCount / $daysInMonth) * 100, 1) : 0;

        return view('attendance.parent', compact('parent', 'children', 'selectedStudent', 'selectedStudentId', 'month', 'year', 'monthlyData', 'percent'));
    }

    public function studentGrid(Request $request)
    {
        $user = Auth::user();
        if (!$this->isStudent($user)) {
            abort(403, 'Unauthorized access');
        }

        $request->validate([
            'month' => 'nullable|integer|min:1|max:12',
            'year' => 'nullable|integer|min:2000|max:2100',
        ]);

        $student = Student::findOrFail($user->id);
        $month = (int) $request->get('month', Carbon::today()->month);
        $year = (int) $request->get('year', Carbon::today()->year);
        $monthlyData = $this->buildMonthlyData(collect([$student]), $month, $year);
        $daysInMonth = count($monthlyData['days']);
        $presentCount = $monthlyData['counts']['present'];
        $percent = $daysInMonth > 0 ? round(($presentCount / $daysInMonth) * 100, 1) : 0;

        $summaryHtml = view('attendance.partials.monthly-summary', [
            'daysInMonth' => $daysInMonth,
            'counts' => $monthlyData['counts'],
            'percent' => $percent,
        ])->render();

        $gridHtml = view('attendance.partials.monthly-grid', [
            'students' => collect([$student]),
            'days' => $monthlyData['days'],
            'attendanceMap' => $monthlyData['attendanceMap'],
            'editableDate' => Carbon::today()->toDateString(),
            'canEdit' => false,
        ])->render();

        return response()->json([
            'summary' => $summaryHtml,
            'html' => $gridHtml,
        ]);
    }

    public function parentGrid(Request $request)
    {
        $user = Auth::user();
        if (!$this->isParent($user)) {
            abort(403, 'Unauthorized access');
        }

        $request->validate([
            'month' => 'nullable|integer|min:1|max:12',
            'year' => 'nullable|integer|min:2000|max:2100',
            'student_id' => 'nullable|exists:students,id',
        ]);

        $parent = ParentModel::with('students')->findOrFail($user->id);
        $children = $parent->students;
        $selectedStudentId = (int) $request->get('student_id', $children->first()?->id);
        $selectedStudent = $children->firstWhere('id', $selectedStudentId);
        if (!$selectedStudent) {
            return response()->json([
                'summary' => view('attendance.partials.monthly-summary', [
                    'daysInMonth' => 0,
                    'counts' => ['present' => 0, 'absent' => 0],
                    'percent' => 0,
                ])->render(),
                'html' => view('attendance.partials.monthly-grid-empty')->render(),
            ]);
        }

        $month = (int) $request->get('month', Carbon::today()->month);
        $year = (int) $request->get('year', Carbon::today()->year);
        $monthlyData = $this->buildMonthlyData(collect([$selectedStudent]), $month, $year);
        $daysInMonth = count($monthlyData['days']);
        $presentCount = $monthlyData['counts']['present'];
        $percent = $daysInMonth > 0 ? round(($presentCount / $daysInMonth) * 100, 1) : 0;

        $summaryHtml = view('attendance.partials.monthly-summary', [
            'daysInMonth' => $daysInMonth,
            'counts' => $monthlyData['counts'],
            'percent' => $percent,
        ])->render();

        $gridHtml = view('attendance.partials.monthly-grid', [
            'students' => collect([$selectedStudent]),
            'days' => $monthlyData['days'],
            'attendanceMap' => $monthlyData['attendanceMap'],
            'editableDate' => Carbon::today()->toDateString(),
            'canEdit' => false,
        ])->render();

        return response()->json([
            'summary' => $summaryHtml,
            'html' => $gridHtml,
        ]);
    }
}

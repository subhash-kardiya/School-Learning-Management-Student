<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Classes;
use App\Models\Section;
use App\Models\Student;
use App\Models\ParentModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

class AttendanceController extends Controller
{
    private function canPermission(string $permission): bool
    {
        $user = Auth::user();
        if (!$user || !method_exists($user, 'hasPermission')) {
            return false;
        }
        return $user->hasPermission($permission);
    }

    public function markForm(Request $request)
    {
        if (!$this->canPermission('attendance_mark')) {
            abort(403, 'Unauthorized access');
        }

        $classes = Classes::with('sections')->get();
        $sections = Section::all();
        $students = collect();
        $selectedClass = $request->get('class_id');
        $selectedSection = $request->get('section_id');
        $today = Carbon::today()->toDateString();

        if ($selectedClass && $selectedSection) {
            $students = Student::where('class_id', $selectedClass)
                ->where('section_id', $selectedSection)
                ->orderBy('student_name')
                ->get();
        }

        $todayAttendance = Attendance::where('date', $today)
            ->whereIn('student_id', $students->pluck('id'))
            ->get()
            ->keyBy('student_id');

        return view('attendance.mark', compact(
            'classes',
            'sections',
            'students',
            'selectedClass',
            'selectedSection',
            'today',
            'todayAttendance'
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

    public function index(Request $request)
    {
        $user = Auth::user();
        if ($user && method_exists($user, 'hasRole') && $user->hasRole(['student', 'parent', 'teacher'])) {
            abort(403, 'Unauthorized access');
        }
        if (!$this->canPermission('attendance_view')) {
            abort(403, 'Unauthorized access');
        }

        $classes = Classes::with('sections')->get();
        $sections = Section::all();
        $date = $request->get('date', Carbon::today()->toDateString());
        $classId = $request->get('class_id');
        $sectionId = $request->get('section_id');

        $query = Attendance::with('student.class', 'student.section')
            ->where('date', $date);

        if ($classId) {
            $query->whereHas('student', fn($q) => $q->where('class_id', $classId));
        }
        if ($sectionId) {
            $query->whereHas('student', fn($q) => $q->where('section_id', $sectionId));
        }

        $records = $query->get();

        return view('attendance.index', compact('classes', 'sections', 'records', 'date', 'classId', 'sectionId'));
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
        $records = Attendance::where('student_id', $student->id)->get();
        $total = $records->count();
        $present = $records->where('status', 'present')->count();
        $percent = $total > 0 ? round(($present / $total) * 100, 1) : 0;

        $monthly = $records->groupBy(function ($row) {
            return Carbon::parse($row->date)->format('Y-m');
        })->map(function ($group) {
            $total = $group->count();
            $present = $group->where('status', 'present')->count();
            return [
                'total' => $total,
                'present' => $present,
                'percent' => $total > 0 ? round(($present / $total) * 100, 1) : 0,
            ];
        });

        return view('attendance.student', compact('student', 'percent', 'monthly'));
    }

    public function parentView()
    {
        $user = Auth::user();
        if (!$user || !method_exists($user, 'hasRole') || !$user->hasRole('parent')) {
            abort(403, 'Unauthorized access');
        }

        $parent = ParentModel::with('students')->findOrFail($user->id);
        $children = $parent->students;

        $summaries = $children->map(function ($student) {
            $records = Attendance::where('student_id', $student->id)->get();
            $total = $records->count();
            $present = $records->where('status', 'present')->count();
            $percent = $total > 0 ? round(($present / $total) * 100, 1) : 0;

            $monthly = $records->groupBy(function ($row) {
                return Carbon::parse($row->date)->format('Y-m');
            })->map(function ($group) {
                $total = $group->count();
                $present = $group->where('status', 'present')->count();
                return [
                    'total' => $total,
                    'present' => $present,
                    'percent' => $total > 0 ? round(($present / $total) * 100, 1) : 0,
                ];
            });

            return [
                'student' => $student,
                'percent' => $percent,
                'monthly' => $monthly,
            ];
        });

        return view('attendance.parent', compact('parent', 'summaries'));
    }
}

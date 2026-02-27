<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Classes;
use App\Models\Attendance;
use App\Models\Homework;
use App\Models\HomeworkSubmission;
use App\Models\Exam;
use App\Models\ExamMark;
use App\Models\Announcement;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;


class AdminController extends Controller
{
    public function dashboard()
    {
        if (!in_array((string) session('role'), ['admin', 'superadmin'], true)) {
            abort(403, 'Unauthorized access');
        }

        $yearId = session('selected_academic_year_id');
        $today = Carbon::today();
        $cacheKey = 'admin_dashboard:v5:' . ($yearId ?: 'all') . ':' . $today->toDateString();
        $cached = Cache::get($cacheKey);
        if (is_array($cached)) {
            return view('dashboard.admin', $cached);
        }

        $studentCount = Student::query()
            ->when($yearId, fn($q) => $q->where('academic_year_id', $yearId))
            ->count();
        $teacherCount = Teacher::count();
        $classCount = Classes::query()
            ->when($yearId, fn($q) => $q->where('academic_year_id', $yearId))
            ->count();

        $attendanceTodayAgg = Attendance::query()
            ->join('students', 'students.id', '=', 'attendances.student_id')
            ->when($yearId, fn($q) => $q->where('students.academic_year_id', $yearId))
            ->whereDate('attendances.date', $today->toDateString())
            ->selectRaw('COUNT(*) as marked_count')
            ->selectRaw("SUM(CASE WHEN attendances.status = 'present' THEN 1 ELSE 0 END) as present_count")
            ->first();
        $attendanceMarked = (int) ($attendanceTodayAgg->marked_count ?? 0);
        $attendancePresent = (int) ($attendanceTodayAgg->present_count ?? 0);
        $dailyAttendancePct = $attendanceMarked > 0 ? round(($attendancePresent / $attendanceMarked) * 100, 1) : 0;
        $dailyAttendanceTotal = $attendanceMarked;

        $pendingHomework = Homework::query()
            ->when($yearId, fn($q) => $q->where('academic_year_id', $yearId))
            ->where('status', 1)
            ->whereDate('due_date', '>=', $today->toDateString())
            ->count();

        $activeStudents = Student::query()
            ->when($yearId, fn($q) => $q->where('academic_year_id', $yearId))
            ->where('status', 1)
            ->count();
        $activeStudentsPct = $studentCount > 0 ? round(($activeStudents / $studentCount) * 100, 2) : 0;
        $activeStaff = Teacher::query()->where('status', 1)->count();

        // Unused in current dashboard view, keep empty to avoid extra heavy count queries.
        $moduleSummary = collect();
        $moduleChartLabels = collect();
        $moduleChartData = collect();
        $trendLabels = collect();
        $studentTrend = collect();
        $homeworkTrend = collect();
        $announcementTrend = collect();

        $gradeClassLabels = collect(range(1, 10))->map(fn($n) => 'Class ' . $n)->values();
        $gradeClassDataMap = collect(range(1, 10))->mapWithKeys(fn($n) => [$n => 0.0]);
        $overallGradePct = 0.0;
        $topGradeClass = 'Class 1';
        $lowGradeClass = 'Class 1';

        if (Schema::hasTable('exam_marks') && Schema::hasTable('exam_subjects') && Schema::hasTable('classes')) {
            $classGradeRows = ExamMark::query()
                ->join('students', 'students.id', '=', 'exam_marks.student_id')
                ->join('classes', 'classes.id', '=', 'students.class_id')
                ->join('exam_subjects', 'exam_subjects.id', '=', 'exam_marks.exam_subject_id')
                ->when($yearId, fn($q) => $q->where('students.academic_year_id', $yearId))
                ->select(
                    'classes.name as class_name',
                    DB::raw('AVG(CASE WHEN exam_subjects.total_marks > 0 THEN (exam_marks.obtained_marks / exam_subjects.total_marks) * 100 ELSE 0 END) as avg_pct')
                )
                ->groupBy('classes.id', 'classes.name')
                ->get();

            $bucketed = [];
            foreach ($classGradeRows as $row) {
                if (!preg_match('/\d+/', (string) $row->class_name, $match)) {
                    continue;
                }
                $classNo = (int) $match[0];
                if ($classNo < 1 || $classNo > 10) {
                    continue;
                }
                if (!isset($bucketed[$classNo])) {
                    $bucketed[$classNo] = [];
                }
                $bucketed[$classNo][] = (float) $row->avg_pct;
            }

            foreach (range(1, 10) as $classNo) {
                $values = $bucketed[$classNo] ?? [];
                $gradeClassDataMap[$classNo] = !empty($values)
                    ? round(collect($values)->avg(), 1)
                    : 0.0;
            }
        }

        $gradeClassData = $gradeClassDataMap
            ->map(fn($v) => round((float) $v, 1))
            ->values();
        $overallGradePct = round($gradeClassData->avg(), 1);
        $percentToGrade = function (float $pct): string {
            return $pct >= 90 ? 'A+' : ($pct >= 80 ? 'A' : ($pct >= 70 ? 'B+' : ($pct >= 60 ? 'B' : ($pct >= 50 ? 'C+' : 'C'))));
        };
        $gradeClassLetterData = $gradeClassData->map(fn($pct) => $percentToGrade((float) $pct))->values();
        $overallGradeLabel = $percentToGrade((float) $overallGradePct);

        $pairs = $gradeClassLabels->values()->map(function ($label, $idx) use ($gradeClassData) {
            return ['label' => $label, 'value' => (float) ($gradeClassData[$idx] ?? 0)];
        });
        $topPair = $pairs->sortByDesc('value')->first();
        $lowPair = $pairs->sortBy('value')->first();
        $topGradeClass = (string) ($topPair['label'] ?? 'Class 1');
        $lowGradeClass = (string) ($lowPair['label'] ?? 'Class 1');
        $topGradeLabel = $percentToGrade((float) ($topPair['value'] ?? 0));
        $lowGradeLabel = $percentToGrade((float) ($lowPair['value'] ?? 0));

        // Show attendance for current week Monday to Saturday only.
        $weekStart = $today->copy()->startOfWeek(Carbon::MONDAY);
        $weekDates = collect(range(0, 5))->map(fn($i) => $weekStart->copy()->addDays($i));
        $attendanceWeekLabels = $weekDates->map(fn($d) => strtoupper($d->format('D')))->values();
        $weekStartDate = $weekDates->first()->toDateString();
        $weekEndDate = $weekDates->last()->toDateString();
        $weekAttendanceRows = Attendance::query()
            ->join('students', 'students.id', '=', 'attendances.student_id')
            ->when($yearId, fn($q) => $q->where('students.academic_year_id', $yearId))
            ->whereBetween('attendances.date', [$weekStartDate, $weekEndDate])
            ->select(
                DB::raw('DATE(attendances.date) as att_date'),
                'attendances.status',
                DB::raw('COUNT(*) as total')
            )
            ->groupBy(DB::raw('DATE(attendances.date)'), 'attendances.status')
            ->get();
        $weeklyAgg = [];
        foreach ($weekAttendanceRows as $row) {
            $d = (string) $row->att_date;
            $s = (string) $row->status;
            if (!isset($weeklyAgg[$d])) {
                $weeklyAgg[$d] = ['present' => 0, 'absent' => 0];
            }
            $weeklyAgg[$d][$s] = (int) $row->total;
        }
        $attendancePresentWeekData = $weekDates->map(function ($date) use ($weeklyAgg) {
            $key = $date->toDateString();
            return (int) ($weeklyAgg[$key]['present'] ?? 0);
        })->values();
        $attendanceAbsentWeekData = $weekDates->map(function ($date) use ($weeklyAgg) {
            $key = $date->toDateString();
            return (int) ($weeklyAgg[$key]['absent'] ?? 0);
        })->values();
        $attendanceLast7PresentTotal = $attendancePresentWeekData->sum();
        $attendanceLast7AbsentTotal = $attendanceAbsentWeekData->sum();
        $attendanceLast7Range = $weekDates->first()->format('d M') . ' - ' . $weekDates->last()->format('d M');
        $attendanceWeekData = collect();
        $attendanceLastWeekData = collect();

        $upcomingItems = collect();
        if (Schema::hasTable('exams')) {
            $upcomingItems = $upcomingItems->merge(
                Exam::query()
                    ->when($yearId, fn($q) => $q->where('academic_year_id', $yearId))
                    ->whereDate('start_date', '>=', $today->toDateString())
                    ->orderBy('start_date')
                    ->limit(4)
                    ->get()
                    ->map(fn($exam) => [
                        'title' => $exam->name,
                        'meta' => 'Exam',
                        'date' => Carbon::parse($exam->start_date)->format('d M Y'),
                        'sort_date' => Carbon::parse($exam->start_date),
                    ])
            );
        }
        $upcomingItems = $upcomingItems->merge(
            Homework::query()
                ->when($yearId, fn($q) => $q->where('academic_year_id', $yearId))
                ->whereDate('due_date', '>=', $today->toDateString())
                ->orderBy('due_date')
                ->limit(6)
                ->get()
                ->map(fn($hw) => [
                    'title' => $hw->title,
                    'meta' => 'Homework',
                    'date' => Carbon::parse($hw->due_date)->format('d M Y'),
                    'sort_date' => Carbon::parse($hw->due_date),
                ])
        )->sortBy('sort_date')->take(8)->values();

        $recentActivities = collect();
        $recentActivities = $recentActivities->merge(
            Student::query()
                ->when($yearId, fn($q) => $q->where('academic_year_id', $yearId))
                ->latest()
                ->limit(4)
                ->get()
                ->map(fn($s) => [
                    'actor' => $s->student_name,
                    'action' => 'new admission added',
                    'time' => optional($s->created_at)->diffForHumans(),
                    'sort_time' => $s->created_at,
                ])
        );
        $recentActivities = $recentActivities->merge(
            HomeworkSubmission::query()
                ->join('students', 'students.id', '=', 'homework_submissions.student_id')
                ->latest('homework_submissions.created_at')
                ->limit(4)
                ->get(['students.student_name', 'homework_submissions.created_at'])
                ->map(fn($row) => [
                    'actor' => $row->student_name,
                    'action' => 'submitted homework',
                    'time' => Carbon::parse($row->created_at)->diffForHumans(),
                    'sort_time' => Carbon::parse($row->created_at),
                ])
        );
        $recentActivities = $recentActivities->merge(
            Announcement::query()
                ->latest()
                ->limit(3)
                ->get()
                ->map(fn($a) => [
                    'actor' => $a->creator_name,
                    'action' => 'posted announcement',
                    'time' => optional($a->created_at)->diffForHumans(),
                    'sort_time' => $a->created_at,
                ])
        )->sortByDesc('sort_time')->take(6)->values();

        $recentStaff = Teacher::query()
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn($teacher) => [
                'name' => $teacher->name,
                'qualification' => $teacher->qualification ?: 'N/A',
                'exp' => $teacher->exp ? ((int) $teacher->exp . ' Yrs Exp') : 'N/A',
            ])
            ->values();

        $latestAdmissionsRaw = Student::query()
            ->with(['class:id,name', 'section:id,name'])
            ->when($yearId, fn($q) => $q->where('academic_year_id', $yearId))
            ->latest()
            ->limit(5)
            ->get(['id', 'student_name', 'username', 'roll_no', 'mobile_no', 'profile_image', 'class_id', 'section_id', 'status', 'created_at']);
        $admissionIds = $latestAdmissionsRaw->pluck('id');
        $admissionAttendance = Attendance::query()
            ->whereIn('student_id', $admissionIds)
            ->whereDate('date', '>=', $today->copy()->subDays(30)->toDateString())
            ->select(
                'student_id',
                DB::raw('COUNT(*) as total_days'),
                DB::raw("SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_days")
            )
            ->groupBy('student_id')
            ->get()
            ->keyBy('student_id');
        $latestAdmissions = $latestAdmissionsRaw->map(function ($student) use ($admissionAttendance) {
            $row = $admissionAttendance->get($student->id);
            $pct = $row && (int) $row->total_days > 0 ? round(($row->present_days / $row->total_days) * 100) : 0;
            return [
                'name' => $student->student_name,
                'company' => $student->class?->name ? ('Class ' . $student->class->name) : 'N/A',
                'section' => $student->section?->name ?: 'N/A',
                'username' => $student->username ?: 'N/A',
                'roll_no' => $student->roll_no ?: 'N/A',
                'mobile_no' => $student->mobile_no ?: 'N/A',
                'profile_image' => $student->profile_image,
                'admission_date' => optional($student->created_at)->format('d M Y') ?: 'N/A',
                'progress' => $pct,
                'status' => ((int) $student->status === 1 ? 'Approved' : 'Pending'),
            ];
        })->values();
        $recentAdmissionsCount = $latestAdmissions->count();

        $calendarItems = Announcement::query()
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn($a) => [
                'title' => $a->title,
                'date' => optional($a->start_date)->format('M d, Y') ?: optional($a->created_at)->format('M d, Y'),
            ])->values();

        $activityFeed = $recentActivities->map(fn($a) => [
            'actor' => $a['actor'],
            'text' => $a['action'],
            'time' => $a['time'],
        ])->values();

        $classRows = Classes::query()
            ->when($yearId, fn($q) => $q->where('academic_year_id', $yearId))
            ->select('id', 'name')
            ->get()
            ->keyBy('id');
        $enrollmentCountMap = Student::query()
            ->when($yearId, fn($q) => $q->where('academic_year_id', $yearId))
            ->select('class_id', DB::raw('COUNT(*) as total'))
            ->groupBy('class_id')
            ->pluck('total', 'class_id');

        $groupTotals = [
            'High School (Std 9-10)' => 0,
            'Middle School (Std 6-8)' => 0,
            'Elementary (Std 1-5)' => 0,
        ];
        foreach ($enrollmentCountMap as $classId => $total) {
            $className = (string) optional($classRows->get((int) $classId))->name;
            if (!preg_match('/\d+/', $className, $m)) {
                continue;
            }
            $std = (int) $m[0];
            if ($std >= 9 && $std <= 10) {
                $groupTotals['High School (Std 9-10)'] += (int) $total;
            } elseif ($std >= 6 && $std <= 8) {
                $groupTotals['Middle School (Std 6-8)'] += (int) $total;
            } elseif ($std >= 1 && $std <= 5) {
                $groupTotals['Elementary (Std 1-5)'] += (int) $total;
            }
        }

        $enrollmentLabels = collect(array_keys($groupTotals))->values();
        $enrollmentData = collect(array_values($groupTotals))->values();

        $topPerformers = collect();
        if (Schema::hasTable('exam_marks') && Schema::hasTable('exam_subjects')) {
            $topPerformers = ExamMark::query()
                ->join('students', 'students.id', '=', 'exam_marks.student_id')
                ->leftJoin('classes', 'classes.id', '=', 'students.class_id')
                ->join('exam_subjects', 'exam_subjects.id', '=', 'exam_marks.exam_subject_id')
                ->when(Schema::hasTable('exams'), function ($q) use ($yearId) {
                    $q->join('exams', 'exams.id', '=', 'exam_marks.exam_id')
                        ->when($yearId, fn($qq) => $qq->where('exams.academic_year_id', $yearId));
                }, function ($q) use ($yearId) {
                    $q->when($yearId, fn($qq) => $qq->where('students.academic_year_id', $yearId));
                })
                ->select(
                    'students.student_name',
                    'students.profile_image',
                    'classes.name as class_name',
                    DB::raw('AVG(CASE WHEN exam_subjects.total_marks > 0 THEN (exam_marks.obtained_marks / exam_subjects.total_marks) * 100 ELSE 0 END) as avg_pct')
                )
                ->groupBy('students.id', 'students.student_name', 'students.profile_image', 'classes.name')
                ->orderByDesc('avg_pct')
                ->limit(5)
                ->get()
                ->map(fn($row) => [
                    'name' => $row->student_name,
                    'profile_image' => $row->profile_image,
                    'std' => $row->class_name ?: 'N/A',
                    'score' => number_format((float) $row->avg_pct, 1) . '%',
                    'time' => 'just now',
                ]);
        }
        if ($topPerformers->isEmpty()) {
            $topPerformers = $latestAdmissions->map(fn($a) => [
                'name' => $a['name'],
                'profile_image' => $a['profile_image'] ?? null,
                'std' => !empty($a['company']) ? trim(str_replace('Class ', '', (string) $a['company'])) : 'N/A',
                'score' => $a['progress'] . '%',
                'time' => '1h ago',
            ])->values();
        }

        $avgForGrade = $topPerformers
            ->map(function ($row) {
                return (float) preg_replace('/[^0-9.]/', '', (string) ($row['score'] ?? 0));
            })
            ->filter(fn($v) => $v > 0)
            ->avg();
        $academicAverage = $avgForGrade >= 85 ? 'A' : ($avgForGrade >= 70 ? 'B+' : ($avgForGrade >= 55 ? 'B' : 'C+'));

        $upcomingExams = $upcomingItems
            ->filter(fn($item) => ($item['meta'] ?? '') === 'Exam')
            ->values();
        if ($upcomingExams->isEmpty()) {
            $upcomingExams = $upcomingItems->values();
        }

        $viewData = compact(
            'studentCount',
            'teacherCount',
            'classCount',
            'dailyAttendancePct',
            'dailyAttendanceTotal',
            'activeStudentsPct',
            'activeStaff',
            'recentAdmissionsCount',
            'academicAverage',
            'pendingHomework',
            'moduleSummary',
            'moduleChartLabels',
            'moduleChartData',
            'trendLabels',
            'studentTrend',
            'homeworkTrend',
            'announcementTrend',
            'gradeClassLabels',
            'gradeClassData',
            'overallGradePct',
            'gradeClassLetterData',
            'overallGradeLabel',
            'topGradeClass',
            'lowGradeClass',
            'topGradeLabel',
            'lowGradeLabel',
            'attendanceWeekLabels',
            'attendancePresentWeekData',
            'attendanceAbsentWeekData',
            'attendanceLast7PresentTotal',
            'attendanceLast7AbsentTotal',
            'attendanceLast7Range',
            'attendanceWeekData',
            'attendanceLastWeekData',
            'upcomingItems',
            'recentActivities',
            'recentStaff',
            'latestAdmissions',
            'calendarItems',
            'activityFeed',
            'topPerformers',
            'enrollmentLabels',
            'enrollmentData',
            'upcomingExams'
        );

        Cache::put($cacheKey, $viewData, now()->addMinutes(5));
        return view('dashboard.admin', $viewData);
    }
}

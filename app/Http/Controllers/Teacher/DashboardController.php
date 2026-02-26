<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Attendance;
use App\Models\Classes;
use App\Models\ExamMark;
use App\Models\Homework;
use App\Models\HomeworkSubmission;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\TeacherMapping;
use App\Models\Timetable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function index()
    {
        $teacherId = (int) session('auth_id');
        $yearId = session('selected_academic_year_id');
        $today = Carbon::today();
        $cacheKey = 'teacher_dashboard:v2:' . $teacherId . ':' . ($yearId ?: 'all') . ':' . $today->toDateString();
        $cached = Cache::get($cacheKey);
        if (is_array($cached)) {
            return view('dashboard.teacher', $cached);
        }

        $teacher = Teacher::query()->find($teacherId);
        if (!$teacher) {
            abort(403, 'Unauthorized access');
        }

        $mappings = TeacherMapping::query()
            ->with(['section.class', 'subject'])
            ->where('teacher_id', $teacherId)
            ->get();

        $sectionIds = $mappings->pluck('section_id')->filter()->unique()->values();
        $classIds = $mappings
            ->map(fn($m) => optional($m->section)->class_id)
            ->filter()
            ->unique()
            ->values();

        $assignedClassesCount = $classIds->count();
        $assignedSectionsCount = $sectionIds->count();

        $studentBaseQuery = Student::query()
            ->when($sectionIds->isNotEmpty(), fn($q) => $q->whereIn('section_id', $sectionIds), fn($q) => $q->whereRaw('1=0'))
            ->when($yearId, fn($q) => $q->where('academic_year_id', $yearId));

        $myStudentsCount = (clone $studentBaseQuery)->count();

        $attendanceTodayAgg = Attendance::query()
            ->join('students', 'students.id', '=', 'attendances.student_id')
            ->when($sectionIds->isNotEmpty(), fn($q) => $q->whereIn('students.section_id', $sectionIds), fn($q) => $q->whereRaw('1=0'))
            ->when($yearId, fn($q) => $q->where('students.academic_year_id', $yearId))
            ->whereDate('attendances.date', $today->toDateString())
            ->selectRaw('COUNT(*) as marked_count')
            ->selectRaw("SUM(CASE WHEN attendances.status = 'present' THEN 1 ELSE 0 END) as present_count")
            ->first();

        $attendanceMarked = (int) ($attendanceTodayAgg->marked_count ?? 0);
        $attendancePresent = (int) ($attendanceTodayAgg->present_count ?? 0);
        $attendanceTodayPct = $attendanceMarked > 0 ? round(($attendancePresent / $attendanceMarked) * 100, 1) : 0;

        $assignedClasses = Classes::query()
            ->whereIn('id', $classIds)
            ->orderBy('name')
            ->get(['id', 'name']);

        $todayByClassRows = Attendance::query()
            ->join('students', 'students.id', '=', 'attendances.student_id')
            ->when($sectionIds->isNotEmpty(), fn($q) => $q->whereIn('students.section_id', $sectionIds), fn($q) => $q->whereRaw('1=0'))
            ->whereDate('attendances.date', $today->toDateString())
            ->select(
                'students.class_id',
                'attendances.status',
                DB::raw('COUNT(*) as total')
            )
            ->groupBy('students.class_id', 'attendances.status')
            ->get();
        $presentByClass = [];
        $absentByClass = [];
        foreach ($todayByClassRows as $row) {
            $cid = (int) $row->class_id;
            if ((string) $row->status === 'present') {
                $presentByClass[$cid] = (int) $row->total;
            }
            if ((string) $row->status === 'absent') {
                $absentByClass[$cid] = (int) $row->total;
            }
        }
        $classAttendanceLabels = $assignedClasses->map(fn($c) => (string) $c->name)->values();
        $classAttendancePresentData = $assignedClasses->map(fn($c) => (int) ($presentByClass[(int) $c->id] ?? 0))->values();
        $classAttendanceAbsentData = $assignedClasses->map(fn($c) => (int) ($absentByClass[(int) $c->id] ?? 0))->values();
        $classAttendancePresentTotal = $classAttendancePresentData->sum();
        $classAttendanceAbsentTotal = $classAttendanceAbsentData->sum();
        $classAttendanceDateLabel = $today->format('d M Y');
        $attendanceStatusSummary = $attendanceMarked . '/' . max($assignedSectionsCount, 1) . ' Completed';

        $todayName = $today->format('l');
        $todaysLecturesCount = Timetable::query()
            ->where('teacher_id', $teacherId)
            ->when($yearId, fn($q) => $q->where('academic_year_id', $yearId))
            ->where('day_of_week', $todayName)
            ->count();

        $homeworkBaseQuery = Homework::query()
            ->where('teacher_id', $teacherId)
            ->when($yearId, fn($q) => $q->where('academic_year_id', $yearId));

        $activeHomeworkCount = (clone $homeworkBaseQuery)
            ->where('status', 1)
            ->whereDate('due_date', '>=', $today->toDateString())
            ->count();

        $myHomeworkIds = (clone $homeworkBaseQuery)->pluck('id');
        $pendingReviewCount = 0;
        if ($myHomeworkIds->isNotEmpty()) {
            $pendingReviewCount = HomeworkSubmission::query()
                ->whereIn('homework_id', $myHomeworkIds)
                ->where(function ($q) {
                    $q->whereNull('feedback')->orWhere('feedback', '');
                })
                ->count();
        }

        $weekStart = $today->copy()->startOfWeek(Carbon::MONDAY);
        $weekDates = collect(range(0, 5))->map(fn($i) => $weekStart->copy()->addDays($i));
        $attendanceWeekLabels = $weekDates->map(fn($d) => strtoupper($d->format('D')))->values();

        $weekAttendanceRows = Attendance::query()
            ->join('students', 'students.id', '=', 'attendances.student_id')
            ->when($sectionIds->isNotEmpty(), fn($q) => $q->whereIn('students.section_id', $sectionIds), fn($q) => $q->whereRaw('1=0'))
            ->when($yearId, fn($q) => $q->where('students.academic_year_id', $yearId))
            ->whereBetween('attendances.date', [$weekDates->first()->toDateString(), $weekDates->last()->toDateString()])
            ->select(
                DB::raw('DATE(attendances.date) as att_date'),
                'attendances.status',
                DB::raw('COUNT(*) as total')
            )
            ->groupBy(DB::raw('DATE(attendances.date)'), 'attendances.status')
            ->get();

        $weeklyAgg = [];
        foreach ($weekAttendanceRows as $row) {
            $key = (string) $row->att_date;
            if (!isset($weeklyAgg[$key])) {
                $weeklyAgg[$key] = ['present' => 0, 'absent' => 0];
            }
            $weeklyAgg[$key][(string) $row->status] = (int) $row->total;
        }

        $attendancePresentWeekData = $weekDates->map(function ($date) use ($weeklyAgg) {
            return (int) ($weeklyAgg[$date->toDateString()]['present'] ?? 0);
        })->values();
        $attendanceAbsentWeekData = $weekDates->map(function ($date) use ($weeklyAgg) {
            return (int) ($weeklyAgg[$date->toDateString()]['absent'] ?? 0);
        })->values();

        $attendanceWeekRange = $weekDates->first()->format('d M') . ' - ' . $weekDates->last()->format('d M');

        $hasExamTables = Schema::hasTable('exam_marks') && Schema::hasTable('exam_subjects');
        if ($hasExamTables) {
            $topStudents = ExamMark::query()
                ->join('students', 'students.id', '=', 'exam_marks.student_id')
                ->leftJoin('classes', 'classes.id', '=', 'students.class_id')
                ->join('exam_subjects', 'exam_subjects.id', '=', 'exam_marks.exam_subject_id')
                ->when($sectionIds->isNotEmpty(), fn($q) => $q->whereIn('students.section_id', $sectionIds), fn($q) => $q->whereRaw('1=0'))
                ->when($yearId, fn($q) => $q->where('students.academic_year_id', $yearId))
                ->select(
                    'students.student_name',
                    'classes.name as class_name',
                    DB::raw('AVG(CASE WHEN exam_subjects.total_marks > 0 THEN (exam_marks.obtained_marks / exam_subjects.total_marks) * 100 ELSE 0 END) as avg_pct')
                )
                ->groupBy('students.id', 'students.student_name', 'classes.name')
                ->orderByDesc('avg_pct')
                ->limit(5)
                ->get()
                ->values();
        } else {
            $topStudents = collect([
                (object) ['student_name' => 'Riya Patel', 'class_name' => 'Std 10 - A', 'avg_pct' => 92.4],
                (object) ['student_name' => 'Aarav Shah', 'class_name' => 'Std 9 - B', 'avg_pct' => 90.1],
                (object) ['student_name' => 'Priya Desai', 'class_name' => 'Std 8 - A', 'avg_pct' => 88.3],
                (object) ['student_name' => 'Dev Parmar', 'class_name' => 'Std 7 - C', 'avg_pct' => 86.7],
                (object) ['student_name' => 'Mahi Joshi', 'class_name' => 'Std 6 - A', 'avg_pct' => 85.2],
            ]);
        }
        $topStudentsLabels = $topStudents->map(fn($r) => (string) $r->student_name)->values();
        $topStudentsData = $topStudents->map(fn($r) => round((float) $r->avg_pct, 1))->values();
        $topPerformerName = (string) ($topStudents->first()->student_name ?? 'N/A');
        $topPerformerScore = round((float) ($topStudents->first()->avg_pct ?? 0), 1) . '%';

        $resultBuckets = ['A' => 0, 'B' => 0, 'C' => 0, 'Fail' => 0];
        foreach ($topStudents as $row) {
            $pct = (float) $row->avg_pct;
            if ($pct >= 80) {
                $resultBuckets['A']++;
            } elseif ($pct >= 60) {
                $resultBuckets['B']++;
            } elseif ($pct >= 40) {
                $resultBuckets['C']++;
            } else {
                $resultBuckets['Fail']++;
            }
        }
        $resultDistLabels = collect(array_keys($resultBuckets))->values();
        $resultDistData = collect(array_values($resultBuckets))->values();

        $sectionStudentRows = Student::query()
            ->join('sections', 'sections.id', '=', 'students.section_id')
            ->join('classes', 'classes.id', '=', 'students.class_id')
            ->when($sectionIds->isNotEmpty(), fn($q) => $q->whereIn('students.section_id', $sectionIds), fn($q) => $q->whereRaw('1=0'))
            ->when($yearId, fn($q) => $q->where('students.academic_year_id', $yearId))
            ->select('classes.name as class_name', 'sections.name as section_name', DB::raw('COUNT(*) as total'))
            ->groupBy('classes.name', 'sections.name')
            ->orderBy('classes.name')
            ->orderBy('sections.name')
            ->get();

        $sectionLabels = $sectionStudentRows
            ->map(fn($row) => trim(($row->class_name ?? 'Class') . ' - ' . ($row->section_name ?? 'Section')))
            ->values();
        $sectionStudentData = $sectionStudentRows->map(fn($row) => (int) $row->total)->values();
        $classPerfLabels = $sectionStudentRows->pluck('class_name')->unique()->values();
        $classPerfData = $classPerfLabels->map(function ($className) use ($sectionStudentRows) {
            return (int) $sectionStudentRows->where('class_name', $className)->sum('total');
        })->values();

        // Class-wise homework submission logic:
        // expected submissions = (students in class-section) x (homeworks assigned to class-section)
        // pending = expected - submitted
        $studentCountByClassSection = Student::query()
            ->when($sectionIds->isNotEmpty(), fn($q) => $q->whereIn('section_id', $sectionIds), fn($q) => $q->whereRaw('1=0'))
            ->when($yearId, fn($q) => $q->where('academic_year_id', $yearId))
            ->select('class_id', 'section_id', DB::raw('COUNT(*) as total_students'))
            ->groupBy('class_id', 'section_id')
            ->get()
            ->keyBy(fn($r) => $r->class_id . '-' . $r->section_id);

        $homeworkCountByClassSection = Homework::query()
            ->where('teacher_id', $teacherId)
            ->when($yearId, fn($q) => $q->where('academic_year_id', $yearId))
            ->whereDate('created_at', '>=', $today->copy()->subDays(30)->toDateString())
            ->select('class_id', 'section_id', DB::raw('COUNT(*) as total_homeworks'))
            ->groupBy('class_id', 'section_id')
            ->get();

        $expectedByClass = [];
        foreach ($homeworkCountByClassSection as $row) {
            $key = $row->class_id . '-' . $row->section_id;
            $studentsInSection = (int) optional($studentCountByClassSection->get($key))->total_students;
            $expectedByClass[$row->class_id] = (int) ($expectedByClass[$row->class_id] ?? 0) + ($studentsInSection * (int) $row->total_homeworks);
        }

        $submittedByClass = HomeworkSubmission::query()
            ->join('homeworks', 'homeworks.id', '=', 'homework_submissions.homework_id')
            ->where('homeworks.teacher_id', $teacherId)
            ->when($yearId, fn($q) => $q->where('homeworks.academic_year_id', $yearId))
            ->whereDate('homeworks.created_at', '>=', $today->copy()->subDays(30)->toDateString())
            ->select('homeworks.class_id', DB::raw("COUNT(DISTINCT CONCAT(homework_submissions.homework_id, '-', homework_submissions.student_id)) as total_submitted"))
            ->groupBy('homeworks.class_id')
            ->pluck('total_submitted', 'homeworks.class_id');

        $classIdsForChart = $classIds
            ->merge(array_keys($expectedByClass))
            ->merge($submittedByClass->keys())
            ->unique()
            ->values();

        $homeworkClassLabels = collect();
        $homeworkClassSubmittedData = collect();
        $homeworkClassPendingData = collect();
        $orderedClasses = Classes::query()
            ->whereIn('id', $classIdsForChart)
            ->orderBy('name')
            ->get(['id', 'name']);
        foreach ($orderedClasses as $classModel) {
            $classId = (int) $classModel->id;
            $expected = (int) ($expectedByClass[$classId] ?? 0);
            $submitted = (int) ($submittedByClass[$classId] ?? 0);
            $pending = max($expected - $submitted, 0);

            $homeworkClassLabels->push((string) ($classModel->name ?: ('Class ' . $classId)));
            $homeworkClassSubmittedData->push($submitted);
            $homeworkClassPendingData->push($pending);
        }

        $homeworkClassSubmittedTotal = $homeworkClassSubmittedData->sum();
        $homeworkClassPendingTotal = $homeworkClassPendingData->sum();

        $recentHomeworkRows = (clone $homeworkBaseQuery)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get(['id', 'title', 'class_id', 'section_id']);

        $submissionCountMap = collect();
        if ($recentHomeworkRows->isNotEmpty()) {
            $submissionCountMap = HomeworkSubmission::query()
                ->whereIn('homework_id', $recentHomeworkRows->pluck('id'))
                ->select('homework_id', DB::raw('COUNT(*) as total'))
                ->groupBy('homework_id')
                ->pluck('total', 'homework_id');
        }

        $homeworkPerfLabels = collect();
        $homeworkPerfSubmitted = collect();
        $homeworkPerfRate = collect();
        foreach ($recentHomeworkRows as $hw) {
            $totalStudents = Student::query()
                ->where('class_id', $hw->class_id)
                ->where('section_id', $hw->section_id)
                ->when($yearId, fn($q) => $q->where('academic_year_id', $yearId))
                ->count();
            $submitted = (int) ($submissionCountMap[$hw->id] ?? 0);
            $rate = $totalStudents > 0 ? round(($submitted / $totalStudents) * 100, 1) : 0;

            $homeworkPerfLabels->push(\Illuminate\Support\Str::limit((string) $hw->title, 18));
            $homeworkPerfSubmitted->push($submitted);
            $homeworkPerfRate->push($rate);
        }

        $monthStart = $today->copy()->startOfMonth()->subMonths(5);
        $monthKeys = collect(range(0, 5))->map(fn($i) => $monthStart->copy()->addMonths($i));
        $homeworkMonthLabels = $monthKeys->map(fn($d) => $d->format('M'))->values();
        $homeworkRowsByMonth = (clone $homeworkBaseQuery)
            ->whereBetween('created_at', [$monthKeys->first()->startOfMonth()->toDateString(), $monthKeys->last()->endOfMonth()->toDateString()])
            ->select(DB::raw('DATE_FORMAT(created_at, "%Y-%m") as ym'), DB::raw('COUNT(*) as total'))
            ->groupBy('ym')
            ->pluck('total', 'ym');
        $submissionRowsByMonth = HomeworkSubmission::query()
            ->join('homeworks', 'homeworks.id', '=', 'homework_submissions.homework_id')
            ->where('homeworks.teacher_id', $teacherId)
            ->whereBetween('homework_submissions.created_at', [$monthKeys->first()->startOfMonth()->toDateString(), $monthKeys->last()->endOfMonth()->toDateString()])
            ->select(DB::raw('DATE_FORMAT(homework_submissions.created_at, "%Y-%m") as ym'), DB::raw('COUNT(*) as total'))
            ->groupBy('ym')
            ->pluck('total', 'ym');
        $homeworkSubmittedMonthData = collect();
        $homeworkPendingMonthData = collect();
        foreach ($monthKeys as $m) {
            $ym = $m->format('Y-m');
            $totalHw = (int) ($homeworkRowsByMonth[$ym] ?? 0);
            $expected = $totalHw * max((int) $myStudentsCount, 1);
            $submitted = (int) ($submissionRowsByMonth[$ym] ?? 0);
            $homeworkSubmittedMonthData->push($submitted);
            $homeworkPendingMonthData->push(max($expected - $submitted, 0));
        }

        $rawLectures = Timetable::query()
            ->with(['class:id,name', 'section:id,name', 'subject:id,name'])
            ->where('teacher_id', $teacherId)
            ->when($yearId, fn($q) => $q->where('academic_year_id', $yearId))
            ->get();

        $dayOrder = ['Monday' => 1, 'Tuesday' => 2, 'Wednesday' => 3, 'Thursday' => 4, 'Friday' => 5, 'Saturday' => 6];
        $todayOrder = $dayOrder[$todayName] ?? 1;
        $upcomingLectures = $rawLectures
            ->map(function ($row) use ($dayOrder, $todayOrder) {
                $current = $dayOrder[$row->day_of_week] ?? 99;
                $delta = $current >= $todayOrder ? ($current - $todayOrder) : (7 - $todayOrder + $current);
                $start = $row->start_time ? Carbon::parse($row->start_time) : null;
                $end = $row->end_time ? Carbon::parse($row->end_time) : null;
                return [
                    'day' => $row->day_of_week,
                    'subject' => optional($row->subject)->name ?: 'Subject',
                    'class_section' => trim((optional($row->class)->name ?: 'Class') . ' - ' . (optional($row->section)->name ?: 'Section')),
                    'time' => ($start ? $start->format('h:i A') : '--:--') . ' - ' . ($end ? $end->format('h:i A') : '--:--'),
                    'sort_score' => ($delta * 10000) + (int) str_replace(':', '', ($start ? $start->format('H:i') : '23:59')),
                ];
            })
            ->sortBy('sort_score')
            ->take(6)
            ->values();

        $todaySchedule = $rawLectures
            ->filter(fn($r) => (string) $r->day_of_week === (string) $todayName)
            ->sortBy('start_time')
            ->values()
            ->map(function ($r) {
                $start = $r->start_time ? Carbon::parse($r->start_time)->format('h:i A') : '--:--';
                $end = $r->end_time ? Carbon::parse($r->end_time)->format('h:i A') : '--:--';
                return [
                    'time' => $start . ' - ' . $end,
                    'class' => trim((optional($r->class)->name ?: '-') . ' ' . (optional($r->section)->name ?: '')),
                    'subject' => optional($r->subject)->name ?: '-',
                    'room' => (string) ($r->room ?: '-'),
                ];
            });

        $myClassRows = $mappings
            ->groupBy(fn($m) => (optional(optional($m->section)->class)->name ?: '-') . '|' . (optional($m->subject)->name ?: '-'))
            ->map(function ($group) use ($yearId) {
                $first = $group->first();
                $sectionIdsLocal = $group->pluck('section_id')->filter()->unique();
                $studentsCount = Student::query()
                    ->whereIn('section_id', $sectionIdsLocal)
                    ->when($yearId, fn($q) => $q->where('academic_year_id', $yearId))
                    ->count();
                return [
                    'class' => optional(optional($first->section)->class)->name ?: '-',
                    'subject' => optional($first->subject)->name ?: '-',
                    'students' => $studentsCount,
                ];
            })
            ->values();

        $quickActions = collect([
            ['label' => 'Mark Attendance', 'icon' => 'bi-check2-square', 'url' => route('teacher.attendance.mark')],
            ['label' => 'Add Homework', 'icon' => 'bi-journal-plus', 'url' => route('teacher.homework.create')],
            ['label' => 'Create Test', 'icon' => 'bi-ui-checks-grid', 'url' => route('teacher.exams.schedule')],
            ['label' => 'Send Message', 'icon' => 'bi-megaphone', 'url' => route('teacher.communication.announcements.create')],
        ]);

        $recentSubmissions = collect();
        if ($myHomeworkIds->isNotEmpty()) {
            $recentSubmissions = HomeworkSubmission::query()
                ->join('homeworks', 'homeworks.id', '=', 'homework_submissions.homework_id')
                ->join('students', 'students.id', '=', 'homework_submissions.student_id')
                ->whereIn('homework_submissions.homework_id', $myHomeworkIds)
                ->orderByDesc('homework_submissions.submitted_at')
                ->limit(6)
                ->get([
                    'students.student_name',
                    'students.class_id',
                    'students.section_id',
                    'homeworks.title as homework_title',
                    'homework_submissions.submitted_at',
                    'homework_submissions.feedback',
                ])
                ->map(function ($row) {
                    return [
                        'student' => $row->student_name,
                        'homework' => $row->homework_title,
                        'submitted' => Carbon::parse($row->submitted_at)->diffForHumans(),
                        'state' => filled($row->feedback) ? 'Reviewed' : 'Pending review',
                    ];
                })
                ->values();
        }

        $latestAnnouncements = Announcement::query()
            ->activeWindow()
            ->visibleTo((string) session('role'), $teacher)
            ->latest()
            ->limit(5)
            ->get();

        $viewData = compact(
            'teacher',
            'assignedClassesCount',
            'assignedSectionsCount',
            'myStudentsCount',
            'attendanceStatusSummary',
            'attendanceTodayPct',
            'attendanceMarked',
            'classAttendanceLabels',
            'classAttendancePresentData',
            'classAttendanceAbsentData',
            'classAttendancePresentTotal',
            'classAttendanceAbsentTotal',
            'classAttendanceDateLabel',
            'todaysLecturesCount',
            'activeHomeworkCount',
            'pendingReviewCount',
            'attendanceWeekLabels',
            'attendancePresentWeekData',
            'attendanceAbsentWeekData',
            'attendanceWeekRange',
            'sectionLabels',
            'sectionStudentData',
            'classPerfLabels',
            'classPerfData',
            'resultDistLabels',
            'resultDistData',
            'homeworkClassLabels',
            'homeworkClassSubmittedData',
            'homeworkClassPendingData',
            'homeworkClassSubmittedTotal',
            'homeworkClassPendingTotal',
            'homeworkPerfLabels',
            'homeworkPerfSubmitted',
            'homeworkPerfRate',
            'homeworkMonthLabels',
            'homeworkSubmittedMonthData',
            'homeworkPendingMonthData',
            'topStudents',
            'topStudentsLabels',
            'topStudentsData',
            'topPerformerName',
            'topPerformerScore',
            'upcomingLectures',
            'todaySchedule',
            'myClassRows',
            'recentSubmissions',
            'latestAnnouncements',
            'quickActions'
        );

        Cache::put($cacheKey, $viewData, now()->addMinutes(5));
        return view('dashboard.teacher', $viewData);
    }
}

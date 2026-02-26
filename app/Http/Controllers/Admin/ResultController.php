<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Classes;
use App\Models\Exam;
use App\Models\ExamMark;
use App\Models\Section;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ResultController extends Controller
{
    public function index(Request $request)
    {
        $role = (string) session('role');

        if ($role === 'student') {
            return redirect()->route('student.results');
        }
        if ($role === 'teacher') {
            return redirect()->route('teacher.results');
        }
        if ($role === 'parent') {
            return redirect()->route('parent.results');
        }

        if ($request->ajax()) {
            $query = ExamMark::with(['student.class', 'section', 'exam', 'subject'])
                ->whereHas('exam', fn($q) => $q->where('result_declared', 1));

            $hasRequiredFilters = $request->filled('class_id')
                && $request->filled('section_id')
                && $request->filled('exam_name');
            if (!$hasRequiredFilters) {
                $query->whereRaw('1 = 0');
            }

            if ($request->filled('class_id')) {
                $query->where('class_id', (int) $request->class_id);
            }
            if ($request->filled('section_id')) {
                $query->where('section_id', (int) $request->section_id);
            }
            if ($request->filled('exam_name')) {
                $examName = trim((string) $request->exam_name);
                $query->whereHas('exam', fn($q) => $q->where('name', $examName));
            }

            $summaryQuery = clone $query;

            $searchValue = trim((string) $request->input('search.value', ''));
            $searchText = trim((string) $request->input('search_text', ''));
            $search = $searchValue !== '' ? $searchValue : $searchText;
            if ($search !== '') {
                $query->where(function ($inner) use ($search) {
                    $inner->whereHas('student', function ($studentQuery) use ($search) {
                        $studentQuery->where('roll_no', 'like', "%{$search}%")
                            ->orWhere('student_name', 'like', "%{$search}%");
                    })->orWhereHas('exam', function ($examQuery) use ($search) {
                        $examQuery->where('name', 'like', "%{$search}%");
                    })->orWhereHas('subject', function ($subjectQuery) use ($search) {
                        $subjectQuery->where('name', 'like', "%{$search}%");
                    });
                });
            }

            $rows = $query->get()
                ->groupBy('student_id')
                ->map(function ($studentMarks) {
                    $first = $studentMarks->first();
                    $totalObtained = (float) $studentMarks->whereNotNull('marks_obtained')->sum('marks_obtained');
                    $totalMarks = (float) $studentMarks->sum(function ($m) {
                        return (float) ($m->exam->total_mark ?? 0);
                    });
                    $percentageNumeric = $totalMarks > 0 ? (($totalObtained / $totalMarks) * 100) : null;

                    $percentage = $totalMarks > 0
                        ? number_format((float) $percentageNumeric, 2) . '%'
                        : '-';

                    $hasPassingRules = $studentMarks->contains(fn($m) => $m->exam?->passing_mark !== null);
                    if ($hasPassingRules) {
                        $allKnown = $studentMarks->every(function ($m) {
                            return $m->marks_obtained !== null && $m->exam?->passing_mark !== null;
                        });
                        $allPass = $allKnown && $studentMarks->every(function ($m) {
                            return (float) $m->marks_obtained >= (float) $m->exam->passing_mark;
                        });
                        $resultValue = $allKnown ? ($allPass ? 'Pass' : 'Fail') : '-';
                    } else {
                        $resultValue = $percentageNumeric === null
                            ? '-'
                            : ($percentageNumeric >= 60 ? 'Pass' : 'Fail');
                    }

                    return [
                        'student_name' => $first->student->student_name ?? '-',
                        'roll_no_value' => $first->student->roll_no ?? '-',
                        'percentage_value' => $percentage,
                        'result_value' => $resultValue,
                        'view_url' => route('results.view', [
                            'student' => $first->student_id,
                            'exam' => $first->exam_id,
                        ]),
                    ];
                })
                ->values();

            $summaryRows = $summaryQuery->get()
                ->groupBy('student_id')
                ->map(function ($studentMarks) {
                    $totalObtained = (float) $studentMarks->whereNotNull('marks_obtained')->sum('marks_obtained');
                    $totalMarks = (float) $studentMarks->sum(function ($m) {
                        return (float) ($m->exam->total_mark ?? 0);
                    });
                    $percentageNumeric = $totalMarks > 0 ? (($totalObtained / $totalMarks) * 100) : null;

                    $hasPassingRules = $studentMarks->contains(fn($m) => $m->exam?->passing_mark !== null);
                    if ($hasPassingRules) {
                        $allKnown = $studentMarks->every(function ($m) {
                            return $m->marks_obtained !== null && $m->exam?->passing_mark !== null;
                        });
                        $allPass = $allKnown && $studentMarks->every(function ($m) {
                            return (float) $m->marks_obtained >= (float) $m->exam->passing_mark;
                        });
                        $resultValue = $allKnown ? ($allPass ? 'Pass' : 'Fail') : '-';
                    } else {
                        $resultValue = $percentageNumeric === null
                            ? '-'
                            : ($percentageNumeric >= 60 ? 'Pass' : 'Fail');
                    }

                    return [
                        'total_obtained' => $totalObtained,
                        'percentage_numeric' => $percentageNumeric,
                        'result_value' => $resultValue,
                    ];
                })
                ->values();

            $totalStudents = (int) $summaryRows->count();
            $passStudents = (int) $summaryRows->where('result_value', 'Pass')->count();
            $failStudents = (int) $summaryRows->where('result_value', 'Fail')->count();
            $highestMarks = $totalStudents > 0
                ? number_format((float) $summaryRows->max('total_obtained'), 2)
                : '-';
            $averagePercentage = $totalStudents > 0
                ? number_format((float) $summaryRows->pluck('percentage_numeric')->filter(fn($v) => $v !== null)->avg(), 2) . '%'
                : '-';

            $dt = DataTables::of($rows)->make(true);
            $payload = $dt->getData(true);
            $payload['summary'] = [
                'total_students' => $totalStudents,
                'pass_students' => $passStudents,
                'fail_students' => $failStudents,
                'highest_marks' => $highestMarks,
                'average_percentage' => $averagePercentage,
            ];

            return response()->json($payload);
        }

        $classes = Classes::where('status', 1)->get();
        $sections = Section::where('status', 1)->get();
        $examOptions = Exam::where('result_declared', 1)
            ->orderBy('name')
            ->get(['name'])
            ->pluck('name')
            ->filter(fn($name) => trim((string) $name) !== '')
            ->unique()
            ->values();

        $layout = $request->boolean('print') ? 'layouts.print' : 'layouts.admin';

        return view('results.admin', compact('classes', 'sections', 'examOptions', 'layout'));
    }

    public function show(Request $request, int $student, int $exam)
    {
        $referenceMark = ExamMark::with(['student.class', 'section', 'exam.academicYear'])
            ->where('student_id', $student)
            ->where('exam_id', $exam)
            ->whereHas('exam', fn($q) => $q->where('result_declared', 1))
            ->first();

        if (!$referenceMark) {
            abort(404, 'Result details not found.');
        }

        $selectedExamName = (string) ($referenceMark->exam?->name ?? '');
        $selectedAcademicYearId = (int) ($referenceMark->academic_year_id ?? 0);
        $selectedClassId = (int) ($referenceMark->class_id ?? 0);
        $selectedSectionId = (int) ($referenceMark->section_id ?? 0);

        $marks = ExamMark::with(['student.class', 'section', 'exam.academicYear', 'subject'])
            ->where('student_id', $student)
            ->where('class_id', $selectedClassId)
            ->where('section_id', $selectedSectionId)
            ->where('academic_year_id', $selectedAcademicYearId)
            ->whereHas('exam', function ($q) use ($selectedExamName, $selectedClassId, $selectedSectionId) {
                $q->where('result_declared', 1)
                    ->where('name', $selectedExamName)
                    ->where('class_id', $selectedClassId)
                    ->where(function ($sq) use ($selectedSectionId) {
                        $sq->where('section_id', $selectedSectionId)
                            ->orWhereNull('section_id');
                    });
            })
            ->orderBy('subject_id')
            ->get();

        if ($marks->isEmpty()) {
            abort(404, 'Result details not found.');
        }

        $first = $marks->first();
        $studentModel = $first->student;
        $section = $first->section;
        $examModel = $first->exam;
        $academicYearName = $examModel?->academicYear?->name ?? '-';
        $declaredDate = !empty($examModel?->updated_at)
            ? \Carbon\Carbon::parse($examModel->updated_at)->format('d M Y')
            : '-';

        $subjectRows = $marks->map(function ($mark) {
            $obtained = $mark->marks_obtained;
            $passing = $mark->exam?->passing_mark;
            $status = '-';
            if ($obtained !== null && $passing !== null) {
                $status = ((float) $obtained >= (float) $passing) ? 'Pass' : 'Fail';
            }
            return (object) [
                'subject_name' => $mark->subject->name ?? '-',
                'total_mark' => $mark->exam->total_mark ?? '-',
                'passing_mark' => $passing ?? '-',
                'obtained_mark' => $obtained ?? '-',
                'status' => $status,
            ];
        });

        $totalObtained = (float) $marks->whereNotNull('marks_obtained')->sum('marks_obtained');
        $totalMarks = (float) $marks->sum(fn($m) => (float) ($m->exam->total_mark ?? 0));
        $overallPercentage = $totalMarks > 0 ? (($totalObtained / $totalMarks) * 100) : null;
        $hasPassingRules = $marks->contains(fn($m) => $m->exam?->passing_mark !== null);
        if ($hasPassingRules) {
            $overallPass = $marks->count() > 0
                && $marks->every(function ($m) {
                    return $m->marks_obtained !== null
                        && $m->exam?->passing_mark !== null
                        && (float) $m->marks_obtained >= (float) $m->exam->passing_mark;
                });
        } else {
            $overallPass = $marks->count() > 0 && $overallPercentage !== null && $overallPercentage >= 60;
        }
        $overallResult = $overallPass ? 'Pass' : 'Fail';
        $resultMessage = $overallResult === 'Pass'
            ? 'Congratulations! The student has passed this exam.'
            : 'Student has failed in one or more subjects. Please improve in weak subjects.';

        $backRoute = route('results.index');
        $layout = $request->boolean('print') ? 'layouts.print' : 'layouts.admin';

        return view('results.teacher-view', compact(
            'studentModel',
            'section',
            'examModel',
            'academicYearName',
            'declaredDate',
            'subjectRows',
            'overallResult',
            'resultMessage',
            'backRoute',
            'layout'
        ));
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Classes;
use App\Models\Exam;
use App\Models\ExamMark;
use App\Models\ExamSubject;
use App\Models\ExamType;
use App\Models\Section;
use App\Models\Student;
use App\Models\Subject;
use App\Models\TeacherMapping;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExamController extends Controller
{
    private function selectedAcademicYearId(): ?int
    {
        $selected = session('selected_academic_year_id');
        if (!empty($selected)) {
            return (int) $selected;
        }

        return optional(AcademicYear::where('is_active', 1)->first())->id;
    }

    private function normalizedRole(): string
    {
        return strtolower((string) session('role'));
    }

    private function teacherSubjectIds(): array
    {
        $user = Auth::user();
        if (!$user || $this->normalizedRole() !== 'teacher') {
            return [];
        }

        $ids = Subject::where('teacher_id', $user->id)->pluck('id')->all();
        $mapped = TeacherMapping::where('teacher_id', $user->id)->pluck('subject_id')->all();

        return array_values(array_unique(array_map('intval', array_merge($ids, $mapped))));
    }

    private function teacherClassIds(): array
    {
        $subjectIds = $this->teacherSubjectIds();
        if (empty($subjectIds)) {
            return [];
        }

        return Subject::whereIn('id', $subjectIds)
            ->pluck('class_id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    public function type(Request $request)
    {
        $yearId = $this->selectedAcademicYearId();
        foreach (['Unit Test', 'Mid Term', 'Final Exam', 'Prelim', 'Annual'] as $defaultType) {
            ExamType::firstOrCreate(['name' => $defaultType], ['status' => 1]);
        }
        $examTypes = ExamType::query()->where('status', 1)->orderBy('name')->get();
        $classes = Classes::query()
            ->when($yearId, fn ($q) => $q->where('academic_year_id', $yearId))
            ->orderBy('name')
            ->get();

        $sections = Section::query()
            ->when($yearId, fn ($q) => $q->whereHas('class', fn ($cq) => $cq->where('academic_year_id', $yearId)))
            ->orderBy('name')
            ->get();

        $exams = Exam::with(['class', 'section', 'examType', 'examSubjects'])
            ->when($yearId, fn ($q) => $q->where('academic_year_id', $yearId))
            ->latest()
            ->get();

        return view('exams.type', compact('examTypes', 'classes', 'sections', 'exams', 'yearId'));
    }

    public function storeType(Request $request)
    {
        $yearId = $this->selectedAcademicYearId();

        $request->validate([
            'exam_type_id' => 'required|exists:exam_types,id',
            'class_id' => 'required|exists:classes,id',
            'section_id' => 'required|exists:sections,id',
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'result_publish_date' => 'nullable|date|after_or_equal:end_date',
            'status' => 'required|in:draft,published',
        ]);

        if ($yearId && !Classes::whereKey($request->class_id)->where('academic_year_id', $yearId)->exists()) {
            return back()->withErrors(['class_id' => 'Selected class does not belong to selected academic year.'])->withInput();
        }
        if (!Section::whereKey($request->section_id)->where('class_id', $request->class_id)->exists()) {
            return back()->withErrors(['section_id' => 'Selected section does not belong to selected class.'])->withInput();
        }

        Exam::create([
            'academic_year_id' => $yearId,
            'class_id' => $request->class_id,
            'section_id' => $request->section_id,
            'exam_type_id' => $request->exam_type_id,
            'name' => $request->name,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'result_publish_date' => $request->result_publish_date,
            'status' => $request->status,
            'creator_role' => $this->normalizedRole(),
            'creator_id' => Auth::id(),
        ]);

        $typeRoute = ($this->normalizedRole() === 'teacher') ? 'teacher.exams.type' : 'exams.type';
        return redirect()->route($typeRoute)->with('success', 'Exam created successfully.');
    }

    public function schedule(Request $request)
    {
        $yearId = $this->selectedAcademicYearId();
        $teacherSubjectIds = $this->teacherSubjectIds();
        $teacherClassIds = $this->teacherClassIds();
        $isTeacher = $this->normalizedRole() === 'teacher';

        $exams = Exam::with(['class', 'section', 'examType'])
            ->when($yearId, fn ($q) => $q->where('academic_year_id', $yearId))
            ->when($isTeacher, function ($q) use ($teacherClassIds) {
                if (empty($teacherClassIds)) {
                    $q->whereRaw('1 = 0');
                    return;
                }
                $q->whereIn('class_id', $teacherClassIds);
            })
            ->latest()
            ->get();

        $selectedExamId = $request->filled('exam_id') ? (int) $request->exam_id : (int) optional($exams->first())->id;
        $selectedExam = $selectedExamId ? $exams->firstWhere('id', $selectedExamId) : null;

        $subjects = Subject::query()
            ->when($selectedExam, fn ($q) => $q->where('class_id', $selectedExam->class_id))
            ->when($isTeacher, fn ($q) => $q->whereIn('id', $teacherSubjectIds))
            ->orderBy('name')
            ->get();

        $examSubjects = collect();
        if ($selectedExam) {
            $examSubjects = ExamSubject::with('subject')
                ->where('exam_id', $selectedExam->id)
                ->when($isTeacher, fn ($q) => $q->whereIn('subject_id', $teacherSubjectIds))
                ->orderBy('id')
                ->get();
        }

        return view('exams.schedule', compact('exams', 'selectedExam', 'subjects', 'examSubjects', 'yearId'));
    }

    public function storeSchedule(Request $request)
    {
        $teacherSubjectIds = $this->teacherSubjectIds();
        $isTeacher = $this->normalizedRole() === 'teacher';

        $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'subject_id' => 'required|exists:subjects,id',
            'theory_marks' => 'nullable|numeric|min:0|max:1000',
            'practical_marks' => 'nullable|numeric|min:0|max:1000',
            'internal_marks' => 'nullable|numeric|min:0|max:1000',
            'passing_marks' => 'required|numeric|min:0|max:1000',
        ]);

        $exam = Exam::findOrFail($request->exam_id);
        $subject = Subject::findOrFail($request->subject_id);

        if ((int) $subject->class_id !== (int) $exam->class_id) {
            return back()->withErrors(['subject_id' => 'Selected subject is not mapped to selected exam class.'])->withInput();
        }
        if ($isTeacher && !in_array((int) $subject->id, $teacherSubjectIds, true)) {
            return back()->withErrors(['subject_id' => 'You can only assign your own subjects.'])->withInput();
        }

        $theory = (float) $request->input('theory_marks', 0);
        $practical = (float) $request->input('practical_marks', 0);
        $internal = (float) $request->input('internal_marks', 0);
        $passing = (float) $request->input('passing_marks', 0);
        $total = $theory + $practical + $internal;
        if ($total <= 0) {
            return back()->withErrors(['theory_marks' => 'At least one mark component must be greater than 0.'])->withInput();
        }
        if ($passing > $total) {
            return back()->withErrors(['passing_marks' => 'Passing marks cannot exceed total marks.'])->withInput();
        }

        ExamSubject::updateOrCreate(
            [
                'exam_id' => $exam->id,
                'subject_id' => $subject->id,
            ],
            [
                'theory_marks' => $theory,
                'practical_marks' => $practical,
                'internal_marks' => $internal,
                'passing_marks' => $passing,
                'total_marks' => $total,
            ]
        );

        $scheduleRoute = ($this->normalizedRole() === 'teacher') ? 'teacher.exams.schedule' : 'exams.schedule';
        return redirect()->route($scheduleRoute, ['exam_id' => $exam->id])->with('success', 'Exam subject saved successfully.');
    }

    public function marks(Request $request)
    {
        $yearId = $this->selectedAcademicYearId();
        $teacherSubjectIds = $this->teacherSubjectIds();
        $isTeacher = $this->normalizedRole() === 'teacher';

        $exams = Exam::with(['class', 'section'])
            ->when($yearId, fn ($q) => $q->where('academic_year_id', $yearId))
            ->when($isTeacher, function ($q) use ($teacherSubjectIds) {
                if (empty($teacherSubjectIds)) {
                    $q->whereRaw('1 = 0');
                    return;
                }
                $q->whereHas('examSubjects', fn ($sq) => $sq->whereIn('subject_id', $teacherSubjectIds));
            })
            ->latest()
            ->get();

        $selectedExamId = $request->filled('exam_id') ? (int) $request->exam_id : (int) optional($exams->first())->id;
        $selectedExam = $selectedExamId ? $exams->firstWhere('id', $selectedExamId) : null;

        $examSubjects = collect();
        if ($selectedExam) {
            $examSubjects = ExamSubject::with('subject')
                ->where('exam_id', $selectedExam->id)
                ->when($isTeacher, fn ($q) => $q->whereIn('subject_id', $teacherSubjectIds))
                ->orderBy('id')
                ->get();
        }

        $selectedExamSubjectId = $request->filled('exam_subject_id') ? (int) $request->exam_subject_id : (int) optional($examSubjects->first())->id;
        $selectedExamSubject = $selectedExamSubjectId ? $examSubjects->firstWhere('id', $selectedExamSubjectId) : null;

        $students = collect();
        $marksMap = [];
        $studentSummaries = [];
        if ($selectedExam && $selectedExamSubject) {
            $students = Student::query()
                ->where('class_id', $selectedExam->class_id)
                ->where('section_id', $selectedExam->section_id)
                ->when($yearId, fn ($q) => $q->where('academic_year_id', $yearId))
                ->orderBy('student_name')
                ->get();

            $marksMap = ExamMark::where('exam_subject_id', $selectedExamSubject->id)
                ->get()
                ->keyBy('student_id')
                ->toArray();

            $allExamSubjects = ExamSubject::where('exam_id', $selectedExam->id)->get();
            $subjectIds = $allExamSubjects->pluck('id');
            $allMarks = ExamMark::whereIn('exam_subject_id', $subjectIds)->get()->groupBy('student_id');

            foreach ($students as $student) {
                $totalMax = 0;
                $totalObtained = 0;
                $overallPass = true;

                foreach ($allExamSubjects as $examSubjectRow) {
                    $totalMax += (float) $examSubjectRow->total_marks;
                    $markRow = optional($allMarks->get($student->id))->firstWhere('exam_subject_id', $examSubjectRow->id);
                    $obtained = is_null(optional($markRow)->obtained_marks) ? 0 : (float) $markRow->obtained_marks;
                    $totalObtained += $obtained;

                    $subjectPass = !$markRow
                        ? false
                        : !$markRow->is_absent && $obtained >= (float) $examSubjectRow->passing_marks;
                    if (!$subjectPass) {
                        $overallPass = false;
                    }
                }

                $percentage = $totalMax > 0 ? round(($totalObtained / $totalMax) * 100, 2) : 0;
                $grade = 'F';
                if ($percentage >= 90) {
                    $grade = 'A+';
                } elseif ($percentage >= 80) {
                    $grade = 'A';
                } elseif ($percentage >= 70) {
                    $grade = 'B+';
                } elseif ($percentage >= 60) {
                    $grade = 'B';
                } elseif ($percentage >= 50) {
                    $grade = 'C';
                } elseif ($percentage >= 35) {
                    $grade = 'D';
                }

                $studentSummaries[$student->id] = [
                    'total_obtained' => $totalObtained,
                    'total_max' => $totalMax,
                    'percentage' => $percentage,
                    'result' => $overallPass ? 'Pass' : 'Fail',
                    'grade' => $overallPass ? $grade : 'F',
                ];
            }
        }

        return view('exams.marks', compact(
            'exams',
            'selectedExam',
            'examSubjects',
            'selectedExamSubject',
            'students',
            'marksMap',
            'studentSummaries',
            'yearId'
        ));
    }

    public function saveMarks(Request $request)
    {
        $teacherSubjectIds = $this->teacherSubjectIds();
        $isTeacher = $this->normalizedRole() === 'teacher';

        $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'exam_subject_id' => 'required|exists:exam_subjects,id',
            'theory_marks' => 'nullable|array',
            'practical_marks' => 'nullable|array',
            'internal_marks' => 'nullable|array',
            'is_absent' => 'nullable|array',
        ]);

        $exam = Exam::findOrFail($request->exam_id);
        $examSubject = ExamSubject::with('subject')->findOrFail($request->exam_subject_id);
        if ((int) $examSubject->exam_id !== (int) $exam->id) {
            return back()->withErrors(['exam_subject_id' => 'Selected subject does not belong to selected exam.']);
        }
        if ($isTeacher && !in_array((int) $examSubject->subject_id, $teacherSubjectIds, true)) {
            return back()->withErrors(['exam_subject_id' => 'You can only enter marks for your own subjects.']);
        }

        $students = Student::query()
            ->where('class_id', $exam->class_id)
            ->where('section_id', $exam->section_id)
            ->pluck('id')
            ->all();
        $allowedStudentIds = array_map('intval', $students);

        foreach ($allowedStudentIds as $studentId) {
            $studentId = (int) $studentId;
            $isAbsent = (bool) ($request->input("is_absent.$studentId") ? true : false);

            $theory = $request->input("theory_marks.$studentId");
            $practical = $request->input("practical_marks.$studentId");
            $internal = $request->input("internal_marks.$studentId");

            $theory = is_numeric($theory) ? round((float) $theory, 2) : null;
            $practical = is_numeric($practical) ? round((float) $practical, 2) : null;
            $internal = is_numeric($internal) ? round((float) $internal, 2) : null;

            if (!is_null($theory) && ($theory < 0 || $theory > (float) $examSubject->theory_marks)) {
                return back()->withErrors([
                    "theory_marks.$studentId" => 'Theory marks must be between 0 and ' . $examSubject->theory_marks . '.',
                ])->withInput();
            }
            if (!is_null($practical) && ($practical < 0 || $practical > (float) $examSubject->practical_marks)) {
                return back()->withErrors([
                    "practical_marks.$studentId" => 'Practical marks must be between 0 and ' . $examSubject->practical_marks . '.',
                ])->withInput();
            }
            if (!is_null($internal) && ($internal < 0 || $internal > (float) $examSubject->internal_marks)) {
                return back()->withErrors([
                    "internal_marks.$studentId" => 'Internal marks must be between 0 and ' . $examSubject->internal_marks . '.',
                ])->withInput();
            }

            $obtained = null;
            if ($isAbsent) {
                $theory = null;
                $practical = null;
                $internal = null;
                $obtained = 0;
            } elseif (!is_null($theory) || !is_null($practical) || !is_null($internal)) {
                $obtained = (float) ($theory ?? 0) + (float) ($practical ?? 0) + (float) ($internal ?? 0);
            }

            ExamMark::updateOrCreate(
                [
                    'exam_id' => $exam->id,
                    'exam_subject_id' => $examSubject->id,
                    'student_id' => $studentId,
                ],
                [
                    'theory_marks' => $theory,
                    'practical_marks' => $practical,
                    'internal_marks' => $internal,
                    'obtained_marks' => $obtained,
                    'is_absent' => $isAbsent,
                    'entered_by_role' => $this->normalizedRole(),
                    'entered_by_id' => Auth::id(),
                ]
            );
        }

        $marksRoute = ($this->normalizedRole() === 'teacher') ? 'teacher.exams.marks' : 'exams.marks';
        return redirect()->route($marksRoute, [
            'exam_id' => $exam->id,
            'exam_subject_id' => $examSubject->id,
        ])->with('success', 'Marks saved successfully.');
    }

    public function results(Request $request)
    {
        $yearId = $this->selectedAcademicYearId();
        $teacherSubjectIds = $this->teacherSubjectIds();
        $isTeacher = $this->normalizedRole() === 'teacher';

        $exams = Exam::with(['class', 'section', 'examType'])
            ->when($yearId, fn ($q) => $q->where('academic_year_id', $yearId))
            ->when($isTeacher, function ($q) use ($teacherSubjectIds) {
                if (empty($teacherSubjectIds)) {
                    $q->whereRaw('1 = 0');
                    return;
                }
                $q->whereHas('examSubjects', fn ($sq) => $sq->whereIn('subject_id', $teacherSubjectIds));
            })
            ->latest()
            ->get();

        $selectedExamId = $request->filled('exam_id') ? (int) $request->exam_id : (int) optional($exams->first())->id;
        $selectedExam = $selectedExamId ? $exams->firstWhere('id', $selectedExamId) : null;

        $resultRows = collect();
        if ($selectedExam) {
            $examSubjects = ExamSubject::where('exam_id', $selectedExam->id)
                ->when($isTeacher, fn ($q) => $q->whereIn('subject_id', $teacherSubjectIds))
                ->get();

            if ($examSubjects->isNotEmpty()) {
                $students = Student::query()
                    ->where('class_id', $selectedExam->class_id)
                    ->where('section_id', $selectedExam->section_id)
                    ->when($yearId, fn ($q) => $q->where('academic_year_id', $yearId))
                    ->orderBy('student_name')
                    ->get(['id', 'student_name']);

                $examSubjectIds = $examSubjects->pluck('id');
                $totalMaxMarks = (float) $examSubjects->sum('total_marks');

                $marksByStudent = ExamMark::query()
                    ->where('exam_id', $selectedExam->id)
                    ->whereIn('exam_subject_id', $examSubjectIds)
                    ->get()
                    ->groupBy('student_id');

                $resultRows = $students->map(function ($student) use ($marksByStudent, $totalMaxMarks) {
                    $studentMarks = $marksByStudent->get($student->id, collect());
                    $totalObtained = (float) $studentMarks->sum(fn ($m) => (float) ($m->obtained_marks ?? 0));
                    $percentage = $totalMaxMarks > 0 ? round(($totalObtained / $totalMaxMarks) * 100, 2) : 0;

                    return [
                        'student_name' => $student->student_name,
                        'percentage' => $percentage,
                        'grade' => $this->gradeFromPercentage($percentage),
                    ];
                })->sortByDesc('percentage')->values();
            }
        }

        return view('results.index', compact('exams', 'selectedExam', 'resultRows', 'yearId'));
    }

    private function gradeFromPercentage(float $percentage): string
    {
        if ($percentage >= 90) {
            return 'A+';
        }
        if ($percentage >= 80) {
            return 'A';
        }
        if ($percentage >= 70) {
            return 'B+';
        }
        if ($percentage >= 60) {
            return 'B';
        }
        if ($percentage >= 50) {
            return 'C+';
        }
        if ($percentage >= 40) {
            return 'C';
        }

        return 'F';
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Exam;
use App\Models\ExamMark;
use App\Models\Classes;
use App\Models\Section;
use App\Models\Subject;
use App\Models\AcademicYear;
use App\Models\Student;
use App\Models\Grade;
use App\Models\TeacherMapping;
use Illuminate\Support\Facades\Auth;

class ExamMarkController extends Controller
{
    public function defaultGradeRules(): array
    {
        return [
            ['name' => 'A', 'start_mark' => 90, 'end_mark' => 100, 'description' => 'Excellent'],
            ['name' => 'B', 'start_mark' => 80, 'end_mark' => 89.99, 'description' => 'Good'],
            ['name' => 'C', 'start_mark' => 70, 'end_mark' => 79.99, 'description' => 'Fair/Average'],
            ['name' => 'D', 'start_mark' => 60, 'end_mark' => 69.99, 'description' => 'Poor/Barely Passing'],
            ['name' => 'F', 'start_mark' => 0, 'end_mark' => 59.99, 'description' => 'Fail'],
        ];
    }

    public function resolveGradeFromPercentage(float $percentage, \Illuminate\Support\Collection $gradeRules): ?array
    {
        $gradeInfo = Grade::resolveGrade($percentage);
        
        if ($gradeInfo['name'] === '-') {
            return null;
        }

        return $gradeInfo;
    }

    public function index(Request $request)
    {
        // Add permission check if needed
        // if (!auth()->user()->hasPermission('exam_marks.manage')) { ... }

        $role = session('role');
        $authId = session('auth_id');

        if ($role === 'teacher' && $authId) {
            $mappings = TeacherMapping::with(['section.class', 'subject'])
                ->where('teacher_id', $authId)
                ->get();

            $allowedClassIds = $mappings->pluck('section.class_id')
                ->filter()
                ->unique()
                ->values();
            $allowedSectionIds = $mappings->pluck('section_id')
                ->filter()
                ->unique()
                ->values();
            $allowedSubjectIds = $mappings->pluck('subject_id')
                ->filter()
                ->unique()
                ->values();

            $classes = Classes::with(['sections' => function ($q) use ($allowedSectionIds) {
                $q->whereIn('sections.id', $allowedSectionIds);
            }])->whereIn('id', $allowedClassIds)->get();

            $sections = Section::whereIn('id', $allowedSectionIds)->get();
            $subjects = Subject::whereIn('id', $allowedSubjectIds)->orderBy('name')->get();

            $examsQuery = Exam::with(['academicYear', 'class'])
                ->where('status', 1)
                ->orderBy('name');
            if ($mappings->isEmpty()) {
                $exams = collect();
            } else {
                $examsQuery->where(function ($q) use ($mappings) {
                    foreach ($mappings as $mapping) {
                        if ($mapping->section && $mapping->subject) {
                            $q->orWhere(function ($subQuery) use ($mapping) {
                                $normalizedSubject = strtolower(trim((string) $mapping->subject->name));
                                $subQuery->where('class_id', $mapping->section->class_id)
                                    ->whereRaw('LOWER(TRIM(subject_name)) = ?', [$normalizedSubject])
                                    ->where(function ($s) use ($mapping) {
                                        $s->where('section_id', $mapping->section_id)
                                            ->orWhereNull('section_id');
                                    });
                            });
                        }
                    }
                });
                $exams = $examsQuery->get();
            }

            $sectionSubjects = $mappings
                ->map(function ($mapping) {
                    return [
                        'section_id' => $mapping->section_id,
                        'class_id' => $mapping->section->class_id ?? null,
                        'subject_id' => $mapping->subject_id,
                    ];
                })
                ->filter(function ($item) {
                    return !empty($item['section_id']) && !empty($item['class_id']) && !empty($item['subject_id']);
                })
                ->values();
        } else {
            $classes = Classes::with('sections')->get();
            $sections = Section::all();
            $subjects = Subject::orderBy('name')->get();
            $exams = Exam::with(['academicYear', 'class'])
                ->where('status', 1)
                ->orderBy('name')
                ->get();
            $sectionSubjects = TeacherMapping::with(['section:id,class_id', 'subject:id'])
                ->get()
                ->map(function ($mapping) {
                    return [
                        'section_id' => $mapping->section_id,
                        'class_id' => $mapping->section->class_id ?? null,
                        'subject_id' => $mapping->subject_id,
                    ];
                })
                ->filter(function ($item) {
                    return !empty($item['section_id']) && !empty($item['class_id']) && !empty($item['subject_id']);
                })
                ->values();
        }

        $academicYears = AcademicYear::orderBy('name')->get();
        $grades = collect($this->defaultGradeRules());

        return view('exams.marks', compact(
            'classes',
            'sections',
            'subjects',
            'academicYears',
            'exams',
            'grades',
            'sectionSubjects'
        ));
    }

    public function data(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'section_id' => 'required|exists:sections,id',
            'subject_id' => 'required|exists:subjects,id',
            'exam_id' => 'required|exists:exams,id',
            'academic_year_id' => 'required|exists:academic_years,id',
        ]);

        $role = session('role');
        $authId = session('auth_id');

        if ($role === 'teacher' && $authId) {
            $hasMapping = TeacherMapping::where('teacher_id', $authId)
                ->where('section_id', $request->section_id)
                ->where('subject_id', $request->subject_id)
                ->exists();

            if (!$hasMapping) {
                return response()->json([
                    'message' => 'Unauthorized access to this section/subject.',
                ], 403);
            }
        }

        // Get students for the selected class and section
        // Assuming students have class_id and section_id or there is a mapping
        // Get students for the selected class and section
        $students = Student::where('class_id', $request->class_id)
            ->where('section_id', $request->section_id)
            ->where('academic_year_id', $request->academic_year_id)
            ->orderBy('roll_no')
            ->get();

        // Get existing marks for this specific exam
        $exam = Exam::findOrFail($request->exam_id);
        if ((int) $exam->status !== 1) {
            return response()->json([
                'message' => 'Selected exam is inactive.',
            ], 422);
        }
        $subject = Subject::findOrFail($request->subject_id);

        if ((int) $exam->class_id !== (int) $request->class_id
            || (int) $exam->academic_year_id !== (int) $request->academic_year_id
            || strtolower(trim((string) $exam->subject_name)) !== strtolower(trim((string) $subject->name))
            || (!is_null($exam->section_id) && (int) $exam->section_id !== (int) $request->section_id)
        ) {
            return response()->json([
                'message' => 'Selected exam does not match the class/section/subject/year context.',
            ], 422);
        }


        $marks = ExamMark::where('class_id', $request->class_id)
            ->where('section_id', $request->section_id)
            ->where('subject_id', $request->subject_id)
            ->where('exam_id', $request->exam_id)
            ->where('academic_year_id', $request->academic_year_id)
            ->get()
            ->keyBy('student_id');

        $rows = $students->map(function ($student) use ($marks) {
            $markEntry = $marks->get($student->id);
            return [
                'student_id' => $student->id,
                'student_name' => $student->student_name, // Verify column name in Student model
                'roll_no' => $student->roll_no,
                'marks_obtained' => $markEntry ? $markEntry->marks_obtained : null,
                'grade' => $markEntry ? $markEntry->grade : null,
                'remarks' => $markEntry ? $markEntry->remarks : null,
                'term' => $markEntry ? $markEntry->term : null,
                'mark_id' => $markEntry ? $markEntry->id : null,
            ];
        });

        return response()->json([
            'students' => $rows,
            'is_result_declared' => (bool) $exam->result_declared,
        ]);
    }

    public function store(Request $request)
    {
        \Illuminate\Support\Facades\Log::info('Exam Marks Store Request:', $request->all());

        try {
            $data = $request->validate([
                'class_id' => 'required|exists:classes,id',
                'section_id' => 'required|exists:sections,id',
                'subject_id' => 'required|exists:subjects,id',
                'exam_id' => 'required|exists:exams,id',
                'academic_year_id' => 'required|exists:academic_years,id',
                'marks' => 'required|array',
                'marks.*.student_id' => 'required|exists:students,id',
                'marks.*.marks_obtained' => 'nullable|numeric|min:0',
                'marks.*.grade' => 'nullable|string|max:10',
                'marks.*.remarks' => 'nullable|string',
                'marks.*.term' => 'nullable|string|max:255',
            ]);

            $role = session('role');
            $authId = session('auth_id');

            if ($role === 'teacher' && $authId) {
                $hasMapping = TeacherMapping::where('teacher_id', $authId)
                    ->where('section_id', $data['section_id'])
                    ->where('subject_id', $data['subject_id'])
                    ->exists();

                if (!$hasMapping) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Unauthorized access to save marks for this section/subject.',
                    ], 403);
                }
            }

            $exam = Exam::findOrFail($data['exam_id']);
            if ((int) $exam->status !== 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Selected exam is inactive.',
                ], 422);
            }
            $subject = Subject::findOrFail($data['subject_id']);
            if ((int) $exam->class_id !== (int) $data['class_id']
                || (int) $exam->academic_year_id !== (int) $data['academic_year_id']
                || strtolower(trim((string) $exam->subject_name)) !== strtolower(trim((string) $subject->name))
                || (!is_null($exam->section_id) && (int) $exam->section_id !== (int) $data['section_id'])
            ) {
                return response()->json([
                    'success' => false,
                    'message' => 'Selected exam does not match the class/section/subject/year context.',
                ], 422);
            }

            \Illuminate\Support\Facades\DB::beginTransaction();
            $gradeRules = collect($this->defaultGradeRules());
            foreach ($data['marks'] as $markData) {
                // Skip if marks_obtained is null/empty? No, user might want to clear marks.
                // But JS filters out empty marks.
                $marksObtained = isset($markData['marks_obtained']) ? (float) $markData['marks_obtained'] : null;
                $calculatedGrade = null;
                $gradeInfo = null;
                if ($marksObtained !== null && (float) $exam->total_mark > 0) {
                    $percentage = round(($marksObtained / (float) $exam->total_mark) * 100, 2);
                    $gradeInfo = $this->resolveGradeFromPercentage($percentage, $gradeRules);
                    $calculatedGrade = $gradeInfo['name'] ?? null;
                }
                
                ExamMark::updateOrCreate(
                    [
                        'exam_id' => $data['exam_id'],
                        'student_id' => $markData['student_id'],
                        'subject_id' => $data['subject_id'],
                        'academic_year_id' => $data['academic_year_id'],
                    ],
                    [
                        'class_id' => $data['class_id'],
                        'section_id' => $data['section_id'],
                        'marks_obtained' => $marksObtained,
                        'grade' => $calculatedGrade, // e.g., 'A', 'B'
                        'remarks' => $markData['remarks'] ?? $gradeInfo['description'] ?? null,
                        'term' => $markData['term'] ?? null,
                        'status' => 1
                    ]
                );
            }

            \Illuminate\Support\Facades\DB::commit();

            return response()->json(['success' => true, 'message' => 'Marks saved successfully.']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Illuminate\Support\Facades\Log::error('Exam Mark Validation Error: ' . json_encode($e->errors()));
             return response()->json([
                'success' => false, 
                'message' => 'Validation Error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
             \Illuminate\Support\Facades\Log::error('Exam Mark Store Error: ' . $e->getMessage());
             \Illuminate\Support\Facades\DB::rollBack();
             return response()->json(['success' => false, 'message' => 'Server Error: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        $mark = ExamMark::find($id);
        if (!$mark) {
            return response()->json([
                'success' => false,
                'message' => 'Mark entry not found.',
            ], 404);
        }

        $exam = Exam::find($mark->exam_id);
        if ($exam && (int) $exam->result_declared === 1) {
            return response()->json([
                'success' => false,
                'message' => 'Result already declared. Mark cannot be deleted.',
            ], 422);
        }

        $role = session('role');
        $authId = session('auth_id');

        if ($role === 'teacher' && $authId) {
            $hasMapping = TeacherMapping::where('teacher_id', $authId)
                ->where('section_id', $mark->section_id)
                ->where('subject_id', $mark->subject_id)
                ->exists();

            if (!$hasMapping) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access to delete marks for this section/subject.',
                ], 403);
            }
        }

        $mark->delete();

        return response()->json([
            'success' => true,
            'message' => 'Mark deleted successfully.',
        ]);
    }

    public function storeGrade(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:10',
            'start_mark' => 'required|numeric|min:0',
            'end_mark' => 'required|numeric|gte:start_mark',
            'point' => 'nullable|numeric|min:0',
        ]);

        Grade::create($request->all());

        return response()->json(['success' => true, 'message' => 'Grade added successfully.']);
    }

    public function destroyGrade($id)
    {
        $grade = Grade::find($id);
        if ($grade) {
            $grade->delete();
            return response()->json(['success' => true, 'message' => 'Grade deleted successfully.']);
        }
        return response()->json(['success' => false, 'message' => 'Grade not found.']);
    }

    public function getGrades()
    {
        $grades = Grade::orderBy('start_mark', 'desc')->get();
        return response()->json($grades);
    }
}

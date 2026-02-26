<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Classes;
use App\Models\Section;
use App\Models\AcademicYear;
use App\Models\ParentModel;
use App\Models\Role;
use App\Models\Exam;
use Illuminate\Support\Facades\Hash;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use App\Models\ExamMark;
use Illuminate\Support\Facades\Auth;

class StudentController extends Controller
{
    private function canPermission(string $permission): bool
    {
        /** @var \App\Models\Admin|\App\Models\Teacher|\App\Models\Student|\App\Models\ParentModel|\App\Models\User|null $user */
        $user = Auth::user();
        if (!$user || !method_exists($user, 'hasPermission')) {
            return false;
        }
        return $user->hasPermission($permission);
    }
    private function enforceOwnStudentAccess(Student $student): void
    {
        $role = session('role');
        /** @var \App\Models\Admin|\App\Models\Teacher|\App\Models\Student|\App\Models\ParentModel|\App\Models\User|null $user */
        $user = Auth::user();

        if ($role === 'student' && $user && (int) $student->id !== (int) $user->id) {
            abort(403, 'Unauthorized access');
        }

        if ($role === 'parent' && $user && (int) $student->parent_id !== (int) $user->id) {
            abort(403, 'Unauthorized access');
        }
    }

    public function index()
    {
        return view('students.index');
    }

    public function create()
    {
        if (!$this->canPermission('student_add')) {
            abort(403, 'Unauthorized access');
        }
        $classes = Classes::where('status', 1)->get();
        $academicYears = AcademicYear::all();
        $parents = ParentModel::all();
        $roles = Role::all();
        return view('students.create', compact('classes', 'academicYears', 'parents', 'roles'));
    }

    public function getStudents(Request $request)
    {
        $students = Student::with(['class', 'section'])->latest();

        $role = session('role');
        $user = Auth::user();
        if ($role === 'student' && $user) {
            $students->where('id', $user->id);
        }
        if ($role === 'parent' && $user) {
            $students->where('parent_id', $user->id);
        }

        // 🔍 Global search (FIXED)
        if ($search = $request->get('search')['value'] ?? null) {
            $students->where(function ($q) use ($search) {
                $q->where('student_name', 'like', "%{$search}%")
                    ->orWhere('roll_no', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // 🎯 Filter: Class
        if ($request->class_id) {
            $students->where('class_id', $request->class_id);
        }

        // 🎯 Filter: Status
        if ($request->status !== null && $request->status !== '') {
            $students->where('status', $request->status);
        }

        return DataTables::of($students)
            ->addIndexColumn()
            ->addColumn('avatar', function ($row) {
                return $row->profile_image
                    ? asset('uploads/students/' . $row->profile_image)
                    : 'https://ui-avatars.com/api/?name=' . urlencode($row->student_name) . '&background=5D59E0&color=fff';
            })
            ->addColumn('name', fn($row) => $row->student_name)
            ->addColumn('roll_no', fn($row) => $row->roll_no ?? '-')
            ->addColumn('username', fn($row) => $row->username)
            ->addColumn('email', fn($row) => $row->email)
            ->addColumn('status', fn($row) => $row->status)
            ->addColumn('action', function ($row) {
                $actions = '<div class="d-flex justify-content-end gap-1">';

                if ($this->canPermission('student_view')) {
                    $actions .= '
                <a href="' . route('students.show', $row->id) . '" class="btn btn-sm btn-light">
                    <i class="fas fa-eye"></i>
                </a>';
                }

                if ($this->canPermission('student_edit')) {
                    $actions .= '
                <a href="' . route('students.edit', $row->id) . '" class="btn btn-sm btn-light">
                    <i class="fas fa-pen"></i>
                </a>';
                }

                if ($this->canPermission('student_delete')) {
                    $actions .= '
                <form method="POST" action="' . route('students.destroy', $row->id) . '" onsubmit="return confirm(\'Delete?\')">
                    ' . csrf_field() . method_field('DELETE') . '
                    <button class="btn btn-sm btn-light">
                        <i class="fas fa-trash text-danger"></i>
                    </button>
                </form>';
                }

                $actions .= '</div>';
                return $actions;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        if (!$this->canPermission('student_add')) {
            abort(403, 'Unauthorized access');
        }
        $validated = $request->validate([
            'role_id' => 'required|exists:roles,id',

            'student_name' => 'required|string|max:255',
            'roll_no'      => 'required|string|max:50',
            'username'     => 'required|string|max:255|unique:students,username',
            'email'        => 'required|email|max:255|unique:students,email',
            'password'     => 'required|string|min:8',

            'mobile_no'    => 'nullable|string|max:20',
            'gender'       => 'nullable|in:male,female,other',
            'date_of_birth' => 'nullable|date',

            'address'      => 'nullable|string|max:500',
            'city'         => 'nullable|string|max:100',
            'state'        => 'nullable|string|max:100',
            'pincode'      => 'nullable|string|max:10',

            'class_id'         => 'required|exists:classes,id',
            'section_id'       => 'required|exists:sections,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'parent_id'        => 'nullable|exists:parents,id',

            'profile_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',

            'status' => 'required|boolean',
        ]);

        DB::transaction(function () use ($validated, $request) {

            // Password hash
            $validated['password'] = Hash::make($request->password);

            // Image upload
            if ($request->hasFile('profile_image')) {
                $file = $request->file('profile_image');
                $filename = uniqid('student_') . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('uploads/students'), $filename);
                $validated['profile_image'] = $filename;
            }

            Student::create($validated);
        });

        return redirect()
            ->route('students.index')
            ->with('success', 'Student admission completed successfully.');
    }

    public function show($id)
    {
        $student = Student::with(['class', 'section', 'academicYear', 'parent'])->findOrFail($id);
        $this->enforceOwnStudentAccess($student); // Ensures student/parent can only see their own/child's profile

        $user = Auth::user();
        $role = session('role');

        $marksQuery = ExamMark::with(['exam', 'subject'])
            ->whereHas('exam', fn($q) => $q->where('result_declared', 1));

        if ($role === 'student' && $user) {
            // For student role, we also need to calculate overall result.
            $marksForOverall = (clone $marksQuery)->where('student_id', $id)->get();
            if ($marksForOverall->isNotEmpty()) {
                $totalObtained = (float) $marksForOverall->whereNotNull('marks_obtained')->sum('marks_obtained');
                $totalMarks = (float) $marksForOverall->sum(fn($m) => (float) ($m->exam->total_mark ?? 0));
                $overallPercentage = $totalMarks > 0 ? (($totalObtained / $totalMarks) * 100) : null;
                $hasPassingRules = $marksForOverall->contains(fn($m) => $m->exam?->passing_mark !== null);

                if ($hasPassingRules) {
                    $overallPass = $marksForOverall->every(function ($m) {
                        return $m->marks_obtained !== null
                            && $m->exam?->passing_mark !== null
                            && (float) $m->marks_obtained >= (float) $m->exam->passing_mark;
                    });
                } else {
                    $overallPass = $overallPercentage !== null && $overallPercentage >= 60;
                }
                view()->share('overallResult', $overallPass ? 'Pass' : 'Fail');
            }

            $marksQuery->where('student_id', $user->id);
        } elseif ($role === 'parent' && $user) {
            // This logic is for a parent viewing a specific child's results.
            // The enforceOwnStudentAccess already validates the parent-child relationship.
            $marksQuery->where('student_id', $student->id);
        } else {
            $marksQuery->where('student_id', $id);
        }
        $marks = $marksQuery->latest('updated_at')->get();
        return view('students.show', compact('student', 'marks'));
    }

    public function edit($id)
    {
        if (!$this->canPermission('student_edit')) {
            abort(403, 'Unauthorized access');
        }
        $student = Student::findOrFail($id);
        $this->enforceOwnStudentAccess($student);
        $classes = Classes::where('status', 1)->get();
        $sections = Section::where('class_id', $student->class_id)->get();
        $academicYears = AcademicYear::all();
        $parents = ParentModel::all();
        $roles = Role::all();
        return view('students.edit', compact('student', 'classes', 'sections', 'academicYears', 'parents', 'roles'));
    }

    public function update(Request $request, $id)
    {
        if (!$this->canPermission('student_edit')) {
            abort(403, 'Unauthorized access');
        }
        $student = Student::findOrFail($id);
        $this->enforceOwnStudentAccess($student);

        $request->validate([
            'student_name' => 'required|string|max:255',
            'roll_no' => 'required|string|max:50',
            'username' => 'required|string|unique:students,username,'.$id,
            'email' => 'required|email|unique:students,email,'.$id,
            'class_id' => 'required|exists:classes,id',
            'section_id' => 'required|exists:sections,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'role_id' => 'required|exists:roles,id',
            'status' => 'required|boolean',
        ]);

        $data = $request->all();
        if (isset($data['status'])) {
            $data['status'] = (int) $data['status'];
        }
        if($request->password){
            $data['password'] = Hash::make($request->password);
        } else {
            unset($data['password']);
        }

        if ($request->hasFile('profile_image')) {
            if($student->profile_image && file_exists(public_path('uploads/students/'.$student->profile_image))){
                unlink(public_path('uploads/students/'.$student->profile_image));
            }
            $img = $request->file('profile_image');
            $name = time().'.'.$img->getClientOriginalExtension();
            $img->move(public_path('uploads/students'), $name);
            $data['profile_image'] = $name;
        }

        $student->update($data);

        return redirect()->route('students.index')->with('success', 'Student updated successfully.');
    }

    public function destroy($id)
    {
        if (!$this->canPermission('student_delete')) {
            abort(403, 'Unauthorized access');
        }
        $student = Student::findOrFail($id);
        $this->enforceOwnStudentAccess($student);
        if($student->profile_image && file_exists(public_path('uploads/students/'.$student->profile_image))){
            unlink(public_path('uploads/students/'.$student->profile_image));
        }
        $student->delete();
        return redirect()->route('students.index')->with('success', 'Student deleted successfully.');
    }

    public function dashboard()
    {
        return view('dashboard.student');
    }

    public function getSections($class_id)
    {
        $sections = Section::where('class_id', $class_id)->where('status', 1)->get();
        return response()->json($sections);
    }

    public function getClassDetails($class_id)
    {
        $class = Classes::with('teacher')->find($class_id);
        return response()->json([
            'teacher_name' => $class && $class->teacher ? $class->teacher->name : 'No teacher assigned'
        ]);
    }

    public function results(Request $request)
    {
        $studentId = (int) session('auth_id');
        if (session('role') !== 'student' || $studentId <= 0) {
            abort(403, 'Unauthorized access.');
        }

        $student = Student::with(['class:id,name', 'section:id,name', 'academicYear:id,name'])->findOrFail($studentId);

        $baseQuery = ExamMark::with(['exam', 'subject', 'student.class'])
            ->where('student_id', $studentId)
            ->where('class_id', $student->class_id)
            ->where('section_id', $student->section_id)
            ->whereHas('exam', function ($query) use ($student) {
                $query->where('result_declared', 1)
                    ->where('class_id', $student->class_id)
                    ->where(function ($q) use ($student) {
                        $q->whereNull('section_id')
                            ->orWhere('section_id', $student->section_id);
                    });
            });

        $examOptionsBase = Exam::with(['class:id,name', 'section:id,name', 'academicYear:id,name'])
            ->where('result_declared', 1)
            ->where('class_id', $student->class_id)
            ->where(function ($query) use ($student) {
                $query->whereNull('section_id')
                    ->orWhere('section_id', $student->section_id);
            })
            ->whereIn('id', (clone $baseQuery)->pluck('exam_id')->unique()->values())
            ->orderBy('name')
            ->get(['id', 'name', 'class_id', 'section_id', 'academic_year_id']);

        $selectedClassId = (int) $student->class_id;
        $selectedSectionId = (int) $student->section_id;
        $sessionOptions = $examOptionsBase
            ->map(fn($exam) => $exam->academicYear)
            ->filter()
            ->unique('id')
            ->sortBy('name')
            ->values();

        $selectedSessionId = $request->filled('session_id')
            ? (int) $request->session_id
            : (int) ($student->academic_year_id ?? 0);
        if ($selectedSessionId && !$sessionOptions->contains('id', $selectedSessionId)) {
            $selectedSessionId = (int) ($sessionOptions->first()->id ?? 0);
        }
        $selectedSession = $selectedSessionId ? $sessionOptions->firstWhere('id', $selectedSessionId) : null;

        $examOptions = $selectedSessionId
            ? $examOptionsBase->where('academic_year_id', $selectedSessionId)->values()
            : collect();
        $examNameOptions = $examOptions
            ->pluck('name')
            ->filter(fn($name) => trim((string) $name) !== '')
            ->unique()
            ->values();

        $selectedExamName = $request->filled('exam_name') ? trim((string) $request->exam_name) : null;
        if ($selectedExamName && !$examNameOptions->contains($selectedExamName)) {
            $selectedExamName = null;
        }

        $enteredRollNo = trim((string) $request->input('roll_no', (string) ($student->roll_no ?? '')));

        $marksQuery = (clone $baseQuery)->where('academic_year_id', $selectedSessionId);
        if ($selectedExamName) {
            $marksQuery->whereHas('exam', function ($query) use ($selectedExamName) {
                $query->where('name', $selectedExamName);
            });
        }

        if ($request->ajax()) {
            $ajaxQuery = (clone $baseQuery);
            if (!empty($selectedSessionId)) {
                $ajaxQuery->where('academic_year_id', $selectedSessionId);
            }
            if (!empty($selectedExamName)) {
                $ajaxQuery->whereHas('exam', function ($query) use ($selectedExamName) {
                    $query->where('name', $selectedExamName);
                });
            } else {
                // Keep table empty until exam is selected.
                $ajaxQuery->whereRaw('1 = 0');
            }

            return DataTables::of($ajaxQuery)
                ->addIndexColumn()
                ->addColumn('subject_name', fn($row) => $row->subject->name ?? '-')
                ->addColumn('grade_value', fn($row) => $row->grade ?? '-')
                ->addColumn('total_mark_value', fn($row) => $row->exam->total_mark ?? '-')
                ->addColumn('passing_mark_value', fn($row) => $row->exam->passing_mark ?? '-')
                ->addColumn('obtained_mark_value', fn($row) => $row->marks_obtained ?? '-')
                ->make(true);
        }

        $marks = $marksQuery->latest('updated_at')->get();

        $selectedExam = $selectedExamName
            ? $examOptions->firstWhere('name', $selectedExamName)
            : null;
        $selectedStudentName = $student->student_name ?? '-';
        $selectedRollNo = $enteredRollNo !== '' ? $enteredRollNo : ($student->roll_no ?? '-');
        $selectedAcademicYearName = $selectedSession->name ?? ($student->academicYear->name ?? '-');
        $selectedClassSectionName = ($student->class->name ?? '-')
            . ($student->section ? ' - ' . $student->section->name : '');
        $selectedExamType = $selectedExamName ?: '-';

        $canShowResult = !empty($selectedSessionId)
            && !empty($selectedExamName)
            && trim((string) $selectedStudentName) !== ''
            && trim((string) $selectedStudentName) !== '-'
            && trim((string) $selectedRollNo) !== ''
            && trim((string) $selectedRollNo) !== '-'
            && trim((string) $selectedAcademicYearName) !== ''
            && trim((string) $selectedAcademicYearName) !== '-'
            && trim((string) $selectedClassSectionName) !== ''
            && trim((string) $selectedClassSectionName) !== '-';

        $overallGrade = null;
        if ($canShowResult && $marks->isNotEmpty()) {
            $totalObtained = (float) $marks->whereNotNull('marks_obtained')->sum('marks_obtained');
            $totalMarks = (float) $marks->sum(fn($m) => (float) ($m->exam->total_mark ?? 0));
            if ($totalMarks > 0) {
                $overallPercentage = round(($totalObtained / $totalMarks) * 100, 2);
                
                // Use the same grading rules as ExamMarkController
                $examMarkController = new ExamMarkController();
                // This requires making resolveGradeFromPercentage public in ExamMarkController
                // For now, let's duplicate the logic to avoid changing method visibility.
                $gradeRules = collect((new \App\Http\Controllers\Admin\ExamMarkController)->defaultGradeRules());
                $gradeInfo = (new \App\Http\Controllers\Admin\ExamMarkController)->resolveGradeFromPercentage($overallPercentage, $gradeRules);
                $overallGrade = $gradeInfo['name'] ?? null;
            }
        }

        return view('results.index', [
            'marks' => $marks,
            'studentProfile' => $student,
            'selectedExam' => $selectedExam,
            'selectedSession' => $selectedSession,
            'studentResultFilters' => true,
            'classOptions' => collect([$student->class])->filter(),
            'sectionOptions' => collect([$student->section])->filter(),
            'sessionOptions' => $sessionOptions,
            'examOptions' => $examOptions,
            'examNameOptions' => $examNameOptions,
            'selectedClassId' => $selectedClassId,
            'selectedSectionId' => $selectedSectionId,
            'selectedSessionId' => $selectedSessionId,
            'selectedExamName' => $selectedExamName,
            'enteredRollNo' => $enteredRollNo,
            'selectedStudentName' => $selectedStudentName,
            'selectedRollNo' => $selectedRollNo,
            'selectedAcademicYearName' => $selectedAcademicYearName,
            'selectedClassSectionName' => $selectedClassSectionName,
            'selectedExamType' => $selectedExamType,
            'canShowResult' => $canShowResult,
            'overallGrade' => $overallGrade,
            'useAjaxStudentResults' => true,
            'layout' => $request->boolean('print') ? 'layouts.print' : 'layouts.admin',
        ]);
    }
}

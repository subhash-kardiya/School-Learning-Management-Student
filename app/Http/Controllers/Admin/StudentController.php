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
use Illuminate\Support\Facades\Hash;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class StudentController extends Controller
{
    private function assertSectionAssignable(int $classId, int $sectionId): Section
    {
        $section = Section::whereKey($sectionId)
            ->where('class_id', $classId)
            ->first();

        if (!$section) {
            throw ValidationException::withMessages([
                'section_id' => 'Selected section does not belong to selected class.',
            ]);
        }

        return $section;
    }

    private function assertSectionCapacity(int $sectionId, int $capacity, ?int $ignoreStudentId = null): void
    {
        $studentsInSection = Student::where('section_id', $sectionId)
            ->when($ignoreStudentId, fn($q) => $q->where('id', '!=', $ignoreStudentId))
            ->count();

        if ($studentsInSection >= $capacity) {
            throw ValidationException::withMessages([
                'section_id' => "This section is full. Capacity is {$capacity} students.",
            ]);
        }
    }

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
        $classes = Classes::with(['sections' => function ($q) {
            $q->where('status', 1)->orderBy('name');
        }])
            ->where('status', 1)
            ->when(session('selected_academic_year_id'), function ($q) {
                $q->where('academic_year_id', session('selected_academic_year_id'));
            })
            ->orderBy('name')
            ->get();

        return view('students.index', compact('classes'));
    }

    public function create()
    {
        if (!$this->canPermission('student_add')) {
            abort(403, 'Unauthorized access');
        }
        $classes = Classes::where('status', 1)->get();
        $academicYears = AcademicYear::all();
        return view('students.create', compact('classes', 'academicYears'));
    }

    public function getStudents(Request $request)
    {
        $students = Student::with(['class', 'section'])
            ->when(session('selected_academic_year_id'), function($q) {
                $q->where('academic_year_id', session('selected_academic_year_id'));
            })
            ->latest();

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
        if ($request->section_id) {
            $students->where('section_id', $request->section_id);
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
            'student_name' => 'required|string|max:255',
            'roll_no'      => 'required|string|max:50',
            'username'     => 'required|string|max:255|unique:students,username',
            'email'        => 'required|email|max:255|unique:students,email',
            'password'     => 'required|string|min:8',

            'mobile_no'    => 'required|digits_between:10,15',
            'gender'       => 'required|in:male,female,other',
            'date_of_birth' => 'required|date|before:today',

            'address'      => 'required|string|max:500',
            'city'         => 'required|string|max:100',
            'state'        => 'required|string|max:100',
            'pincode'      => 'required|digits:6',

            'class_id'         => 'required|exists:classes,id',
            'section_id'       => 'required|exists:sections,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'parent_name'      => 'required|string|max:255',
            'parent_username'  => 'required|string|max:255',
            'parent_email'     => 'required|email|max:255',
            'parent_password'  => 'nullable|string|min:8',
            'parent_mobile_no' => 'required|digits_between:10,15',

            'profile_image' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',

            'status' => 'required|boolean',
        ]);

        DB::transaction(function () use ($validated, $request) {
            $section = $this->assertSectionAssignable((int) $validated['class_id'], (int) $validated['section_id']);
            $this->assertSectionCapacity((int) $section->id, (int) $section->capacity);

            $studentRole = Role::whereRaw('LOWER(name) = ?', ['student'])->first();
            $parentRole = Role::whereRaw('LOWER(name) = ?', ['parent'])->first();
            if (!$studentRole || !$parentRole) {
                throw ValidationException::withMessages([
                    'role' => 'Student/Parent role not found. Please seed roles first.',
                ]);
            }

            $parentByEmail = ParentModel::where('email', $validated['parent_email'])->first();
            $parentByUsername = ParentModel::where('username', $validated['parent_username'])->first();
            if ($parentByEmail && $parentByUsername && $parentByEmail->id !== $parentByUsername->id) {
                throw ValidationException::withMessages([
                    'parent_email' => 'Parent email and username belong to different users.',
                ]);
            }

            $parent = $parentByEmail ?: $parentByUsername;
            if (!$parent && empty($validated['parent_password'])) {
                throw ValidationException::withMessages([
                    'parent_password' => 'Parent password is required for new parent.',
                ]);
            }

            if ($parent) {
                $parent->update([
                    'role_id' => $parentRole->id,
                    'parent_name' => $validated['parent_name'],
                    'username' => $validated['parent_username'],
                    'email' => $validated['parent_email'],
                    'mobile_no' => $validated['parent_mobile_no'],
                    'address' => $validated['address'],
                    'status' => (int) $validated['status'],
                    'password' => !empty($validated['parent_password'])
                        ? Hash::make($validated['parent_password'])
                        : $parent->password,
                ]);
            } else {
                $parent = ParentModel::create([
                    'role_id' => $parentRole->id,
                    'parent_name' => $validated['parent_name'],
                    'username' => $validated['parent_username'],
                    'email' => $validated['parent_email'],
                    'password' => Hash::make($validated['parent_password']),
                    'mobile_no' => $validated['parent_mobile_no'],
                    'address' => $validated['address'],
                    'status' => (int) $validated['status'],
                ]);
            }

            // Password hash
            $validated['password'] = Hash::make($request->password);
            $validated['role_id'] = $studentRole->id;
            $validated['parent_id'] = $parent->id;

            // Image upload
            if ($request->hasFile('profile_image')) {
                $file = $request->file('profile_image');
                $filename = uniqid('student_') . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('uploads/students'), $filename);
                $validated['profile_image'] = $filename;
            }

            $studentData = collect($validated)->except([
                'parent_name',
                'parent_username',
                'parent_email',
                'parent_password',
                'parent_mobile_no',
            ])->toArray();

            Student::create($studentData);
        });

        return redirect()
            ->route('students.index')
            ->with('success', 'Student admission completed successfully.');
    }

    public function show($id)
    {
        $student = Student::with(['class', 'section', 'academicYear', 'parent'])->findOrFail($id);
        $this->enforceOwnStudentAccess($student);
        return view('students.show', compact('student'));
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
        return view('students.edit', compact('student', 'classes', 'sections', 'academicYears'));
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
            'password' => 'nullable|string|min:8',
            'mobile_no'    => 'required|digits_between:10,15',
            'gender'       => 'required|in:male,female,other',
            'date_of_birth' => 'required|date|before:today',
            'address'      => 'required|string|max:500',
            'city'         => 'required|string|max:100',
            'state'        => 'required|string|max:100',
            'pincode'      => 'required|digits:6',
            'class_id' => 'required|exists:classes,id',
            'section_id' => 'required|exists:sections,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'parent_name'      => 'required|string|max:255',
            'parent_username'  => 'required|string|max:255',
            'parent_email'     => 'required|email|max:255',
            'parent_password'  => 'nullable|string|min:8',
            'parent_mobile_no' => 'required|digits_between:10,15',
            'profile_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
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

        $section = $this->assertSectionAssignable((int) $request->class_id, (int) $request->section_id);
        $this->assertSectionCapacity((int) $section->id, (int) $section->capacity, (int) $student->id);

        $studentRole = Role::whereRaw('LOWER(name) = ?', ['student'])->first();
        $parentRole = Role::whereRaw('LOWER(name) = ?', ['parent'])->first();
        if (!$studentRole || !$parentRole) {
            throw ValidationException::withMessages([
                'role' => 'Student/Parent role not found. Please seed roles first.',
            ]);
        }

        $parentByEmail = ParentModel::where('email', $request->parent_email)->first();
        $parentByUsername = ParentModel::where('username', $request->parent_username)->first();
        if ($parentByEmail && $parentByUsername && $parentByEmail->id !== $parentByUsername->id) {
            throw ValidationException::withMessages([
                'parent_email' => 'Parent email and username belong to different users.',
            ]);
        }

        $parent = $parentByEmail ?: $parentByUsername;
        if (!$parent && empty($request->parent_password)) {
            throw ValidationException::withMessages([
                'parent_password' => 'Parent password is required for new parent.',
            ]);
        }

        if ($parent) {
            $parent->update([
                'role_id' => $parentRole->id,
                'parent_name' => $request->parent_name,
                'username' => $request->parent_username,
                'email' => $request->parent_email,
                'mobile_no' => $request->parent_mobile_no,
                'address' => $request->address,
                'status' => (int) $request->status,
                'password' => $request->filled('parent_password')
                    ? Hash::make($request->parent_password)
                    : $parent->password,
            ]);
        } else {
            $parent = ParentModel::create([
                'role_id' => $parentRole->id,
                'parent_name' => $request->parent_name,
                'username' => $request->parent_username,
                'email' => $request->parent_email,
                'password' => Hash::make($request->parent_password),
                'mobile_no' => $request->parent_mobile_no,
                'address' => $request->address,
                'status' => (int) $request->status,
            ]);
        }

        $data['role_id'] = $studentRole->id;
        $data['parent_id'] = $parent->id;
        unset(
            $data['parent_name'],
            $data['parent_username'],
            $data['parent_email'],
            $data['parent_password'],
            $data['parent_mobile_no']
        );

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
}

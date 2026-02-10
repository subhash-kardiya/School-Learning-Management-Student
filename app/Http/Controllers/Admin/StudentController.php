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
}

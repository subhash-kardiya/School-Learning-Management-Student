<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ParentModel;
use App\Models\Role;
use App\Models\Student;
use Illuminate\Support\Facades\Hash;
use Yajra\DataTables\Facades\DataTables;

class ParentController extends Controller
{
    public function index()
    {
        return view('parents.index');
    }

    public function getParents(Request $request)
    {
        $parents = ParentModel::latest();

        if ($search = $request->get('search')['value'] ?? null) {
            $parents->where(function ($q) use ($search) {
                $q->where('parent_name', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('mobile_no', 'like', "%{$search}%");
            });
        }

        if ($request->status !== null && $request->status !== '') {
            $parents->where('status', $request->status);
        }

        return DataTables::of($parents)
            ->addIndexColumn()
            ->addColumn('avatar', function ($row) {
                return $row->profile_image
                    ? asset('uploads/parents/' . $row->profile_image)
                    : 'https://ui-avatars.com/api/?name=' . urlencode($row->parent_name) . '&background=5D59E0&color=fff';
            })
            ->addColumn('name', fn($row) => $row->parent_name)
            ->addColumn('mobile_no', fn($row) => $row->mobile_no ?? '-')
            ->addColumn('username', fn($row) => $row->username)
            ->addColumn('email', fn($row) => $row->email)
            ->addColumn('status', function ($row) {
                return $row->status == 1
                    ? '<span class="status-badge status-active">Active</span>'
                    : '<span class="status-badge status-inactive">Inactive</span>';
            })
            ->addColumn('action', function ($row) {
                return '
                <div class="d-flex justify-content-end gap-1">
                    <a href="' . route('parents.show', $row->id) . '" class="btn btn-sm btn-light">
                        <i class="fas fa-eye"></i>
                    </a>
                    <a href="' . route('parents.edit', $row->id) . '" class="btn btn-sm btn-light">
                        <i class="fas fa-pen"></i>
                    </a>
                    <form method="POST" action="' . route('parents.destroy', $row->id) . '" onsubmit="return confirm(\'Delete this parent?\')">
                        ' . csrf_field() . method_field('DELETE') . '
                        <button class="btn btn-sm btn-light">
                            <i class="fas fa-trash text-danger"></i>
                        </button>
                    </form>
                </div>';
            })
            ->rawColumns(['status', 'action'])
            ->make(true);
    }

    public function create()
    {
        $parentRole = Role::where('name', 'Parent')->orWhere('name', 'parent')->first();
        $roles = $parentRole ? collect([$parentRole]) : Role::all();
        $students = Student::orderBy('student_name')->get();
        return view('parents.create', compact('roles', 'students', 'parentRole'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'role_id' => 'required|exists:roles,id',
            'parent_name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:parents,username',
            'email' => 'required|email|max:255|unique:parents,email',
            'password' => 'required|string|min:8',
            'mobile_no' => 'nullable|string|max:20',
            'gender' => 'nullable|in:male,female,other',
            'date_of_birth' => 'nullable|date',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'pincode' => 'nullable|string|max:10',
            'profile_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'status' => 'required|boolean',
            'student_ids' => 'array',
            'student_ids.*' => 'exists:students,id',
        ]);

        $validated['password'] = Hash::make($request->password);
        $parentRole = Role::where('name', 'Parent')->orWhere('name', 'parent')->first();
        if ($parentRole) {
            $validated['role_id'] = $parentRole->id;
        }

        if ($request->hasFile('profile_image')) {
            $file = $request->file('profile_image');
            $filename = uniqid('parent_') . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/parents'), $filename);
            $validated['profile_image'] = $filename;
        }

        $parent = ParentModel::create($validated);

        if ($request->filled('student_ids')) {
            Student::whereIn('id', $request->student_ids)->update(['parent_id' => $parent->id]);
        }

        return redirect()->route('parents.index')->with('success', 'Parent created successfully.');
    }

    public function show($id)
    {
        $parent = ParentModel::with(['students.class', 'students.section', 'students.academicYear'])->findOrFail($id);
        return view('parents.show', compact('parent'));
    }

    public function edit($id)
    {
        $parent = ParentModel::findOrFail($id);
        $parentRole = Role::where('name', 'Parent')->orWhere('name', 'parent')->first();
        $roles = $parentRole ? collect([$parentRole]) : Role::all();
        $students = Student::orderBy('student_name')->get();
        $assignedStudentIds = Student::where('parent_id', $parent->id)->pluck('id')->toArray();
        return view('parents.edit', compact('parent', 'roles', 'students', 'assignedStudentIds', 'parentRole'));
    }

    public function update(Request $request, $id)
    {
        $parent = ParentModel::findOrFail($id);

        $validated = $request->validate([
            'role_id' => 'required|exists:roles,id',
            'parent_name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:parents,username,' . $id,
            'email' => 'required|email|max:255|unique:parents,email,' . $id,
            'password' => 'nullable|string|min:8',
            'mobile_no' => 'nullable|string|max:20',
            'gender' => 'nullable|in:male,female,other',
            'date_of_birth' => 'nullable|date',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'pincode' => 'nullable|string|max:10',
            'profile_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'status' => 'required|boolean',
            'student_ids' => 'array',
            'student_ids.*' => 'exists:students,id',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }
        $parentRole = Role::where('name', 'Parent')->orWhere('name', 'parent')->first();
        if ($parentRole) {
            $validated['role_id'] = $parentRole->id;
        }

        if ($request->hasFile('profile_image')) {
            if ($parent->profile_image && file_exists(public_path('uploads/parents/' . $parent->profile_image))) {
                unlink(public_path('uploads/parents/' . $parent->profile_image));
            }
            $file = $request->file('profile_image');
            $filename = uniqid('parent_') . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/parents'), $filename);
            $validated['profile_image'] = $filename;
        }

        $parent->update($validated);

        $studentIds = $request->input('student_ids', []);
        Student::where('parent_id', $parent->id)
            ->whereNotIn('id', $studentIds)
            ->update(['parent_id' => null]);
        if (!empty($studentIds)) {
            Student::whereIn('id', $studentIds)->update(['parent_id' => $parent->id]);
        }

        return redirect()->route('parents.index')->with('success', 'Parent updated successfully.');
    }

    public function destroy($id)
    {
        $parent = ParentModel::findOrFail($id);
        if ($parent->profile_image && file_exists(public_path('uploads/parents/' . $parent->profile_image))) {
            unlink(public_path('uploads/parents/' . $parent->profile_image));
        }
        $parent->delete();

        return redirect()->route('parents.index')->with('success', 'Parent deleted successfully.');
    }

    public function dashboard()
    {
        return view('dashboard.parent');
    }
}

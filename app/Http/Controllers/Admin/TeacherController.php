<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Models\Role;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Yajra\DataTables\Facades\DataTables;

class TeacherController extends Controller
{
    // LIST
    public function index(Request $request)
    {
        if ($request->ajax()) {

            $query = Teacher::latest();
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            return DataTables::of($query)
                ->addIndexColumn()

                ->addColumn('teacher_info', function ($row) {

                    $avatar = 'https://ui-avatars.com/api/?name=' . urlencode($row->name) . '&background=5D59E0&color=fff';

                    if ($row->profile_image) {
                        $avatar = asset('uploads/teachers/' . $row->profile_image);
                    }

                    return '
                    <div class="d-flex align-items-center gap-3">
                        <img src="' . $avatar . '" class="faculty-avatar">
                        <div>
                            <div class="fw-semibold">' . $row->name . '</div>
                            <div class="text-muted small">Faculty</div>
                        </div>
                    </div>';
                })

                ->addColumn('username', function ($row) {
                    return '<span class="teacher-username">' . e($row->username) . '</span>';
                })

                ->addColumn('email', function ($row) {
                    return '
                    <div class="teacher-email">
                        <i class="fas fa-envelope text-muted"></i>
                        <span>' . e($row->email) . '</span>
                    </div>';
                })

                ->addColumn('status', function ($row) {
                    return $row->status == 1
                        ? '<span class="badge badge-soft-success">Active</span>'
                        : '<span class="badge badge-soft-danger">Inactive</span>';
                })

                ->addColumn('action', function ($row) {
                    $user = auth()->user();
                    $actions = '<div class="d-flex justify-content-end gap-1">';

                    if ($user && $user->hasPermission('teacher_view')) {
                        $actions .= '
                        <a href="' . route('teachers.show', $row->id) . '" class="btn btn-sm btn-light" title="View">
                            <i class="fas fa-eye"></i>
                        </a>';
                    }

                    if ($user && $user->hasPermission('teacher_edit')) {
                        $actions .= '
                        <a href="' . route('teachers.edit', $row->id) . '" class="btn btn-sm btn-light" title="Edit">
                            <i class="fas fa-pen"></i>
                        </a>';
                    }

                    if ($user && $user->hasPermission('teacher_delete')) {
                        $actions .= '
                        <form action="' . route('teachers.destroy', $row->id) . '" method="POST" onsubmit="return confirm(\'Delete this faculty?\')" style="display:inline">
                            ' . csrf_field() . method_field('DELETE') . '
                            <button type="submit" class="btn btn-sm btn-light text-danger">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>';
                    }

                    $actions .= '</div>';
                    return $actions;
                })

                ->filterColumn('teacher_info', function ($query, $keyword) {
                    $query->where(function ($q) use ($keyword) {
                        $q->where('name', 'like', "%{$keyword}%")
                            ->orWhere('email', 'like', "%{$keyword}%");
                    });
                })

                ->filterColumn('username', function ($query, $keyword) {
                    $query->where('username', 'like', "%{$keyword}%");
                })

                ->filterColumn('email', function ($query, $keyword) {
                    $query->where('email', 'like', "%{$keyword}%");
                })

                // ✅ ONLY real HTML columns
                ->rawColumns(['teacher_info', 'username', 'email', 'status', 'action'])

                ->make(true);
        }

        return view('teachers.index');
    }

    // CREATE
    public function create()
    {
        $user = auth()->user();
        if (!$user || !$user->hasPermission('teacher_add')) {
            abort(403, 'Unauthorized access');
        }
        $teacherRole = Role::where('name', 'Teacher')->orWhere('name', 'teacher')->first();
        $roles = $teacherRole ? collect([$teacherRole]) : Role::all();
        $subjects = Subject::orderBy('name')->get();
        return view('teachers.create', compact('roles', 'subjects', 'teacherRole'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        if (!$user || !$user->hasPermission('teacher_add')) {
            abort(403, 'Unauthorized access');
        }
        $request->validate([
            'role_id'  => 'required',
            'name'     => 'required',
            'email'    => 'required|email|unique:teachers',
            'username' => 'required|unique:teachers',
            'password' => 'required|min:6',
            'subject_ids' => 'array',
            'subject_ids.*' => 'exists:subjects,id',
        ]);

        $data = $request->all();
        $teacherRole = Role::where('name', 'Teacher')->orWhere('name', 'teacher')->first();
        if ($teacherRole) {
            $data['role_id'] = $teacherRole->id;
        }
        $data['password'] = Hash::make($request->password);

        if ($request->hasFile('profile_image')) {
            $img = $request->file('profile_image');
            $name = time() . '.' . $img->getClientOriginalExtension();
            $img->move(public_path('uploads/teachers'), $name);
            $data['profile_image'] = $name;
        }

        $teacher = Teacher::create($data);

        if ($request->filled('subject_ids')) {
            Subject::whereIn('id', $request->subject_ids)->update(['teacher_id' => $teacher->id]);
        }

        return redirect()->route('teachers.index')->with('success', 'Teacher Added Successfully');
    }

    // EDIT
    public function edit($id)
    {
        $user = auth()->user();
        if (!$user || !$user->hasPermission('teacher_edit')) {
            abort(403, 'Unauthorized access');
        }
        $teacher = Teacher::findOrFail($id);
        $teacherRole = Role::where('name', 'Teacher')->orWhere('name', 'teacher')->first();
        $roles = $teacherRole ? collect([$teacherRole]) : Role::all();
        $subjects = Subject::orderBy('name')->get();
        $assignedSubjectIds = Subject::where('teacher_id', $teacher->id)->pluck('id')->toArray();
        return view('teachers.edit', compact('teacher', 'roles', 'subjects', 'assignedSubjectIds', 'teacherRole'));
    }

    // UPDATE
    public function update(Request $request, $id)
    {
        $user = auth()->user();
        if (!$user || !$user->hasPermission('teacher_edit')) {
            abort(403, 'Unauthorized access');
        }
        $teacher = Teacher::findOrFail($id);

        $request->validate([
            'role_id'  => 'required',
            'name'     => 'required',
            'email'    => 'required|email|unique:teachers,email,' . $id,
            'username' => 'required|unique:teachers,username,' . $id,
            'subject_ids' => 'array',
            'subject_ids.*' => 'exists:subjects,id',
        ]);

        $data = $request->all();
        $teacherRole = Role::where('name', 'Teacher')->orWhere('name', 'teacher')->first();
        if ($teacherRole) {
            $data['role_id'] = $teacherRole->id;
        }

        if ($request->password) {
            $data['password'] = Hash::make($request->password);
        } else {
            unset($data['password']);
        }

        if ($request->hasFile('profile_image')) {
            if ($teacher->profile_image && file_exists(public_path('uploads/teachers/' . $teacher->profile_image))) {
                unlink(public_path('uploads/teachers/' . $teacher->profile_image));
            }

            $img = $request->file('profile_image');
            $name = time() . '.' . $img->getClientOriginalExtension();
            $img->move(public_path('uploads/teachers'), $name);
            $data['profile_image'] = $name;
        }

        $teacher->update($data);

        $subjectIds = $request->input('subject_ids', []);
        Subject::where('teacher_id', $teacher->id)
            ->whereNotIn('id', $subjectIds)
            ->update(['teacher_id' => null]);
        if (!empty($subjectIds)) {
            Subject::whereIn('id', $subjectIds)->update(['teacher_id' => $teacher->id]);
        }

        return redirect()->route('teachers.index')->with('success', 'Teacher Updated');
    }

    // SHOW
    public function show($id)
    {
        $teacher = Teacher::with(['role', 'subjects'])->findOrFail($id);
        return view('teachers.show', compact('teacher'));
    }

    // DELETE
    public function destroy($id)
    {
        $user = auth()->user();
        if (!$user || !$user->hasPermission('teacher_delete')) {
            abort(403, 'Unauthorized access');
        }
        Teacher::findOrFail($id)->delete();
        return back()->with('success', 'Teacher Deleted');
    }

    // TEACHER DASHBOARD
   
}

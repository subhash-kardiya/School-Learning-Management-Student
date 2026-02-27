<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\TeacherMapping;
use App\Models\Teacher;
use App\Models\Section;
use App\Models\Classes;
use App\Models\Subject;

class TeacherMappingController extends Controller
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

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = TeacherMapping::with(['teacher', 'section.class', 'subject'])->latest();
            return datatables()->of($query)
                ->addIndexColumn()
                ->addColumn('teacher_name', function($row){
                    return $row->teacher ? '<span class="fw-bold text-dark">'.$row->teacher->name.'</span>' : '<span class="text-muted small">N/A</span>';
                })
                ->addColumn('mapping_info', function($row){
                    $class = $row->section && $row->section->class ? $row->section->class->name : 'N/A';
                    $section = $row->section ? $row->section->name : 'N/A';
                    return '<span class="fw-semibold text-primary">'.$class.'</span> <span class="text-mutedmx-1">•</span> <span class="fw-semibold text-dark">'.$section.'</span>';
                })
                ->addColumn('subject_name', function($row){
                    return $row->subject ? '<span class="fw-semibold">'.$row->subject->name.'</span>' : '<span class="text-muted small">N/A</span>';
                })
                ->addColumn('action', function($row){
                    if (!$this->canPermission('class_manage')) {
                        return '<div class="d-flex justify-content-end"></div>';
                    }
                    return '<div class="d-flex justify-content-end">
                        <form action="'.route('teacher.mapping.destroy', $row->id).'" method="POST" class="d-inline" onsubmit="return confirm(\'Remove this mapping?\')">
                            ' . csrf_field() . method_field('DELETE') . '
                            <button type="submit" class="btn-action btn-delete-soft" title="Delete"><i class="fas fa-trash"></i></button>
                        </form>
                    </div>';
                })
                ->rawColumns(['action', 'DT_RowIndex', 'teacher_name', 'mapping_info', 'subject_name'])
                ->make(true);
        }
        $teachers = Teacher::all();
        $classes = Classes::with('sections')->get();
        $subjects = Subject::orderBy('name')->get();
        return view('teacher-mapping.index', compact('teachers', 'classes', 'subjects'));
    }

    public function store(Request $request)
    {
        if (!$this->canPermission('class_manage')) {
            abort(403, 'Unauthorized access');
        }
        $request->validate([
            'teacher_id' => 'required|exists:teachers,id',
            'section_id' => 'required|exists:sections,id',
            'subject_id' => 'required|exists:subjects,id',
        ]);

        // Prevent duplicate mapping
        $exists = TeacherMapping::where('teacher_id', $request->teacher_id)
                                ->where('section_id', $request->section_id)
                                ->where('subject_id', $request->subject_id)
                                ->exists();
        if ($exists) {
            return redirect()->back()->with('success', 'Mapping already exists.');
        }

        TeacherMapping::create($request->all());
        return redirect()->route('teacher.mapping')->with('success', 'Teacher mapping saved successfully.');
    }

    public function destroy($id)
    {
        if (!$this->canPermission('class_manage')) {
            abort(403, 'Unauthorized access');
        }
        TeacherMapping::findOrFail($id)->delete();
        return redirect()->route('teacher.mapping')->with('success', 'Mapping deleted.');
    }
}


<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Classes;
use Illuminate\Http\Request;
use App\Models\AcademicYear;
use App\Models\Teacher;

class ClassController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Classes::with(['teacher', 'academicYear'])->latest();
            return datatables()->of($query)
                ->addIndexColumn()
                ->addColumn('academic_year', function($row){
                    return $row->academicYear ? '<span class="fw-semibold text-dark">'.$row->academicYear->name.'</span>' : '<span class="text-muted small">N/A</span>';
                })
                ->addColumn('teacher', function($row){
                    return $row->teacher ? '<div class="d-flex align-items-center">
                                <div class="fw-bold text-dark">'.$row->teacher->name.'</div>
                            </div>' : '<span class="text-muted small">N/A</span>';
                })
                ->addColumn('status', function($row){
                    return $row->status == 1 
                        ? '<span class="badge-soft-success">Active</span>' 
                        : '<span class="badge-soft-danger">Inactive</span>';
                })
                ->addColumn('action', function($row){
                    return '<div class="d-flex justify-content-end gap-1">
                        <a href="'.route('classes.show', $row->id).'" class="btn-action btn-view-soft" title="View"><i class="fas fa-eye"></i></a>
                        <a href="'.route('classes.edit', $row->id).'" class="btn-action btn-edit-soft" title="Edit"><i class="fas fa-pen"></i></a>
                        <form action="'.route('classes.destroy', $row->id).'" method="POST" class="d-inline" onsubmit="return confirm(\'Delete this class?\')">
                            ' . csrf_field() . method_field('DELETE') . '
                            <button type="submit" class="btn-action btn-delete-soft" title="Delete"><i class="fas fa-trash"></i></button>
                        </form>
                    </div>';
                })
                ->filterColumn('academic_year', function($query, $keyword) {
                    $query->whereHas('academicYear', function($q) use ($keyword) {
                        $q->where('name', 'like', "%{$keyword}%");
                    });
                })
                ->filterColumn('teacher', function($query, $keyword) {
                    $query->whereHas('teacher', function($q) use ($keyword) {
                        $q->where('name', 'like', "%{$keyword}%");
                    });
                })
                ->rawColumns(['status', 'action', 'academic_year', 'teacher', 'DT_RowIndex'])
                ->make(true);
        }
        $classes = Classes::all();
        return view('classes.index', compact('classes'));
    }

   public function create()
{
    $academicYears = AcademicYear::all(); // Academic years fetch
    $teachers = Teacher::all(); // Teachers fetch

    return view('classes.create', compact('academicYears', 'teachers'));
}
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'academic_year_id' => 'required|integer|exists:academic_years,id',
            'class_teacher_id' => 'required|integer|exists:teachers,id',
            'status' => 'required|integer|in:0,1',
        ]);

        Classes::create($request->all());

        return redirect()->route('classes.index')->with('success', 'Class added successfully.');
    }

    public function show($id)
    {
        $class = Classes::findOrFail($id);
        $academicYears = AcademicYear::all();
        $teachers = Teacher::all();

        return view('classes.show', compact('class', 'academicYears', 'teachers'));
    }

    public function edit($id) {
        $class = Classes::findOrFail($id);
        $academicYears = AcademicYear::all();
        $teachers = Teacher::all();

        return view('classes.edit', compact('class', 'academicYears', 'teachers'));
    }

public function update(Request $request, $id) {
    $request->validate([
        'name' => 'required|string|max:255',
        'academic_year_id' => 'required|integer|exists:academic_years,id',
        'class_teacher_id' => 'required|integer|exists:teachers,id',
        'status' => 'required|integer|in:0,1',
    ]);

    $class = Classes::findOrFail($id);
    $class->update($request->all());
    return redirect()->route('classes.index')->with('success', 'Class updated successfully.');
}

public function destroy($id) {
    $class = Classes::findOrFail($id);
    $class->delete();
    return redirect()->route('classes.index')->with('success', 'Class deleted successfully.');
}

}

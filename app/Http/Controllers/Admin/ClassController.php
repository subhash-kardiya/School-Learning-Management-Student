<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Classes;
use Illuminate\Http\Request;
use App\Models\AcademicYear;
use App\Models\Teacher;
use Illuminate\Validation\Rule;

class ClassController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Classes::with(['teacher', 'academicYear'])
                ->when(session('selected_academic_year_id'), function($q) {
                    $q->where('academic_year_id', session('selected_academic_year_id'));
                })
                ->when($request->filled('class_name'), function ($q) use ($request) {
                    $q->where('name', $request->input('class_name'));
                })
                ->latest();
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
        $classes = Classes::query()
            ->when(session('selected_academic_year_id'), function ($q) {
                $q->where('academic_year_id', session('selected_academic_year_id'));
            })
            ->select('name')
            ->distinct()
            ->orderBy('name')
            ->get();
        return view('classes.index', compact('classes'));
    }

   public function create()
{
    $academicYears = AcademicYear::where('is_active', 1)->where('is_locked', 0)->orderBy('name')->get(); // Active academic year only
    $teachers = Teacher::all(); // Teachers fetch

    return view('classes.create', compact('academicYears', 'teachers'));
}
    public function store(Request $request)
    {
        $activeYearId = (int) (AcademicYear::where('is_active', 1)->where('is_locked', 0)->value('id') ?? 0);
        if ($activeYearId <= 0) {
            return back()->with('error', 'No active academic year available.')->withInput();
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'academic_year_id' => [
                'required',
                'integer',
                Rule::exists('academic_years', 'id')->where(fn ($q) => $q->where('is_active', 1)->where('is_locked', 0)),
            ],
            'class_teacher_id' => 'required|integer|exists:teachers,id',
            'status' => 'required|integer|in:0,1',
        ]);

        $payload = $request->only(['name', 'class_teacher_id', 'status']);
        $payload['academic_year_id'] = $activeYearId;
        Classes::create($payload);

        return redirect()->route('classes.index')->with('success', 'Class added successfully.');
    }

    public function show($id)
    {
        $class = Classes::findOrFail($id);
        $academicYears = AcademicYear::where('is_active', 1)->where('is_locked', 0)->orderBy('name')->get();
        $teachers = Teacher::all();

        return view('classes.show', compact('class', 'academicYears', 'teachers'));
    }

    public function edit($id) {
        $class = Classes::findOrFail($id);
        $academicYears = AcademicYear::where('is_active', 1)->where('is_locked', 0)->orderBy('name')->get();
        $teachers = Teacher::all();

        return view('classes.edit', compact('class', 'academicYears', 'teachers'));
    }

public function update(Request $request, $id) {
    $activeYearId = (int) (AcademicYear::where('is_active', 1)->where('is_locked', 0)->value('id') ?? 0);
    if ($activeYearId <= 0) {
        return back()->with('error', 'No active academic year available.')->withInput();
    }

    $request->validate([
        'name' => 'required|string|max:255',
        'academic_year_id' => [
            'required',
            'integer',
            Rule::exists('academic_years', 'id')->where(fn ($q) => $q->where('is_active', 1)->where('is_locked', 0)),
        ],
        'class_teacher_id' => 'required|integer|exists:teachers,id',
        'status' => 'required|integer|in:0,1',
    ]);

    $class = Classes::findOrFail($id);
    $payload = $request->only(['name', 'class_teacher_id', 'status']);
    $payload['academic_year_id'] = $activeYearId;
    $class->update($payload);
    return redirect()->route('classes.index')->with('success', 'Class updated successfully.');
}

public function destroy($id) {
    $class = Classes::findOrFail($id);
    $class->delete();
    return redirect()->route('classes.index')->with('success', 'Class deleted successfully.');
}

}

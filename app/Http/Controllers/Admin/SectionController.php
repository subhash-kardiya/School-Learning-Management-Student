<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Section;
use App\Models\Classes; // Your Class model
use Illuminate\Http\Request;

class SectionController extends Controller
{
    // List all sections
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Section::with('class')
                ->whereHas('class', function($q) {
                    $q->when(session('selected_academic_year_id'), function($sq) {
                        $sq->where('academic_year_id', session('selected_academic_year_id'));
                    });
                })
                ->when($request->filled('class_id'), function ($q) use ($request) {
                    $q->where('class_id', (int) $request->input('class_id'));
                })
                ->latest();
            return datatables()->of($query)
                ->addIndexColumn()
                ->addColumn('class_name', function($row){
                    return $row->class ? '<span class="fw-semibold text-dark">'.$row->class->name.'</span>' : '<span class="text-muted small">N/A</span>';
                })
                ->addColumn('status', function($row){
                    return $row->status == 1
                        ? '<span class="badge-soft-success">Active</span>'
                        : '<span class="badge-soft-danger">Inactive</span>';
                })
                ->addColumn('capacity', function($row){
                    return $row->capacity !== null
                        ? '<span class="fw-semibold text-dark">'.$row->capacity.'</span>'
                        : '<span class="text-muted small">N/A</span>';
                })
                ->addColumn('action', function($row){
                    return '<div class="d-flex justify-content-end gap-1">
                        <a href="'.route('section.show', $row->id).'" class="btn-action btn-view-soft" title="View"><i class="fas fa-eye"></i></a>
                        <a href="'.route('section.edit', $row->id).'" class="btn-action btn-edit-soft" title="Edit"><i class="fas fa-pen"></i></a>
                        <form action="'.route('section.destroy', $row->id).'" method="POST" class="d-inline" onsubmit="return confirm(\'Delete this section?\')">
                            ' . csrf_field() . method_field('DELETE') . '
                            <button type="submit" class="btn-action btn-delete-soft" title="Delete"><i class="fas fa-trash"></i></button>
                        </form>
                    </div>';
                })
                ->filterColumn('class_name', function($query, $keyword) {
                    $query->whereHas('class', function($q) use ($keyword) {
                        $q->where('name', 'like', "%{$keyword}%");
                    });
                })
                ->rawColumns(['status', 'action', 'class_name', 'capacity', 'DT_RowIndex'])
                ->make(true);
        }
        $classes = Classes::query()
            ->when(session('selected_academic_year_id'), function ($q) {
                $q->where('academic_year_id', session('selected_academic_year_id'));
            })
            ->orderBy('name')
            ->get(); // For dropdown in add form and list filter
        return view('section.index', compact('classes'));
    }

    public function create()
    {
        $classes = Classes::query()
            ->when(session('selected_academic_year_id'), function ($q) {
                $q->where('academic_year_id', session('selected_academic_year_id'));
            })
            ->orderBy('name')
            ->get();

        return view('section.create', compact('classes'));
    }

    public function show($id)
    {
        $section = Section::with('class')->findOrFail($id);
        return view('section.show', compact('section'));
    }

    public function edit($id)
    {
        $section = Section::findOrFail($id);
        $classes = Classes::all();
        return view('section.edit', compact('section', 'classes'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string',
            'class_id' => 'required|exists:classes,id',
            'capacity' => 'required|integer|min:1',
            'status' => 'required|boolean',
        ]);

        $section = Section::findOrFail($id);
        $section->update($request->all());

        return redirect()->route('section.index')->with('success', 'Section updated successfully!');
    }

    // Store new section
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'class_id' => 'required|exists:classes,id',
            'capacity' => 'required|integer|min:1',
            'status' => 'required|boolean',
        ]);

        Section::create($request->all());
        return redirect()->route('section.index')->with('success', 'Section added successfully!');
    }

    // Delete section
    public function destroy($id)
    {
        $section = Section::findOrFail($id);
        $section->delete();
        return redirect()->route('section.index')->with('success', 'Section deleted successfully!');
    }
}

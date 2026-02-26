<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Subject;
use App\Models\Classes;
use Yajra\DataTables\Facades\DataTables;

class SubjectController extends Controller
{
    /**
     * Subject list (Datatable)
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $yearId = session('selected_academic_year_id');
            $subjects = Subject::with(['class'])
                ->when($yearId, function ($query) use ($yearId) {
                    $query->whereHas('class', function ($q) use ($yearId) {
                        $q->where('academic_year_id', $yearId);
                    });
                })
                ->when($request->filled('class_id'), function ($query) use ($request) {
                    $query->where('class_id', (int) $request->input('class_id'));
                })
                ->latest();

            return DataTables::of($subjects)
                ->addIndexColumn()

                ->addColumn('class', function ($row) {
                    return '<span class="fw-semibold">' . ($row->class->name ?? 'N/A') . '</span>';
                })

                ->addColumn('status', function ($row) {
                    return $row->status
                        ? '<span class="badge bg-success">Active</span>'
                        : '<span class="badge bg-danger">Inactive</span>';
                })

                ->addColumn('action', function ($row) {
                    return '
                    <div class="d-flex justify-content-end gap-1">

                        <!-- SHOW -->
                        <a href="' . route('subjects.show', $row->id) . '"
                           class="btn btn-sm btn-info" title="View">
                            <i class="fas fa-eye"></i>
                        </a>

                        <!-- EDIT -->
                        <a href="' . route('subjects.edit', $row->id) . '"
                           class="btn btn-sm btn-primary" title="Edit">
                            <i class="fas fa-pen"></i>
                        </a>

                        <!-- DELETE -->
                        <form action="' . route('subjects.destroy', $row->id) . '"
                              method="POST" class="d-inline">
                            ' . csrf_field() . method_field('DELETE') . '
                            <button type="submit" class="btn btn-sm btn-danger"
                                onclick="return confirm(\'Delete this subject?\')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>

                    </div>';
                })

                ->rawColumns(['class', 'status', 'action'])
                ->make(true);
        }

        $classes = Classes::query()
            ->when(session('selected_academic_year_id'), function ($q) {
                $q->where('academic_year_id', session('selected_academic_year_id'));
            })
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('subjects.index', compact('classes'));
    }

    public function create()
    {
        $classes = Classes::query()
            ->when(session('selected_academic_year_id'), function ($q) {
                $q->where('academic_year_id', session('selected_academic_year_id'));
            })
            ->orderBy('name')
            ->get();

        return view('subjects.create', compact('classes'));
    }

    /**
     * Store
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'         => 'required|string|max:255',
            'subject_code' => 'required|string|max:50|unique:subjects,subject_code',
            'class_id'     => 'required|exists:classes,id',
            'status'       => 'required|boolean',
        ]);

        Subject::create($request->only(['name', 'subject_code', 'class_id', 'status']));

        return redirect()->route('subjects.index')
            ->with('success', 'Subject created successfully.');
    }

    /**
     * SHOW
     */
    public function show($id)
    {
        $subject = Subject::with(['class'])->findOrFail($id);

        return view('subjects.show', compact('subject'));
    }

    /**
     * EDIT
     */
    public function edit($id)
    {
        $subject  = Subject::findOrFail($id);
        $classes  = Classes::all();

        return view('subjects.edit', compact('subject', 'classes'));
    }

    /**
     * UPDATE
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name'         => 'required|string|max:255',
            'subject_code' => 'required|string|max:50|unique:subjects,subject_code,' . $id,
            'class_id'     => 'required|exists:classes,id',
            'status'       => 'required|boolean',
        ]);

        Subject::findOrFail($id)->update($request->only(['name', 'subject_code', 'class_id', 'status']));

        return redirect()->route('subjects.index')
            ->with('success', 'Subject updated successfully.');
    }

    /**
     * DELETE
     */
    public function destroy($id)
    {
        Subject::findOrFail($id)->delete();

        return redirect()->route('subjects.index')
            ->with('success', 'Subject deleted successfully.');
    }
}

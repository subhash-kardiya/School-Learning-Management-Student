<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AcademicYear;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

// app/Http/Controllers/Admin/AcademicYearController.php

class AcademicYearController extends Controller
{
    private function hasLinkedRecords(int $academicYearId): bool
    {
        $tables = [
            'classes',
            'students',
            'timetables',
            'homeworks',
            'exams',
            'certificates',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table) && DB::table($table)->where('academic_year_id', $academicYearId)->exists()) {
                return true;
            }
        }

        return false;
    }

    public function index(Request $request) {
        if ($request->ajax()) {
            $query = AcademicYear::latest();
            return datatables()->of($query)
                ->addIndexColumn()
                ->addColumn('status', function ($row) {
                    $active = $row->is_active ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-light text-dark">Inactive</span>';
                    $locked = $row->is_locked ? '<span class="badge bg-danger ms-1">Locked</span>' : '<span class="badge bg-light text-dark ms-1">Open</span>';
                    return $active . $locked;
                })
                ->addColumn('action', function ($row) {
                    $setInactive = $row->is_active ? '<a href="' . route('academic.year.inactive', $row->id) . '"
                            class="btn-action btn-delete-soft"
                            title="Set Inactive"
                            onclick="return confirm(\'Set this academic year inactive?\')">
                                <i class="fas fa-toggle-off"></i>
                            </a>' : '';

                    $setActive = (!$row->is_active && !$row->is_locked) ? '<a href="' . route('academic.year.active', $row->id) . '"
                            class="btn-action btn-edit-soft"
                            title="Set Active"
                            onclick="return confirm(\'Set this academic year active?\')">
                                <i class="fas fa-check"></i>
                            </a>' : '';
                    $lockToggle = $row->is_active ? '' : ($row->is_locked
                        ? '<a href="' . route('academic.year.unlock', $row->id) . '"
                            class="btn-action btn-edit-soft"
                            title="Unlock"
                            onclick="return confirm(\'Unlock this academic year?\')">
                                <i class="fas fa-unlock"></i>
                           </a>'
                        : '<a href="' . route('academic.year.lock', $row->id) . '"
                            class="btn-action btn-delete-soft"
                            title="Lock"
                            onclick="return confirm(\'Lock this academic year?\')">
                                <i class="fas fa-lock"></i>
                           </a>');

                    $editButton = $row->is_locked ? '' : '<a href="' . route('academic.year.edit', $row->id) . '"
                            class="btn-action btn-edit-soft"
                            title="Edit">
                                <i class="fas fa-pen"></i>
                            </a>';

                    $deleteButton = ($row->is_locked || $row->is_active) ? '' : '<form action="' . route('academic.year.destroy', $row->id) . '"
                                method="POST"
                                class="d-inline"
                                onsubmit="return confirm(\'Delete this academic year?\')">
                                ' . csrf_field() . method_field('DELETE') . '
                                <button type="submit"
                                        class="btn-action btn-delete-soft"
                                        title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>';

                    return '<div class="d-flex justify-content-end">
                            ' . $setInactive . '
                            ' . $setActive . '
                            ' . $lockToggle . '
                            ' . $editButton . '
                            ' . $deleteButton . '
                        </div>';
                })
                ->rawColumns(['action', 'status', 'DT_RowIndex'])
                ->make(true);
        }
        return view('academic-year.index');
    }

    public function create()
    {
        return view('academic-year.create');
    }

    public function store(Request $request) {
        $request->validate([
            'name' => 'required|string|max:255|unique:academic_years,name|regex:/^\d{4}-\d{4}$/',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        AcademicYear::create($request->only(['name', 'start_date', 'end_date']));
        return redirect()->route('academic.year.index')->with('success','Academic Year Added');
    }

    public function setActive($id) {
        $year = AcademicYear::findOrFail($id);
        if ($year->is_locked) {
            return back()->with('error', 'Locked academic year cannot be set active.');
        }
        DB::transaction(function () use ($year, $id) {
            AcademicYear::where('id', '!=', $id)->update(['is_active' => 0]);
            $year->update(['is_active' => 1, 'is_locked' => 0]);
        });
        session([
            'selected_academic_year_id' => $year->id,
            'selected_class_id' => null,
            'selected_section_id' => null,
        ]);
        return back()->with('success', 'Academic year set active. Other years are now inactive.');
    }

    public function setInactive($id)
    {
        $year = AcademicYear::findOrFail($id);
        if (!$year->is_active) {
            return back()->with('error', 'Academic year is already inactive.');
        }

        $year->update(['is_active' => 0]);
        session()->forget(['selected_academic_year_id', 'selected_class_id', 'selected_section_id']);

        return back()->with('success', 'Academic year set inactive.');
    }

    public function edit($id)
    {
        $year = AcademicYear::findOrFail($id);
        return view('academic-year.edit', compact('year'));
    }

    public function update(Request $request, $id)
    {
        $year = AcademicYear::findOrFail($id);
        if ($year->is_locked) {
            return back()->with('error', 'Locked academic year cannot be edited.');
        }

        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                'regex:/^\d{4}-\d{4}$/',
                Rule::unique('academic_years', 'name')->ignore($year->id),
            ],
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $year->update($request->only(['name', 'start_date', 'end_date']));
        return redirect()->route('academic.year.index')->with('success','Academic Year Updated');
    }

    public function destroy($id)
    {
        $year = AcademicYear::findOrFail($id);
        if ($year->is_locked) {
            return back()->with('error', 'Locked academic year cannot be deleted.');
        }
        if ($year->is_active) {
            return back()->with('error', 'Active academic year cannot be deleted.');
        }
        if ($this->hasLinkedRecords((int) $year->id)) {
            return back()->with('error', 'Cannot delete this academic year because related records exist.');
        }
        $year->delete();
        return redirect()->route('academic.year.index')->with('success','Academic Year Deleted');
    }

    public function lock($id)
    {
        $year = AcademicYear::findOrFail($id);
        if ($year->is_active) {
            return back()->with('error', 'Active academic year cannot be locked.');
        }
        $year->update(['is_locked' => 1]);
        return back()->with('success', 'Academic year locked.');
    }

    public function unlock($id)
    {
        $year = AcademicYear::findOrFail($id);
        $year->update(['is_locked' => 0]);
        return back()->with('success', 'Academic year unlocked.');
    }

}

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
use App\Models\Room;

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

    private function canManageMapping(): bool
    {
        return $this->canPermission('class_manage') || $this->canPermission('room_manage');
    }

    private function formData(): array
    {
        return [
            'teachers' => Teacher::orderBy('name')->get(),
            'classes' => Classes::with(['academicYear', 'sections'])->orderBy('name')->get(),
            'subjects' => Subject::orderBy('name')->get(),
            'rooms' => Room::where('status', 1)->orderBy('name')->get(),
        ];
    }

    private function upsertMapping(Request $request, ?TeacherMapping $mapping = null): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'teacher_id' => 'required|exists:teachers,id',
            'section_id' => 'required|exists:sections,id',
            'subject_id' => 'required|exists:subjects,id',
            'room_id' => 'required|exists:rooms,id',
        ]);

        $section = Section::with('class')->findOrFail((int) $validated['section_id']);
        $academicYearId = (int) ($section->class->academic_year_id ?? 0);
        $subject = Subject::findOrFail((int) $validated['subject_id']);
        if ((int) $subject->class_id !== (int) $section->class_id) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['subject_id' => 'Selected subject does not belong to selected class/section.']);
        }

        $duplicateQuery = TeacherMapping::where('teacher_id', $validated['teacher_id'])
            ->where('section_id', $validated['section_id'])
            ->where('subject_id', $validated['subject_id']);
        if ($mapping) {
            $duplicateQuery->where('id', '!=', $mapping->id);
        }
        if ($duplicateQuery->exists()) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['teacher_id' => 'This teacher-section-subject mapping already exists.']);
        }

        $sectionRoomQuery = TeacherMapping::where('section_id', $validated['section_id'])
            ->whereNotNull('room_id');
        if ($mapping) {
            $sectionRoomQuery->where('id', '!=', $mapping->id);
        }
        $sectionRoomId = $sectionRoomQuery->value('room_id');
        if ($sectionRoomId && (int) $sectionRoomId !== (int) $validated['room_id']) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['room_id' => 'This section already has a different room assigned. Please use same room for this section.']);
        }

        $roomAlreadyAssigned = TeacherMapping::where('room_id', $validated['room_id'])
            ->where('section_id', '!=', $validated['section_id'])
            ->whereHas('section.class', function ($q) use ($academicYearId) {
                $q->where('academic_year_id', $academicYearId);
            });
        if ($mapping) {
            $roomAlreadyAssigned->where('id', '!=', $mapping->id);
        }
        if ($roomAlreadyAssigned->exists()) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['room_id' => 'Same room is already assigned to another section in this academic year.']);
        }

        TeacherMapping::where('section_id', $validated['section_id'])
            ->when($mapping, fn($q) => $q->where('id', '!=', $mapping->id))
            ->update(['room_id' => $validated['room_id']]);

        if ($mapping) {
            $mapping->update($validated);
            return redirect()->route('teacher.mapping')->with('success', 'Teacher mapping updated successfully.');
        }

        TeacherMapping::create($validated);
        return redirect()->route('teacher.mapping')->with('success', 'Teacher mapping saved successfully.');
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = TeacherMapping::with(['teacher', 'section.class.academicYear', 'subject', 'room'])
                ->whereHas('section.class', function($q) {
                    $q->when(session('selected_academic_year_id'), function($sq) {
                        $sq->where('academic_year_id', session('selected_academic_year_id'));
                    });
                })
                ->latest();

            if ($request->filled('class_id')) {
                $query->whereHas('section', function ($q) use ($request) {
                    $q->where('class_id', (int) $request->class_id);
                });
            }
            if ($request->filled('section_id')) {
                $query->where('section_id', (int) $request->section_id);
            }

            return datatables()->of($query)
                ->addIndexColumn()
                ->addColumn('teacher_name', function($row){
                    return $row->teacher ? '<span class="fw-bold text-dark">'.$row->teacher->name.'</span>' : '<span class="text-muted small">N/A</span>';
                })
                ->addColumn('mapping_info', function($row){
                    $year = $row->section && $row->section->class && $row->section->class->academicYear
                        ? $row->section->class->academicYear->name
                        : 'N/A';
                    $class = $row->section && $row->section->class ? $row->section->class->name : 'N/A';
                    $section = $row->section ? $row->section->name : 'N/A';
                    return '<span class="fw-semibold text-muted">'.$year.'</span> <span class="mx-1 text-muted">•</span> <span class="fw-semibold text-primary">'.$class.'</span> <span class="mx-1 text-muted">•</span> <span class="fw-semibold text-dark">'.$section.'</span>';
                })
                ->addColumn('subject_name', function($row){
                    return $row->subject ? '<span class="fw-semibold">'.$row->subject->name.'</span>' : '<span class="text-muted small">N/A</span>';
                })
                ->addColumn('room_name', function($row){
                    return $row->room ? '<span class="fw-semibold text-info">'.e($row->room->name).'</span>' : '<span class="text-muted small">N/A</span>';
                })
                ->addColumn('action', function($row){
                    if (!$this->canManageMapping()) {
                        return '<div class="d-flex justify-content-end"></div>';
                    }
                    return '<div class="d-flex justify-content-end gap-1">
                        <a href="'.route('teacher.mapping.edit', $row->id).'" class="btn-action btn-view-soft" title="Edit">
                            <i class="fas fa-pen"></i>
                        </a>
                        <form action="'.route('teacher.mapping.destroy', $row->id).'" method="POST" class="d-inline" onsubmit="return confirm(\'Remove this mapping?\')">
                            ' . csrf_field() . method_field('DELETE') . '
                            <button type="submit" class="btn-action btn-delete-soft" title="Delete"><i class="fas fa-trash"></i></button>
                        </form>
                    </div>';
                })
                ->rawColumns(['action', 'DT_RowIndex', 'teacher_name', 'mapping_info', 'subject_name', 'room_name'])
                ->make(true);
        }
        return view('teacher-mapping.index', $this->formData());
    }

    public function create()
    {
        if (!$this->canManageMapping()) {
            abort(403, 'Unauthorized access');
        }
        return view('teacher-mapping.create', $this->formData());
    }

    public function edit($id)
    {
        if (!$this->canManageMapping()) {
            abort(403, 'Unauthorized access');
        }
        $mapping = TeacherMapping::findOrFail($id);
        return view('teacher-mapping.edit', array_merge($this->formData(), ['mapping' => $mapping]));
    }

    public function store(Request $request)
    {
        if (!$this->canManageMapping()) {
            abort(403, 'Unauthorized access');
        }
        return $this->upsertMapping($request);
    }

    public function update(Request $request, $id)
    {
        if (!$this->canManageMapping()) {
            abort(403, 'Unauthorized access');
        }
        $mapping = TeacherMapping::findOrFail($id);
        return $this->upsertMapping($request, $mapping);
    }

    public function destroy($id)
    {
        if (!$this->canManageMapping()) {
            abort(403, 'Unauthorized access');
        }
        TeacherMapping::findOrFail($id)->delete();
        return redirect()->route('teacher.mapping')->with('success', 'Mapping deleted.');
    }
}

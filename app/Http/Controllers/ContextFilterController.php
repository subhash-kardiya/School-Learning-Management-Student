<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Classes;
use App\Models\Section;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherMapping;
use Illuminate\Http\Request;

class ContextFilterController extends Controller
{
    public function set(Request $request)
    {
        $request->validate([
            'academic_year_id' => 'nullable|integer',
            'class_id' => 'nullable|integer',
            'section_id' => 'nullable|integer',
        ]);

        $activeAcademicYearId = (int) (AcademicYear::where('is_active', 1)->value('id') ?? 0);
        $academicYearId = $activeAcademicYearId ?: null;
        $classId = $request->filled('class_id') ? (int) $request->input('class_id') : null;
        $sectionId = $request->filled('section_id') ? (int) $request->input('section_id') : null;

        if ($academicYearId && $classId) {
            $validClass = Classes::whereKey($classId)->where('academic_year_id', $academicYearId)->exists();
            if (!$validClass) {
                $classId = null;
                $sectionId = null;
            }
        }

        if ($classId && $sectionId) {
            $validSection = Section::whereKey($sectionId)->where('class_id', $classId)->exists();
            if (!$validSection) {
                $sectionId = null;
            }
        }

        session([
            'selected_academic_year_id' => $academicYearId,
            'selected_class_id' => $classId,
            'selected_section_id' => $sectionId,
        ]);

        return back();
    }

    public function clear()
    {
        session()->forget(['selected_academic_year_id', 'selected_class_id', 'selected_section_id']);
        return back();
    }

    public function sectionsByClass($classId)
    {
        return response()->json(
            Section::where('class_id', $classId)->where('status', 1)->orderBy('name')->get(['id', 'name'])
        );
    }

    public function classesByYear($yearId)
    {
        $activeAcademicYearId = (int) (AcademicYear::where('is_active', 1)->value('id') ?? 0);
        if ($activeAcademicYearId > 0 && (int) $yearId !== $activeAcademicYearId) {
            return response()->json([]);
        }

        return response()->json(
            Classes::where('academic_year_id', $yearId)->where('status', 1)->orderBy('name')->get(['id', 'name'])
        );
    }

    public function teacherBySubject(Request $request, $subjectId)
    {
        $subject = Subject::find($subjectId);
        if (!$subject) {
            return response()->json(['id' => null, 'name' => null, 'source' => 'none'], 404);
        }

        $classId = $request->filled('class_id') ? (int) $request->query('class_id') : (int) $subject->class_id;
        $sectionId = $request->filled('section_id') ? (int) $request->query('section_id') : null;

        $mappingQuery = TeacherMapping::query()
            ->with('teacher')
            ->where('subject_id', $subject->id);

        if ($sectionId) {
            $mappingQuery->where('section_id', $sectionId);
        } elseif ($classId) {
            $mappingQuery->whereHas('section', fn($q) => $q->where('class_id', $classId));
        }

        $mapping = $mappingQuery->first();
        if ($mapping && $mapping->teacher) {
            return response()->json([
                'id' => $mapping->teacher->id,
                'name' => $mapping->teacher->name,
                'source' => 'teacher_mapping',
            ]);
        }

        if ($subject->teacher_id) {
            $teacher = Teacher::find($subject->teacher_id);
            if ($teacher) {
                return response()->json([
                    'id' => $teacher->id,
                    'name' => $teacher->name,
                    'source' => 'subject_teacher',
                ]);
            }
        }

        return response()->json(['id' => null, 'name' => null, 'source' => 'none']);
    }
}

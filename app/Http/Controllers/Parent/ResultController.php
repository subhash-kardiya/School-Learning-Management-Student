<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Models\ExamMark;
use App\Models\ParentModel;
use Illuminate\Http\Request;

class ResultController extends Controller
{
    public function index(Request $request)
    {
        $parentId = (int) session('auth_id');
        if (session('role') !== 'parent' || $parentId <= 0) {
            abort(403, 'Unauthorized access.');
        }

        $parent = ParentModel::find($parentId);
        if (!$parent) {
            abort(403, 'Unauthorized access.');
        }

        $children = $parent->students()
            ->with(['class', 'section'])
            ->orderBy('student_name')
            ->get();

        $selectedStudentId = $request->filled('student_id') ? (int) $request->input('student_id') : null;
        $selectedStudent = null;
        $marks = collect();
        $selectedSessionId = $request->filled('session_id') ? (int) $request->session_id : null;

        $sessionOptions = ExamMark::with('exam.academicYear:id,name')
            ->whereIn('student_id', $children->pluck('id'))
            ->whereHas('exam', function ($query) {
                $query->where('result_declared', 1);
            })
            ->get()
            ->map(fn($m) => $m->exam?->academicYear)
            ->filter()
            ->unique('id')
            ->sortBy('name')
            ->values();

        if ($selectedSessionId && !$sessionOptions->contains('id', $selectedSessionId)) {
            $selectedSessionId = null;
        }

        if ($selectedStudentId) {
            $selectedStudent = $children->firstWhere('id', $selectedStudentId);
        }

        $canShowParentResult = !empty($selectedStudent) && !empty($selectedSessionId);
        if ($canShowParentResult) {
            $marks = ExamMark::with(['exam', 'subject'])
                ->where('student_id', $selectedStudent->id)
                ->where('academic_year_id', $selectedSessionId)
                ->whereHas('exam', function ($query) {
                    $query->where('result_declared', 1);
                })
                ->latest('updated_at')
                ->get();
        }

        $layout = $request->boolean('print') ? 'layouts.print' : 'layouts.admin';

        return view('results.parent', compact(
            'children',
            'selectedStudent',
            'marks',
            'sessionOptions',
            'selectedSessionId',
            'canShowParentResult',
            'layout'
        ));
    }
}

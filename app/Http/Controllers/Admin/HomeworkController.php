<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Homework;
use App\Models\HomeworkSubmission;
use App\Models\Classes;
use App\Models\Section;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\Student;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeworkController extends Controller
{
    private function canPermission(string $permission): bool
    {
        $user = Auth::user();
        if (!$user || !method_exists($user, 'hasPermission')) {
            return false;
        }
        return $user->hasPermission($permission);
    }

    public function create()
    {
        if (!$this->canPermission('homework_create')) {
            abort(403, 'Unauthorized access');
        }

        $classes = Classes::with('sections')->get();
        $sections = Section::all();
        $subjects = Subject::orderBy('name')->get();
        $teachers = Teacher::orderBy('name')->get();
        $academicYears = AcademicYear::orderBy('name')->get();

        return view('homework.create', compact('classes', 'sections', 'subjects', 'teachers', 'academicYears'));
    }

    public function store(Request $request)
    {
        if (!$this->canPermission('homework_create')) {
            abort(403, 'Unauthorized access');
        }

        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'section_id' => 'required|exists:sections,id',
            'subject_id' => 'required|exists:subjects,id',
            'teacher_id' => 'required|exists:teachers,id',
            'academic_year_id' => 'nullable|exists:academic_years,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'required|date',
            'attachment' => 'nullable|file|mimes:pdf,doc,docx,png,jpg,jpeg,zip|max:5120',
            'status' => 'required|boolean',
        ]);

        $data = $request->all();

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $filename = uniqid('hw_') . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/homework'), $filename);
            $data['attachment'] = $filename;
        }

        Homework::create($data);

        return redirect()->route('homework.list')->with('success', 'Homework created successfully.');
    }

    public function list()
    {
        if (!$this->canPermission('homework_list')) {
            abort(403, 'Unauthorized access');
        }

        $user = Auth::user();
        $query = Homework::with(['class', 'section', 'subject', 'teacher'])->latest();

        if ($user && method_exists($user, 'hasRole') && $user->hasRole('teacher')) {
            $query->where('teacher_id', $user->id);
        }

        $homeworks = $query->get();

        return view('homework.list', compact('homeworks'));
    }

    public function submissions()
    {
        if (!$this->canPermission('homework_submission')) {
            abort(403, 'Unauthorized access');
        }

        $user = Auth::user();
        $query = HomeworkSubmission::with(['homework.class', 'homework.section', 'homework.subject', 'student'])->latest();

        if ($user && method_exists($user, 'hasRole') && $user->hasRole('teacher')) {
            $query->whereHas('homework', function ($q) use ($user) {
                $q->where('teacher_id', $user->id);
            });
        }

        $submissions = $query->get();
        return view('homework.submission', compact('submissions'));
    }

    public function feedback(Request $request, $id)
    {
        if (!$this->canPermission('homework_submission')) {
            abort(403, 'Unauthorized access');
        }

        $request->validate([
            'feedback' => 'nullable|string',
            'status' => 'required|string',
        ]);

        $submission = HomeworkSubmission::findOrFail($id);
        $submission->update([
            'feedback' => $request->feedback,
            'status' => $request->status,
        ]);

        return back()->with('success', 'Feedback updated.');
    }

    // Student
    public function studentList()
    {
        $user = Auth::user();
        if (!$user || !method_exists($user, 'hasRole') || !$user->hasRole('student')) {
            abort(403, 'Unauthorized access');
        }

        $student = Student::findOrFail($user->id);

        $homeworks = Homework::with(['subject', 'teacher'])
            ->where('class_id', $student->class_id)
            ->where('section_id', $student->section_id)
            ->latest()
            ->get();

        $submissions = HomeworkSubmission::where('student_id', $student->id)
            ->get()
            ->keyBy('homework_id');

        return view('homework.list', compact('homeworks', 'submissions', 'student'));
    }

    public function submit(Request $request, $homeworkId)
    {
        $user = Auth::user();
        if (!$user || !method_exists($user, 'hasRole') || !$user->hasRole('student')) {
            abort(403, 'Unauthorized access');
        }

        $request->validate([
            'attachment' => 'required|file|mimes:pdf,doc,docx,png,jpg,jpeg,zip|max:5120',
        ]);

        $student = Student::findOrFail($user->id);
        $homework = Homework::findOrFail($homeworkId);

        $file = $request->file('attachment');
        $filename = uniqid('submission_') . '.' . $file->getClientOriginalExtension();
        $file->move(public_path('uploads/homework-submissions'), $filename);

        HomeworkSubmission::updateOrCreate(
            [
                'homework_id' => $homework->id,
                'student_id' => $student->id,
            ],
            [
                'submitted_at' => now(),
                'attachment' => $filename,
                'status' => 'Submitted',
            ]
        );

        return back()->with('success', 'Homework submitted successfully.');
    }
}

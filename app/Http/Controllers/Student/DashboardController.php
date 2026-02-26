<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Homework;
use App\Models\HomeworkSubmission;
use App\Models\Student;
use App\Models\Announcement;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $pendingHomework = 0;
        $user = Auth::user();
        if ($user && method_exists($user, 'hasRole') && $user->hasRole('student')) {
            $student = Student::find($user->id);
            if ($student) {
                $yearId = session('selected_academic_year_id');
                $allHomeworkIds = Homework::where('class_id', $student->class_id)
                    ->where('section_id', $student->section_id)
                    ->when($yearId, fn($q) => $q->where('academic_year_id', $yearId))
                    ->pluck('id');
                $submittedIds = HomeworkSubmission::where('student_id', $student->id)
                    ->whereIn('homework_id', $allHomeworkIds)
                    ->pluck('homework_id');
                $pendingHomework = $allHomeworkIds->diff($submittedIds)->count();
            }
        }

        $latestAnnouncements = Announcement::query()
            ->activeWindow()
            ->visibleTo((string) session('role'), $user)
            ->latest()
            ->limit(6)
            ->get();

        return view('dashboard.student', compact('pendingHomework', 'latestAnnouncements'));
    }
}

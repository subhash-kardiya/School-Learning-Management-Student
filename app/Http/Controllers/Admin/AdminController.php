<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Classes;


class AdminController extends Controller
{
    public function dashboard()
    {
        if (session('role') !== 'admin') {
            abort(403, 'Unauthorized access');
        }
        $studentCount = Student::count();
        $teacherCount = Teacher::count();
        $classCount = Classes::count();

        return view('dashboard.admin', compact('studentCount', 'teacherCount', 'classCount'));

    }
    public function logout()
    {
        Auth::logout();               // actual logout
        session()->invalidate();       // session clear
        session()->regenerateToken();  // CSRF token regenerate
        return redirect()->route('auth.login'); // login page redirect
    }
}

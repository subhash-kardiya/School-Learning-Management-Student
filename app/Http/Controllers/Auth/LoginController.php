<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\OtpMail;

class LoginController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    public function showOtp()
    {
        return view('auth.otp');
    }




    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        return redirect()->route('otp.page')
            ->with('success', 'OTP has been sent to your email!');
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|digits:6',
        ]);


        return redirect()->route('change.password')->with('success', 'OTP Verified! Now set new password.');

    }
    public function showChangePasswordForm()
    {

        return view('auth.change-password');
    }
    public function changePassword(Request $request)
    {

        return redirect()->route('auth.login')->with('success', 'Password changed successfully ✅');


        return back()->with('error', 'User not found!');
    }




    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required',
            'password' => 'required'
        ]);

        $login = $request->email; // This can be email / username / mobile

        // ADMIN
        $admin = Admin::where('email', $login)
            ->orWhere('username', $login)
            ->orWhere('mobile_no', $login)
            ->first();

        if ($admin && Hash::check($request->password, $admin->password)) {
            session([
                'auth_id' => $admin->id,
                'role' => 'admin'
            ]);
            return redirect()->route('admin.dashboard');
        }

        // TEACHER
        $teacher = \App\Models\Teacher::where('email', $login)
            ->orWhere('username', $login)
            ->orWhere('mobile_no', $login)
            ->first();

        if ($teacher && Hash::check($request->password, $teacher->password)) {
            session([
                'auth_id' => $teacher->id,
                'role' => 'teacher'
            ]);
            return redirect()->route('teacher.dashboard');
        }

        // STUDENT
        $student = \App\Models\Student::where('email', $login)
            ->orWhere('username', $login)
            ->orWhere('mobile_no', $login)
            ->first();

        if ($student && Hash::check($request->password, $student->password)) {
            session([
                'auth_id' => $student->id,
                'role' => 'student'
            ]);
            return redirect()->route('student.dashboard');
        }

        // PARENT
        $parent = \App\Models\ParentModel::where('email', $login)
            ->orWhere('username', $login)
            ->orWhere('mobile_no', $login)
            ->first();

        if ($parent && Hash::check($request->password, $parent->password)) {
            session([
                'auth_id' => $parent->id,
                'role' => 'parent'
            ]);
            return redirect()->route('parent.dashboard');
        }

        return back()->withErrors(['email' => 'Invalid login details']);
    }

    public function logout()
    {
        session()->invalidate();
        session()->regenerateToken();
        return redirect()->route('auth.login');
    }
}

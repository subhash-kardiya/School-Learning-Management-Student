<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Admin;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\OtpMail;
use Throwable;

class LoginController extends Controller
{
    private const OTP_EXPIRY_MINUTES = 5;

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
        if (!session('password_reset_email')) {
            return redirect()->route('forgot.password')->with('error', 'Please request OTP first.');
        }

        return view('auth.otp');
    }




    public function forgotPassword(Request $request)
{
    $request->validate([
        'email' => 'required|email'
    ]);

    $email = strtolower(trim($request->email));
    $userInfo = $this->findUserByEmail($email);

    if (!$userInfo) {
        return back()->with('success', 'If the email exists, an OTP has been sent.');
    }

    $otp = (string) random_int(100000, 999999);

    DB::table('password_reset_tokens')->updateOrInsert(
        ['email' => $email],
        [
            'token' => Hash::make($otp),
            'expires_at' => now()->addMinutes(self::OTP_EXPIRY_MINUTES),
            'attempts' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]
    );

    try {
        Mail::to($email)->send(new \App\Mail\OtpMail($otp));
    } catch (\Throwable $e) {
        return back()->with('error', 'Unable to send OTP. Try again.');
    }

    session([
        'password_reset_email' => $email,
        'password_reset_verified' => false,
    ]);

    return redirect()->route('otp.page')
        ->with('success', 'OTP sent to your email.');
}


    public function verifyOtp(Request $request)
{
    $request->validate([
        'otp' => 'required|digits:6',
    ]);

    $email = session('password_reset_email');

    if (!$email) {
        return redirect()->route('forgot.password');
    }

    $record = DB::table('password_reset_tokens')->where('email', $email)->first();

    if (!$record) {
        return back()->with('error', 'Invalid or Expired OTP.');
    }

    if (now()->greaterThan($record->expires_at)) {
        DB::table('password_reset_tokens')->where('email', $email)->delete();
        return back()->with('error', 'OTP expired. Request new one.');
    }

    if ($record->attempts >= 3) {
        DB::table('password_reset_tokens')->where('email', $email)->delete();
        return back()->with('error', 'Too many attempts. Request new OTP.');
    }

    if (!Hash::check($request->otp, $record->token)) {
        DB::table('password_reset_tokens')
            ->where('email', $email)
            ->increment('attempts');

        return back()->with('error', 'Invalid OTP.');
    }

    session(['password_reset_verified' => true]);

    return redirect()->route('change.password');
}


public function showChangePasswordForm()
{
    if (!session('password_reset_verified')) {
        return redirect()->route('otp.page');
    }

    return view('auth.change-password');
}
    

    public function changePassword(Request $request)
{
    $request->validate([
        'password' => 'required|min:8|confirmed',
    ]);

    $email = session('password_reset_email');

    if (!$email || !session('password_reset_verified')) {
        return redirect()->route('forgot.password');
    }

    $userInfo = $this->findUserByEmail($email);
    $user = $userInfo['user'] ?? null;

    if (!$user) {
        return redirect()->route('forgot.password');
    }

    $user->password = Hash::make($request->password);
    $user->save();

    DB::table('password_reset_tokens')->where('email', $email)->delete();

    session()->forget(['password_reset_email', 'password_reset_verified']);

    return redirect()->route('auth.login')
        ->with('success', 'Password reset successfully.');
}


    private function forgotPasswordModelMap(): array
    {
        return [
            'admin' => \App\Models\Admin::class,
            'teacher' => \App\Models\Teacher::class,
            'student' => \App\Models\Student::class,
            'parent' => \App\Models\ParentModel::class,
        ];
    }

    private function findUserByEmail(string $email): ?array
    {
        // Explicitly allow forgot-password for Super Admin (role_id=1) and Admin (role_id=2).
        $admin = \App\Models\Admin::where('email', $email)
            ->whereIn('role_id', [1, 2])
            ->first();
        if ($admin) {
            return ['type' => 'admin', 'user' => $admin];
        }

        foreach ($this->forgotPasswordModelMap() as $type => $modelClass) {
            if ($type === 'admin') {
                continue;
            }
            $user = $modelClass::where('email', $email)->first();
            if ($user) {
                return ['type' => $type, 'user' => $user];
            }
        }

        return null;
    }
    public function login(Request $request)
    {
        $request->validate([
            'login' => 'required|string|min:3|max:191',
            'password' => 'required|string|min:4|max:191'
        ], [
            'login.required' => 'Please enter username, mobile number, or email.',
            'login.min' => 'Login field must be at least 3 characters.',
            'password.required' => 'Please enter password.',
            'password.min' => 'Password must be at least 4 characters.',
        ]);

        $login = trim((string) $request->input('login', $request->input('email', '')));

        // ADMIN
        $admin = Admin::where('email', $login)
            ->orWhere('username', $login)
            ->orWhere('mobile_no', $login)
            ->first();

        if ($admin && Hash::check($request->password, $admin->password)) {
            $adminSessionRole = ((int) $admin->role_id === 1) ? 'superadmin' : 'admin';
            session([
                'auth_id' => $admin->id,
                'role' => $adminSessionRole
            ]);
            return redirect()->route('admin.dashboard')->with('success', 'Login successful.');
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
            return redirect()->route('teacher.dashboard')->with('success', 'Login successful.');
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
            return redirect()->route('student.dashboard')->with('success', 'Login successful.');
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
            return redirect()->route('parent.dashboard')->with('success', 'Login successful.');
        }

        return back()->withErrors(['login' => 'Invalid username/mobile/email or password.'])->withInput();
    }

    public function logout()
    {
        session()->invalidate();
        session()->regenerateToken();
        return redirect()->route('auth.login')->with('success', 'Logged out successfully.');
    }
}

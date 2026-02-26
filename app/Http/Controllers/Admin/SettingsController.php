<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class SettingsController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $sessionRole = strtolower((string) session('role'));
        $sessionIsAdmin = in_array($sessionRole, ['admin', 'superadmin', 'super admin', 'super_admin'], true);
        $roleByMethod = $user && method_exists($user, 'hasRole') && (
            $user->hasRole('admin') ||
            $user->hasRole('Admin') ||
            $user->hasRole('superadmin') ||
            $user->hasRole('super admin') ||
            $user->hasRole('super_admin') ||
            $user->hasRole('Super Admin') ||
            $user->hasRole('SuperAdmin')
        );

        if (!$user || (!$sessionIsAdmin && !$roleByMethod)) {
            abort(403, 'Unauthorized access');
        }

        return view('settings.index');
    }
}

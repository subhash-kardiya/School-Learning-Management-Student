<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Admin;
use App\Models\Teacher;
use App\Models\Student;
use App\Models\ParentModel;

class ShareAuthenticatedUser
{
    public function handle(Request $request, Closure $next)
    {
        if (session()->has('auth_id') && session()->has('role')) {
            $id = session('auth_id');
            $role = session('role');
            
            $user = null;
            switch ($role) {
                case 'admin':
                case 'superadmin':
                    $user = Admin::find($id);
                    break;
                case 'teacher':
                    $user = Teacher::find($id);
                    break;
                case 'student':
                    $user = Student::find($id);
                    break;
                case 'parent':
                    $user = ParentModel::find($id);
                    break;
            }
            
            if ($user) {
                Auth::setUser($user);
                // Also share with all views
                view()->share('authUser', $user);
            }
        }

        return $next($request);
    }
}

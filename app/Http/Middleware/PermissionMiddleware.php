<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Admin;
use App\Models\Teacher;
use App\Models\Student;
use App\Models\ParentModel;

class PermissionMiddleware
{
    public function handle(Request $request, Closure $next, $permission)
    {
        $id = session('auth_id');
        $rawRole = session('role');

        $normalize = function ($value) {
            $value = strtolower(trim((string) $value));
            return preg_replace('/[ _]+/', '', $value);
        };
        $role = $normalize($rawRole);

        if (!$id || !$role) {
            return redirect()->route('auth.login');
        }

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

        $permissions = array_values(array_filter(array_map('trim', explode(',', (string) $permission))));
        $authorized = false;
        foreach ($permissions as $perm) {
            if ($user && $user->hasPermission($perm)) {
                $authorized = true;
                break;
            }
        }

        if (!$user || !$authorized) {
            if ($request->ajax()) {
                return response()->json(['message' => 'Unauthorized access'], 403);
            }
            abort(403, 'Unauthorized access - Missing required permission.');
        }

        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $role)
    {
        if (!session()->has('auth_id')) {
            return redirect()->route('auth.login');
        }

        $currentRole = (string) session('role');
        $normalize = function ($value) {
            $value = strtolower(trim((string) $value));
            return preg_replace('/[ _]+/', '', $value);
        };

        $allowed = array_filter(array_map($normalize, preg_split('/[|,]/', (string) $role)));
        $current = $normalize($currentRole);

        if (!in_array($current, $allowed, true)) {
            abort(403, 'Unauthorized access');
        }

        return $next($request);
    }
}

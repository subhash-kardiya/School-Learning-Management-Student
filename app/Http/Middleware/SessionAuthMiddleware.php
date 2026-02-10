<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SessionAuthMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!session()->has('auth_id') || !session()->has('role')) {
            return redirect()->route('auth.login');
        }

        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AbsoluteSessionTimeout
{
    /**
     * Maximum session lifetime in minutes (default: 8 hours).
     * Prevents indefinitely long sessions even with activity.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $loginAt = $request->session()->get('login_at');
            $maxLifetime = config('session.absolute_timeout', 480); // 8 hours default

            if ($loginAt && now()->diffInMinutes($loginAt) >= $maxLifetime) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect('/login')->with('error', 'Your session has expired. Please log in again.');
            }
        }

        return $next($request);
    }
}

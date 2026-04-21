<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class IdleSessionTimeout
{
    /**
     * Idle timeout in minutes (default: 30 minutes).
     * Logs the user out if they haven't made a request within this window.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $lastActivity = $request->session()->get('last_activity_at');
            $idleTimeout = config('session.idle_timeout', 30); // 30 minutes default

            if ($lastActivity && now()->diffInMinutes($lastActivity) >= $idleTimeout) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'error' => true,
                        'message' => 'Your session has expired due to inactivity. Please log in again.',
                        'status' => 419,
                    ], 419);
                }

                return redirect('/login')->with('error', 'Your session has expired due to inactivity. Please log in again.');
            }

            // Update last activity timestamp on every request
            $request->session()->put('last_activity_at', now());
        }

        return $next($request);
    }
}

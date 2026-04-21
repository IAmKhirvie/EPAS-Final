<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!auth()->check()) {
            // Redirect to the correct login page based on the route
            if ($request->is('admin/*')) {
                return redirect()->route('admin.login');
            }
            if ($request->is('instructor/*')) {
                return redirect()->route('instructor.login');
            }
            return redirect()->route('login');
        }

        $user = auth()->user();

        // Use Roles constants for comparison when possible
        $normalizedRoles = array_map(function ($role) {
            return strtolower(trim($role));
        }, $roles);

        $userRole = strtolower($user->role);

        if (!in_array($userRole, $normalizedRoles)) {
            // Log unauthorized access attempt for security monitoring
            Log::warning('Unauthorized access attempt', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'user_role' => $user->role,
                'required_roles' => $roles,
                'route' => $request->route()?->getName(),
                'url' => $request->fullUrl(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            abort(403, 'Unauthorized access.');
        }

        return $next($request);
    }
}

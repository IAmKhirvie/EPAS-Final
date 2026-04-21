<?php

// Suppress PHP 8.5 deprecation warnings
error_reporting(E_ALL & ~E_DEPRECATED);

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Log;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Trust proxies (set TRUSTED_PROXIES in .env)
        // Note: env() is correct here — config() is not available during middleware setup
        $proxies = env('TRUSTED_PROXIES', '');
        if ($proxies !== '' && $proxies !== null) {
            $middleware->trustProxies(at: $proxies === '*' ? '*' : explode(',', $proxies));
        }

        // Configure auth redirects
        $middleware->redirectGuestsTo('/login');
        $middleware->redirectUsersTo('/dashboard');

        // Register global middleware
        $middleware->web(append: [
            App\Http\Middleware\TrustProxies::class,
            App\Http\Middleware\SecurityHeaders::class,
            App\Http\Middleware\AbsoluteSessionTimeout::class,
            App\Http\Middleware\IdleSessionTimeout::class,
        ]);
        $middleware->alias([
            'check.role' => \App\Http\Middleware\CheckRole::class,
            'check.active' => \App\Http\Middleware\CheckUserActive::class,
            'rate.limit' => \App\Http\Middleware\RateLimitMiddleware::class,
            'two-factor' => \App\Http\Middleware\TwoFactorMiddleware::class,
            'audit.log' => \App\Http\Middleware\AuditLogMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // ── Session Expired (CSRF token mismatch) ──
        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, $request) {
            Log::warning('TokenMismatchException', [
                'url' => $request->fullUrl(),
                'ip' => $request->ip(),
            ]);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'error' => true,
                    'message' => 'Your session has expired. Please refresh the page and try again.',
                    'status' => 419,
                ], 419);
            }

            return redirect()->back()
                ->withInput($request->except('password', '_token'))
                ->with('error', 'Your session has expired. Please try again.');
        });

        // ── Model Not Found (invalid ID in URL) ──
        $exceptions->render(function (\Illuminate\Database\Eloquent\ModelNotFoundException $e, $request) {
            $model = class_basename($e->getModel());

            Log::warning("ModelNotFoundException: {$model}", [
                'ids' => $e->getIds(),
                'url' => $request->fullUrl(),
            ]);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'error' => true,
                    'message' => "The requested {$model} could not be found.",
                    'status' => 404,
                ], 404);
            }

            return redirect()->back()
                ->with('error', "The requested {$model} could not be found.");
        });

        // ── Authorization Exception ──
        $exceptions->render(function (\Illuminate\Auth\Access\AuthorizationException $e, $request) {
            Log::warning('AuthorizationException', [
                'user_id' => $request->user()?->id,
                'url' => $request->fullUrl(),
            ]);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'error' => true,
                    'message' => 'You do not have permission to perform this action.',
                    'status' => 403,
                ], 403);
            }

            return redirect()->back()
                ->with('error', 'You do not have permission to perform this action.');
        });

        // ── Validation Exception (handled gracefully for AJAX) ──
        $exceptions->render(function (\Illuminate\Validation\ValidationException $e, $request) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'error' => true,
                    'message' => 'Please check your input and try again.',
                    'errors' => $e->errors(),
                    'status' => 422,
                ], 422);
            }

            // Let Laravel handle non-AJAX validation exceptions normally (redirect back with errors)
            return null;
        });

        // ── Database Query Exception ──
        $exceptions->render(function (\Illuminate\Database\QueryException $e, $request) {
            Log::error('QueryException', [
                'message' => $e->getMessage(),
                'sql' => $e->getSql(),
                'bindings' => '[REDACTED]',
                'url' => $request->fullUrl(),
                'user_id' => $request->user()?->id,
            ]);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'error' => true,
                    'message' => 'A database error occurred. Please try again later.',
                    'status' => 500,
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'A database error occurred. Please try again later.')
                ->with('error_debug', null);
        });

        // ── HTTP Exceptions (403, 404, 419, 429, 500, 503) ──
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\HttpException $e, $request) {
            $statusCode = $e->getStatusCode();

            Log::warning("HttpException {$statusCode}", [
                'message' => $e->getMessage(),
                'url' => $request->fullUrl(),
                'user_id' => $request->user()?->id,
            ]);

            if ($request->expectsJson() || $request->ajax()) {
                $messages = [
                    403 => 'You do not have permission to perform this action.',
                    404 => 'The requested resource could not be found.',
                    419 => 'Your session has expired. Please refresh and try again.',
                    429 => 'Too many requests. Please slow down and try again.',
                    500 => 'Something went wrong on our end. Please try again later.',
                    503 => 'The system is currently under maintenance. Please try again shortly.',
                ];

                return response()->json([
                    'error' => true,
                    'message' => $messages[$statusCode] ?? 'An unexpected error occurred.',
                    'status' => $statusCode,
                ], $statusCode);
            }

            // CSRF token mismatch: redirect back with friendly message
            // (TokenMismatchException is converted to HttpException 419 by prepareException,
            //  so the TokenMismatchException render handler above never fires)
            if ($statusCode === 419) {
                return redirect()->back()
                    ->withInput($request->except('password', '_token'))
                    ->with('error', 'Your session has expired. Please refresh the page and try again.');
            }

            // For regular requests, let Laravel render the custom error pages in resources/views/errors/
            // Only redirect with flash for status codes that don't have dedicated pages
            $pagesWithViews = [403, 404, 429, 500, 503];
            if (in_array($statusCode, $pagesWithViews)) {
                return null; // Let Laravel render the error view
            }

            $message = 'An unexpected error occurred. Please try again.';
            return redirect()->back()
                ->with('error', $message)
                ->with('error_debug', null);
        });

        // ── Catch-All: Any unhandled exception ──
        $exceptions->render(function (\Throwable $e, $request) {
            // Let Laravel handle validation exceptions normally (redirect back with errors)
            if ($e instanceof \Illuminate\Validation\ValidationException) {
                return null;
            }

            // Let Laravel handle authentication exceptions (redirect to login page)
            if ($e instanceof \Illuminate\Auth\AuthenticationException) {
                return null;
            }

            $logContext = [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'file' => $e->getFile() . ':' . $e->getLine(),
                'url' => $request->fullUrl(),
                'user_id' => $request->user()?->id,
            ];
            if (config('app.debug')) {
                $logContext['trace'] = $e->getTraceAsString();
            }
            Log::error('Unhandled Exception', $logContext);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'error' => true,
                    'message' => 'An unexpected error occurred. Please try again later.',
                    'status' => 500,
                ], 500);
            }

            // For non-AJAX requests, redirect with generic error
            // Never expose raw error messages to users
            return redirect()->back()
                ->with('error', 'An unexpected error occurred. Please try again later.')
                ->with('error_debug', null);
        });
    })->create();

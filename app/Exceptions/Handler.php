<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\View\ViewException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Throwable;
use ParseError;
use ErrorException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class Handler extends ExceptionHandler
{
    protected $levels = [];
    protected $dontReport = [];
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Prevent infinite redirect loops.
     */
    protected function isInfiniteLoopDetected(Throwable $e, $request): bool
    {
        if (!$request->hasSession()) {
            return false;
        }

        $key = 'error_loop_' . md5($request->url());
        $count = Session::get($key, 0);

        if ($count > 2) {
            Session::forget($key);
            return true;
        }

        Session::put($key, $count + 1);
        Session::save();
        return false;
    }

    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $e)
    {
        // AJAX / JSON requests – return JSON error (logs to browser console)
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'error' => true,
                'message' => $e->getMessage(),
                'trace' => config('app.debug') ? $e->getTrace() : null,
            ], 500);
        }

        // Catch ALL errors that occur during view rendering / compilation
        if (
            $e instanceof ViewException ||
            $e instanceof RouteNotFoundException ||
            $e instanceof ParseError ||
            $e instanceof ErrorException
        ) {
            Log::error('View/Parse error: ' . $e->getMessage(), ['exception' => $e]);

            if ($this->isInfiniteLoopDetected($e, $request)) {
                return response("Critical error loop detected. Please contact support.", 500);
            }

            // Return your custom 500 error view
            return response()->view('errors.500', [
                'message' => 'The page could not be rendered due to a technical error.'
            ], 500);
        }

        return parent::render($request, $e);
    }
}

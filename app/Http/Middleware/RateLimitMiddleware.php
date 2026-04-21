<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\LoginAttempt;
use Symfony\Component\HttpFoundation\Response;

class RateLimitMiddleware
{
    protected int $maxAttempts = 5;
    protected int $decayMinutes = 15;

    public function handle(Request $request, Closure $next, int $maxAttempts = null, int $decayMinutes = null): Response
    {
        $this->maxAttempts = $maxAttempts ?? $this->maxAttempts;
        $this->decayMinutes = $decayMinutes ?? $this->decayMinutes;

        $ip = $request->ip();
        $failedAttempts = LoginAttempt::getRecentFailedCount($ip, $this->decayMinutes);

        if ($failedAttempts >= $this->maxAttempts) {
            $retryAfter = $this->getRetryAfter($ip);

            return response()->json([
                'message' => 'Too many attempts. Please try again later.',
                'retry_after' => $retryAfter,
            ], 429)->withHeaders([
                'Retry-After' => $retryAfter,
                'X-RateLimit-Limit' => $this->maxAttempts,
                'X-RateLimit-Remaining' => 0,
            ]);
        }

        $response = $next($request);

        return $response->withHeaders([
            'X-RateLimit-Limit' => $this->maxAttempts,
            'X-RateLimit-Remaining' => max(0, $this->maxAttempts - $failedAttempts - 1),
        ]);
    }

    protected function getRetryAfter(string $ip): int
    {
        $oldestAttempt = LoginAttempt::forIp($ip)
            ->failed()
            ->recent($this->decayMinutes)
            ->orderBy('attempted_at')
            ->first();

        if (!$oldestAttempt) {
            return $this->decayMinutes * 60;
        }

        $unlockTime = $oldestAttempt->attempted_at->addMinutes($this->decayMinutes);
        return max(0, now()->diffInSeconds($unlockTime, false));
    }
}

<?php

namespace App\Http\Traits;

use Illuminate\Support\Facades\Cache;

trait RateLimitsLogins
{
    /**
     * Get rate limit configuration based on consecutive failed attempts
     */
    protected function getRateLimitConfig($key)
    {
        $attempts = (int) Cache::get($key . '-attempts', 0);

        if ($attempts < 5) {
            return ['attempts' => $attempts, 'max' => 5, 'decayMinutes' => 1, 'message' => '1 minute'];
        } elseif ($attempts < 10) {
            return ['attempts' => $attempts, 'max' => 10, 'decayMinutes' => 3, 'message' => '3 minutes'];
        } elseif ($attempts < 15) {
            return ['attempts' => $attempts, 'max' => 15, 'decayMinutes' => 60, 'message' => '1 hour'];
        } else {
            return ['attempts' => $attempts, 'max' => 20, 'decayMinutes' => 1440, 'message' => '24 hours'];
        }
    }

    /**
     * Check if currently locked out
     */
    protected function isLockedOut($key)
    {
        $lockoutKey = $key . '-lockout';

        if (Cache::has($lockoutKey)) {
            $expiresAt = Cache::get($lockoutKey);
            if (now()->timestamp < $expiresAt) {
                $remaining = $expiresAt - now()->timestamp;
                return ['locked' => true, 'remaining' => $remaining];
            } else {
                // Lockout expired - reset everything
                Cache::forget($lockoutKey);
                Cache::forget($key . '-attempts');
            }
        }

        return ['locked' => false];
    }

    /**
     * Record failed attempt and apply lockout if needed
     */
    protected function recordFailedAttempt($key)
    {
        $attemptsKey = $key . '-attempts';
        $attempts = (int) Cache::get($attemptsKey, 0) + 1;
        Cache::put($attemptsKey, $attempts, now()->addDay());

        $config = $this->getRateLimitConfig($key);

        // Apply lockout if threshold reached
        if ($attempts >= $config['max']) {
            $lockoutKey = $key . '-lockout';
            $expiresAt = now()->timestamp + ($config['decayMinutes'] * 60);
            Cache::put($lockoutKey, $expiresAt, now()->addDay());
        }
    }

    /**
     * Clear all rate limit data
     */
    protected function clearRateLimits($key)
    {
        Cache::forget($key . '-attempts');
        Cache::forget($key . '-lockout');
    }

    /**
     * Format remaining time
     */
    protected function formatTime($seconds)
    {
        if ($seconds < 60) {
            return $seconds . ' seconds';
        } elseif ($seconds < 3600) {
            $m = ceil($seconds / 60);
            return $m . ' minute' . ($m > 1 ? 's' : '');
        } elseif ($seconds < 86400) {
            $h = ceil($seconds / 3600);
            return $h . ' hour' . ($h > 1 ? 's' : '');
        }
        $d = ceil($seconds / 86400);
        return $d . ' day' . ($d > 1 ? 's' : '');
    }

    /**
     * Get next tier message for warning
     */
    protected function getNextTierMessage($attempts)
    {
        if ($attempts < 5) return '1 minute';
        if ($attempts < 10) return '3 minutes';
        if ($attempts < 15) return '1 hour';
        return '24 hours';
    }
}

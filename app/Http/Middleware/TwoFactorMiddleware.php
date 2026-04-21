<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\TwoFactorService;
use Symfony\Component\HttpFoundation\Response;

class TwoFactorMiddleware
{
    protected TwoFactorService $twoFactorService;

    public function __construct(TwoFactorService $twoFactorService)
    {
        $this->twoFactorService = $twoFactorService;
    }

    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (!$user) {
            return $next($request);
        }

        // Check if 2FA is enabled for user
        if ($this->twoFactorService->isEnabled($user)) {
            // Check if already verified in this session
            if (!session()->has('2fa_verified') || !session('2fa_verified')) {
                // Store intended URL
                session()->put('url.intended', $request->url());

                return redirect()->route('two-factor.challenge');
            }
        }

        return $next($request);
    }
}

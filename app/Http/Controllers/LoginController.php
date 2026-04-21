<?php

namespace App\Http\Controllers;

use App\Constants\Roles;
use App\Http\Traits\RateLimitsLogins;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LoginController extends Controller
{
    use RateLimitsLogins;

    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect('/dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        try {
            $ipKey = 'login:' . $request->ip();
            $emailKey = 'login:' . strtolower((string) $request->input('email', ''));
            $key = $ipKey; // Primary key for rate limiting

            // Check if locked out (by IP or by email)
            $lockout = $this->isLockedOut($ipKey);
            if (!$lockout['locked']) {
                $lockout = $this->isLockedOut($emailKey);
            }
            if ($lockout['locked']) {
                return back()->withErrors([
                    'email' => 'Too many failed login attempts. Please try again in ' . $this->formatTime($lockout['remaining']) . '.',
                ]);
            }

            $request->validate([
                'email' => 'required|email',
                'password' => 'required|string',
            ]);

            $credentials = $request->only('email', 'password');

            if (Auth::attempt($credentials, $request->filled('remember'))) {
                // Validate role and account status — use generic message to avoid info leakage
                if (Auth::user()->role !== Roles::STUDENT || (int) Auth::user()->stat !== 1) {
                    Auth::logout();
                    return back()->withErrors([
                        'email' => 'Invalid credentials.',
                    ]);
                }

                // Clear rate limits on successful login
                $this->clearRateLimits($ipKey);
                $this->clearRateLimits($emailKey);

                $user = Auth::user();
                $user->last_login = now();
                $user->save();

                // Record daily login for gamification (points + streak)
                app(\App\Services\GamificationService::class)->recordDailyLogin($user);

                $request->session()->regenerate();
                $request->session()->put('login_at', now());
                $request->session()->flash('show_login_loader', true);
                $request->session()->forget('url.intended');

                return redirect('/student/dashboard');
            }

            // Record failed attempt on both IP and email keys
            $this->recordFailedAttempt($ipKey);
            $this->recordFailedAttempt($emailKey);

            // Show warning when approaching lockout
            $config = $this->getRateLimitConfig($ipKey);
            $remaining = $config['max'] - $config['attempts'];
            $warning = '';

            if ($remaining > 0 && $remaining <= 2) {
                $nextTier = $this->getNextTierMessage($config['attempts'] + 1);
                $warning = ' (' . $remaining . ' attempt' . ($remaining > 1 ? 's' : '') . ' until ' . $nextTier . ' lockout)';
            }

            return back()->withErrors([
                'email' => 'Invalid credentials.' . $warning,
            ])->withInput($request->only('email'));
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('LoginController::login failed', [
                'error' => $e->getMessage(),
                'user' => auth()->id(),
            ]);
            return back()->with('error', 'Login failed. Please try again.');
        }
    }

    public function logout(Request $request)
    {
        try {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            session()->flush();

            return redirect('/login')->with('status', 'You have been logged out successfully.');
        } catch (\Exception $e) {
            Log::error('LoginController::logout failed', [
                'error' => $e->getMessage(),
                'user' => auth()->id(),
            ]);
            return redirect('/login');
        }
    }
}

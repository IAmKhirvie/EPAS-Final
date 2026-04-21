<?php

namespace App\Http\Controllers;

use App\Constants\Roles;
use App\Http\Traits\RateLimitsLogins;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PrivateLoginController extends Controller
{
    use RateLimitsLogins;

    /**
     * Show admin login form
     */
    public function showAdminLoginForm()
    {
        if (Auth::check()) {
            return $this->redirectToRoleDashboard();
        }

        return view('private.admin-login');
    }

    /**
     * Show instructor login form
     */
    public function showInstructorLoginForm()
    {
        if (Auth::check()) {
            return $this->redirectToRoleDashboard();
        }

        return view('private.instructor-login');
    }

    /**
     * Handle admin login
     */
    public function adminLogin(Request $request)
    {
        return $this->handleLogin($request, Roles::ADMIN);
    }

    /**
     * Handle instructor login
     */
    public function instructorLogin(Request $request)
    {
        return $this->handleLogin($request, Roles::INSTRUCTOR);
    }

    /**
     * Handle login with role validation
     */
    protected function handleLogin(Request $request, string $expectedRole)
    {
        try {
            $ipKey = "{$expectedRole}-login:" . $request->ip();
            $emailKey = "{$expectedRole}-login:" . strtolower((string) $request->input('email', ''));
            $key = $ipKey;

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
                $user = Auth::user();

                // Validate role and account status — use generic message to avoid info leakage
                if ($user->role !== $expectedRole || (int) $user->stat !== 1) {
                    Auth::logout();
                    return back()->withErrors([
                        'email' => 'Invalid credentials.',
                    ]);
                }

                // Clear rate limits on successful login
                $this->clearRateLimits($ipKey);
                $this->clearRateLimits($emailKey);

                $user->last_login = now();
                $user->save();

                // Record daily login for gamification
                app(\App\Services\GamificationService::class)->recordDailyLogin($user);

                $request->session()->regenerate();
                $request->session()->put('login_at', now());
                $request->session()->flash('show_login_loader', true);
                $request->session()->forget('url.intended');

                return redirect('/admin/dashboard');
            }

            // Record failed attempt on both keys
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
            Log::error('PrivateLoginController::handleLogin failed', [
                'error' => $e->getMessage(),
                'user' => auth()->id(),
                'role' => $expectedRole,
            ]);
            return back()->with('error', 'Login failed. Please try again.');
        }
    }

    private function redirectToRoleDashboard()
    {
        $user = Auth::user();
        if (Roles::canManageStudents($user->role)) {
            return redirect('/admin/dashboard');
        }
        return redirect('/student/dashboard');
    }
}

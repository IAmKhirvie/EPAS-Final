<?php

namespace App\Http\Controllers;

use App\Services\TwoFactorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TwoFactorController extends Controller
{
    protected TwoFactorService $twoFactorService;

    public function __construct(TwoFactorService $twoFactorService)
    {
        $this->twoFactorService = $twoFactorService;
    }

    public function setup()
    {
        try {
            $user = auth()->user();

            if ($this->twoFactorService->isEnabled($user)) {
                return redirect()->route('two-factor.manage')
                    ->with('info', 'Two-factor authentication is already enabled.');
            }

            $secret = $this->twoFactorService->generateSecret();
            session()->put('2fa_setup_secret', $secret);

            $qrCode = $this->twoFactorService->getQrCodeSvg($user, $secret);

            return view('auth.two-factor.setup', compact('secret', 'qrCode'));
        } catch (\Exception $e) {
            Log::error('TwoFactorController::setup failed', [
                'error' => $e->getMessage(),
                'user' => auth()->id(),
            ]);
            return back()->with('error', 'Two-factor setup failed. Please try again.');
        }
    }

    public function enable(Request $request)
    {
        try {
            $request->validate([
                'code' => 'required|string|size:6',
            ]);

            $user = auth()->user();
            $secret = session('2fa_setup_secret');

            if (!$secret) {
                return redirect()->route('two-factor.setup')
                    ->with('error', 'Setup session expired. Please try again.');
            }

            if (!$this->twoFactorService->enable($user, $secret, $request->code)) {
                return back()->with('error', 'Invalid verification code. Please try again.');
            }

            session()->forget('2fa_setup_secret');
            $backupCodes = $this->twoFactorService->getBackupCodes($user);

            return view('auth.two-factor.enabled', compact('backupCodes'));
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('TwoFactorController::enable failed', [
                'error' => $e->getMessage(),
                'user' => auth()->id(),
            ]);
            return back()->with('error', 'Failed to enable two-factor authentication. Please try again.');
        }
    }

    public function manage()
    {
        $user = auth()->user();
        $isEnabled = $this->twoFactorService->isEnabled($user);

        return view('auth.two-factor.manage', compact('isEnabled'));
    }

    public function disable(Request $request)
    {
        try {
            $request->validate([
                'password' => 'required|current_password',
            ]);

            $this->twoFactorService->disable(auth()->user());

            return redirect()->route('two-factor.manage')
                ->with('success', 'Two-factor authentication has been disabled.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('TwoFactorController::disable failed', [
                'error' => $e->getMessage(),
                'user' => auth()->id(),
            ]);
            return back()->with('error', 'Failed to disable two-factor authentication. Please try again.');
        }
    }

    public function challenge()
    {
        if (!$this->twoFactorService->isEnabled(auth()->user())) {
            return redirect()->intended('/dashboard');
        }

        return view('auth.two-factor.challenge');
    }

    public function verify(Request $request)
    {
        try {
            $request->validate([
                'code' => 'required|string',
            ]);

            $user = auth()->user();

            if (!$this->twoFactorService->verifyForUser($user, $request->code)) {
                return back()->with('error', 'Invalid verification code.');
            }

            // Regenerate session ID after successful 2FA to prevent session fixation
            session()->regenerate();
            session()->put('2fa_verified', true);
            session()->put('2fa_verified_at', now());

            return redirect()->intended('/dashboard');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('TwoFactorController::verify failed', [
                'error' => $e->getMessage(),
                'user' => auth()->id(),
            ]);
            return back()->with('error', 'Verification failed. Please try again.');
        }
    }

    public function regenerateBackupCodes(Request $request)
    {
        try {
            $request->validate([
                'password' => 'required|current_password',
            ]);

            $codes = $this->twoFactorService->regenerateBackupCodes(auth()->user());

            return view('auth.two-factor.backup-codes', compact('codes'));
        } catch (\Exception $e) {
            Log::error('TwoFactorController::regenerateBackupCodes failed', [
                'error' => $e->getMessage(),
                'user' => auth()->id(),
            ]);
            return back()->with('error', 'Failed to regenerate backup codes. Please try again.');
        }
    }
}

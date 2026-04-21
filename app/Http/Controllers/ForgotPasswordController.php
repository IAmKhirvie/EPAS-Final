<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use App\Services\PHPMailerService;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ForgotPasswordController extends Controller
{
    public function showLinkRequestForm()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        try {
            DB::beginTransaction();

            $user = User::where('email', $request->email)->first();

            // Security: Use same message regardless of whether email exists (prevents user enumeration)
            if (!$user) {
                return back()->with('status', 'If an account exists with that email, a password reset link has been sent.');
            }

            // Generate reset token
            $token = Str::random(60);

            $user->forceFill([
                'reset_token' => Hash::make($token),
                'reset_token_expires' => now()->addHours(1)
            ])->save();

            // Send reset email using PHPMailerService
            $mailer = app(PHPMailerService::class);
            $resetUrl = URL::temporarySignedRoute(
                'password.reset',
                now()->addHours(1),
                ['token' => $token, 'email' => $user->email]
            );

            $result = $mailer->sendPasswordResetEmail($user, $resetUrl);

            DB::commit();

            if ($result) {
                return back()->with('status', 'Password reset link has been sent to your email!');
            }

            return back()->withErrors(['email' => 'Failed to send reset email. Please check your email configuration or contact support.']);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Password reset failed: ' . $e->getMessage());
            return back()->withErrors(['email' => 'An error occurred. Please try again.']);
        }
    }

    public function showResetForm(Request $request, $token = null)
    {
        return view('auth.reset-password', ['token' => $token, 'email' => $request->email]);
    }

    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => [
                'required',
                'confirmed',
                'min:8',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/',
            ],
        ]);

        try {
            DB::beginTransaction();

            $user = User::where('email', $request->email)
                        ->whereNotNull('reset_token')
                        ->where('reset_token_expires', '>', now())
                        ->first();

            if (!$user || !Hash::check($request->token, $user->reset_token)) {
                return back()->withErrors(['email' => 'Invalid or expired reset token.']);
            }

            $user->forceFill([
                'password' => Hash::make($request->password),
                'reset_token' => null,
                'reset_token_expires' => null
            ])->save();

            DB::commit();

            return redirect('/login')->with('status', 'Password has been reset successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['email' => 'An error occurred. Please try again.']);
        }
    }
}
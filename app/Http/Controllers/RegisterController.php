<?php

namespace App\Http\Controllers;

use App\Models\Registration;
use App\Models\User;
use App\Services\RegistrationService;
use App\Services\PHPMailerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class RegisterController extends Controller
{
    protected RegistrationService $registrationService;

    public function __construct(PHPMailerService $mailer)
    {
        $this->registrationService = new RegistrationService($mailer);
    }

    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_id' => ['required', 'string', 'max:25', 'regex:/^MAR.+$/', 'unique:users,student_id'],
            'first_name' => ['required', 'string', 'max:255', 'regex:/^[\pL\s\-\'\.]+$/u'],
            'middle_name' => ['nullable', 'string', 'max:255', 'regex:/^[\pL\s\-\'\.]+$/u'],
            'last_name' => ['required', 'string', 'max:255', 'regex:/^[\pL\s\-\'\.]+$/u'],
            'ext_name' => ['nullable', 'string', 'max:10', 'regex:/^[\pL\s\-\'\.]+$/u'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                // Check both users and registrations tables
                function ($attribute, $value, $fail) {
                    if (User::where('email', $value)->exists()) {
                        $fail('This email is already registered.');
                    }
                    if (Registration::where('email', $value)
                        ->whereNotIn('status', [Registration::STATUS_REJECTED, Registration::STATUS_TRANSFERRED])
                        ->exists()) {
                        $fail('A registration with this email is pending. Please check your email or contact support.');
                    }
                },
            ],
            'password' => [
                'required',
                'confirmed',
                'min:8',
                'regex:' . config('joms.password.regex'),
            ],
            'terms' => 'required|accepted',
        ], [
            'password.regex' => config('joms.password.message', 'The password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.'),
            'terms.required' => 'You must accept the Terms and Conditions and Privacy Policy.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // Create registration (pending user)
            $registration = $this->registrationService->createRegistration([
                'student_id' => $request->student_id,
                'first_name' => $request->first_name,
                'middle_name' => $request->middle_name,
                'last_name' => $request->last_name,
                'ext_name' => $request->ext_name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            // Send verification email
            $emailSent = $this->registrationService->sendVerificationEmail($registration);

            if (!$emailSent) {
                Log::error("Failed to send verification email for registration ID: {$registration->id}");
                // Don't fail registration, just warn
            }

            return redirect()->route('login')
                ->with('status', 'Registration submitted successfully!')
                ->with('verification_sent', 'Please check your email to verify your account. After verification, an admin will review your registration.');

        } catch (\Exception $e) {
            Log::error("Registration failed: " . $e->getMessage());
            return redirect()->back()
                ->withErrors(['email' => 'Registration failed. Please try again.'])
                ->withInput();
        }
    }

    /**
     * Verify registration email
     */
    public function verifyEmail(string $token)
    {
        $result = $this->registrationService->verifyEmail($token);

        if (!$result['success']) {
            return redirect()->route('login')
                ->withErrors(['email' => $result['message']]);
        }

        if (isset($result['transferred']) && $result['transferred']) {
            return redirect()->route('login')
                ->with('status', $result['message']);
        }

        return redirect()->route('login')
            ->with('status', $result['message']);
    }

    /**
     * Resend verification email
     */
    public function resendVerification(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $registration = Registration::where('email', $request->email)
            ->whereIn('status', [Registration::STATUS_PENDING])
            ->first();

        if (!$registration) {
            return redirect()->back()
                ->withErrors(['email' => 'No pending registration found for this email.']);
        }

        $sent = $this->registrationService->resendVerificationEmail($registration);

        if ($sent) {
            return redirect()->back()
                ->with('status', 'Verification email sent! Please check your inbox.');
        }

        return redirect()->back()
            ->withErrors(['email' => 'Failed to send verification email. Please try again.']);
    }

    /**
     * Check registration status
     */
    public function checkStatus(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $status = $this->registrationService->checkStatus($request->email);

        if (!$status) {
            return response()->json([
                'found' => false,
                'message' => 'No registration found for this email.',
            ]);
        }

        return response()->json([
            'found' => true,
            ...$status,
        ]);
    }
}

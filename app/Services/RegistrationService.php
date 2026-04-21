<?php

namespace App\Services;

use App\Constants\Roles;
use App\Models\Registration;
use App\Models\User;
use App\Models\Announcement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class RegistrationService
{
    protected PHPMailerService $mailerService;
    protected NotificationService $notificationService;

    public function __construct(PHPMailerService $mailerService)
    {
        $this->mailerService = $mailerService;
        $this->notificationService = new NotificationService($mailerService);
    }

    /**
     * Create a new registration (pending user)
     */
    public function createRegistration(array $data): Registration
    {
        $registration = Registration::create([
            'first_name' => $data['first_name'],
            'middle_name' => $data['middle_name'] ?? null,
            'last_name' => $data['last_name'],
            'ext_name' => $data['ext_name'] ?? null,
            'email' => $data['email'],
            'password' => $data['password'], // Already hashed
            'status' => Registration::STATUS_PENDING,
        ]);

        // Generate verification token
        $registration->generateVerificationToken();

        // Notify admins about new registration
        $this->notificationService->notifyNewRegistration(
            $registration->full_name,
            $registration->email
        );

        // Also create an announcement for visibility
        $this->createAnnouncementForAdmins(
            'New Student Registration',
            "New student registration: {$registration->full_name} ({$registration->email}). Pending email verification and admin approval.",
            'admin,instructor'
        );

        // Send email to admins
        $this->emailAdmins(
            'New Student Registration - EPAS-E',
            "<h2>New Student Registration</h2><p>A new student has registered:</p><ul><li><strong>Name:</strong> {$registration->full_name}</li><li><strong>Email:</strong> {$registration->email}</li></ul><p>Status: Pending email verification</p>",
            "New student registration: {$registration->full_name} ({$registration->email}). Status: Pending email verification."
        );

        return $registration;
    }

    /**
     * Send verification email to pending registration
     */
    public function sendVerificationEmail(Registration $registration): bool
    {
        $verificationUrl = url('/verify-registration/' . $registration->verification_token);

        // Create a temporary user object for the mailer
        $tempUser = new \stdClass();
        $tempUser->id = $registration->id;
        $tempUser->first_name = $registration->first_name;
        $tempUser->last_name = $registration->last_name;
        $tempUser->email = $registration->email;

        return $this->mailerService->sendVerificationEmail($tempUser, $verificationUrl);
    }

    /**
     * Verify email by token
     */
    public function verifyEmail(string $token): array
    {
        $registration = Registration::where('verification_token', $token)
            ->where('verification_token_expires', '>', now())
            ->first();

        if (!$registration) {
            return [
                'success' => false,
                'message' => 'Invalid or expired verification link.',
            ];
        }

        if ($registration->isEmailVerified()) {
            return [
                'success' => true,
                'message' => 'Email already verified. Waiting for admin approval.',
                'registration' => $registration,
            ];
        }

        $readyToTransfer = $registration->markEmailAsVerified();

        // Notify admins that email was verified
        $this->notificationService->notifyRegistrationVerified(
            $registration->full_name,
            $registration->email
        );

        // Create announcement for admins
        $this->createAnnouncementForAdmins(
            'Email Verified - Awaiting Approval',
            "Student {$registration->full_name} ({$registration->email}) has verified their email. Ready for admin approval.",
            'admin,instructor'
        );

        // Send email to admins
        $reviewUrl = url('/admin/registrations');
        $this->emailAdmins(
            'Registration Verified - Action Required',
            "<h2>Email Verified - Action Required</h2><p>Student <strong>{$registration->full_name}</strong> has verified their email.</p><ul><li><strong>Email:</strong> {$registration->email}</li></ul><p>This registration is ready for admin approval.</p><p><a href='{$reviewUrl}'>Review Registrations</a></p>",
            "Student {$registration->full_name} ({$registration->email}) verified their email. Review at: {$reviewUrl}"
        );

        if ($readyToTransfer) {
            $user = $this->transferToUsers($registration);
            if ($user) {
                return [
                    'success' => true,
                    'message' => 'Email verified and registration approved! You can now login.',
                    'transferred' => true,
                    'user' => $user,
                ];
            }
        }

        return [
            'success' => true,
            'message' => 'Email verified successfully! Please wait for admin approval.',
            'registration' => $registration,
        ];
    }

    /**
     * Admin approves a registration
     */
    public function approveRegistration(Registration $registration, int $adminId): array
    {
        if ($registration->status === Registration::STATUS_TRANSFERRED) {
            return [
                'success' => false,
                'message' => 'This registration has already been processed.',
            ];
        }

        if ($registration->status === Registration::STATUS_REJECTED) {
            return [
                'success' => false,
                'message' => 'This registration was rejected. Create a new one.',
            ];
        }

        $readyToTransfer = $registration->approve($adminId);

        if ($readyToTransfer) {
            $user = $this->transferToUsers($registration);
            if ($user) {
                return [
                    'success' => true,
                    'message' => 'Registration approved and user account created!',
                    'transferred' => true,
                    'user' => $user,
                ];
            }
        }

        return [
            'success' => true,
            'message' => 'Registration approved. Waiting for email verification.',
            'registration' => $registration,
        ];
    }

    /**
     * Admin rejects a registration
     */
    public function rejectRegistration(Registration $registration, int $adminId, ?string $reason = null): array
    {
        if ($registration->status === Registration::STATUS_TRANSFERRED) {
            return [
                'success' => false,
                'message' => 'This registration has already been processed.',
            ];
        }

        $registration->reject($adminId, $reason);

        // Optionally send rejection email
        $this->sendRejectionEmail($registration, $reason);

        return [
            'success' => true,
            'message' => 'Registration rejected.',
        ];
    }

    /**
     * Transfer approved registration to users table
     */
    public function transferToUsers(Registration $registration): ?User
    {
        if (!$registration->isReadyToTransfer()) {
            Log::warning("Attempted to transfer registration {$registration->id} but not ready");
            return null;
        }

        try {
            DB::beginTransaction();

            // Create user in users table
            $user = User::create([
                'first_name' => $registration->first_name,
                'middle_name' => $registration->middle_name,
                'last_name' => $registration->last_name,
                'ext_name' => $registration->ext_name,
                'email' => $registration->email,
                'password' => $registration->password,
                'role' => Roles::STUDENT,
                'department_id' => 1,
                'stat' => 1, // Active
                'email_verified_at' => $registration->email_verified_at,
            ]);

            // Mark registration as transferred
            $registration->update(['status' => Registration::STATUS_TRANSFERRED]);

            DB::commit();

            Log::info("Registration {$registration->id} transferred to user {$user->id}");

            // Send welcome email and account approved notification
            $this->sendWelcomeEmail($user);
            $this->notificationService->notifyAccountApproved($user);

            return $user;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to transfer registration {$registration->id}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Send welcome email after successful registration transfer
     */
    protected function sendWelcomeEmail(User $user): void
    {
        try {
            // You can implement a welcome email here
            Log::info("Welcome email should be sent to: {$user->email}");
        } catch (\Exception $e) {
            Log::error("Failed to send welcome email: " . $e->getMessage());
        }
    }

    /**
     * Send rejection email
     */
    protected function sendRejectionEmail(Registration $registration, ?string $reason): void
    {
        try {
            $subject = 'Registration Update';
            $body = "Unfortunately, your EPAS-E LMS registration has been rejected.";
            if ($reason) {
                $body .= "\n\nReason: {$reason}";
            }
            $body .= "\n\nIf you believe this was a mistake, please contact the administrator.";

            $this->mailerService->sendNotificationEmail(
                $registration->email,
                $registration->full_name ?? ($registration->first_name . ' ' . $registration->last_name),
                $subject,
                nl2br(htmlspecialchars($body)),
                $body
            );
        } catch (\Exception $e) {
            Log::error("Failed to send rejection email: " . $e->getMessage());
        }
    }

    /**
     * Resend verification email
     */
    public function resendVerificationEmail(Registration $registration): bool
    {
        if ($registration->isEmailVerified()) {
            return false;
        }

        // Generate new token
        $registration->generateVerificationToken();

        return $this->sendVerificationEmail($registration);
    }

    /**
     * Check registration status by email
     */
    public function checkStatus(string $email): ?array
    {
        $registration = Registration::where('email', $email)->first();

        if (!$registration) {
            // Check if already in users table
            $user = User::where('email', $email)->first();
            if ($user) {
                return [
                    'status' => 'active',
                    'message' => 'Account is active. You can login.',
                ];
            }
            return null;
        }

        return [
            'status' => $registration->status,
            'email_verified' => $registration->isEmailVerified(),
            'admin_approved' => $registration->isAdminApproved(),
            'message' => $this->getStatusMessage($registration),
        ];
    }

    /**
     * Get human-readable status message
     */
    protected function getStatusMessage(Registration $registration): string
    {
        return match ($registration->status) {
            Registration::STATUS_PENDING => 'Please verify your email address.',
            Registration::STATUS_EMAIL_VERIFIED => 'Email verified. Waiting for admin approval.',
            Registration::STATUS_APPROVED => 'Approved! Your account is being created.',
            Registration::STATUS_REJECTED => 'Registration was rejected: ' . ($registration->rejection_reason ?? 'No reason provided'),
            Registration::STATUS_TRANSFERRED => 'Account created. You can login.',
            default => 'Unknown status.',
        };
    }

    /**
     * Send email notification to all active admins
     */
    protected function emailAdmins(string $subject, string $bodyHtml, string $bodyText): void
    {
        try {
            $admins = User::where('role', Roles::ADMIN)->where('stat', 1)->get();
            foreach ($admins as $admin) {
                $this->mailerService->sendNotificationEmail(
                    $admin->email,
                    $admin->full_name,
                    $subject,
                    $bodyHtml,
                    $bodyText
                );
            }
        } catch (\Exception $e) {
            Log::error("Failed to email admins: " . $e->getMessage());
        }
    }

    /**
     * Create an announcement visible to admins only (for internal notifications like registrations)
     */
    protected function createAnnouncementForAdmins(string $title, string $content, string $targetRoles = 'admin'): void
    {
        try {
            Announcement::create([
                'user_id' => 1, // System user
                'title' => $title,
                'content' => $content,
                'is_pinned' => false,
                'is_urgent' => true,
                'target_roles' => $targetRoles,
                'publish_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to create admin announcement: " . $e->getMessage());
        }
    }
}

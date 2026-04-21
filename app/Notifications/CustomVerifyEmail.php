<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use App\Services\PHPMailerService;

class CustomVerifyEmail extends Notification
{
    use Queueable;

    protected $mailer;

    public function __construct()
    {
        $this->mailer = app(PHPMailerService::class);
    }

    public function via($notifiable)
    {
        return ['mail']; // Use mail channel to prevent double sending
    }

    public function toMail($notifiable)
    {
        $verificationUrl = $this->verificationUrl($notifiable);
        
        Log::info("Sending verification email to: {$notifiable->email}");
        Log::info("Verification URL: {$verificationUrl}");

        // Send email directly via PHPMailer
        $emailSent = $this->mailer->sendVerificationEmail($notifiable, $verificationUrl);
        
        if (!$emailSent) {
            Log::error("Failed to send verification email to: {$notifiable->email}");
            throw new \Exception('Failed to send verification email');
        }

        // Return a simple mail message for Laravel (won't be used since we're using PHPMailer directly)
        return (new \Illuminate\Notifications\Messages\MailMessage)
            ->subject('Verify Your Email - EPAS-E LMS')
            ->line('Please verify your email address.');
    }

    protected function verificationUrl($notifiable)
    {
        // Use the same format as Laravel's built-in verification
        return URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );
    }
}
<?php

namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
use Illuminate\Support\Facades\Log;

class PHPMailerService
{
    protected $mail;
    protected $debugOutput = '';

    public function __construct()
    {
        $this->mail = new PHPMailer(true);

        // Server settings — use config() so values survive config:cache
        $this->mail->isSMTP();
        $this->mail->Host       = config('joms.mail.host', 'smtp.gmail.com');
        $this->mail->SMTPAuth   = true;
        $this->mail->Username   = config('joms.mail.username');
        $this->mail->Password   = config('joms.mail.password');
        $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mail->Port       = (int) config('joms.mail.port', 587);

        // SSL certificate verification: disabled in local/dev, enabled in production
        if (config('app.env') === 'production') {
            $this->mail->SMTPOptions = [
                'ssl' => [
                    'verify_peer' => true,
                    'verify_peer_name' => true,
                    'allow_self_signed' => false,
                ],
            ];
        } else {
            $this->mail->SMTPOptions = [
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true,
                ],
            ];
        }

        // Enable debug output in local environment
        if (config('app.debug', false)) {
            $this->mail->SMTPDebug = SMTP::DEBUG_SERVER;
            $this->mail->Debugoutput = function($str, $level) {
                $this->debugOutput .= "[$level] $str\n";
                Log::debug("PHPMailer SMTP: [$level] $str");
            };
        }

        // Set timeout to avoid hanging
        $this->mail->Timeout = (int) config('joms.mail.timeout', 30);
        $this->mail->SMTPKeepAlive = false;

        // Character encoding
        $this->mail->CharSet = PHPMailer::CHARSET_UTF8;

        // Sender settings
        $fromAddress = config('joms.mail.from_address', config('joms.mail.username'));
        $fromName = config('joms.mail.from_name', 'EPAS-E LMS');

        $this->mail->setFrom($fromAddress, $fromName);
        $this->mail->addReplyTo($fromAddress, $fromName);

        Log::info("PHPMailerService initialized", ['host' => config('joms.mail.host'), 'from' => $fromAddress]);
    }

    /**
     * Check if SMTP credentials are still placeholders.
     */
    public function hasValidCredentials(): bool
    {
        $username = config('joms.mail.username', '');
        $password = config('joms.mail.password', '');
        $placeholders = ['your-email@example.com', 'your-gmail@gmail.com', 'your-app-password', 'your-16-char-app-password', '', null];

        return !in_array($username, $placeholders, true) && !in_array($password, $placeholders, true);
    }

    public function sendVerificationEmail($user, $verificationUrl)
    {
        try {
            if (!$this->hasValidCredentials()) {
                Log::warning('SMTP credentials not configured. Email not sent.', ['to' => $user->email ?? 'unknown']);
                return false;
            }

            $this->debugOutput = '';
            $userId = $user->id ?? 'N/A';
            Log::info("Sending verification email to user ID: {$userId}, email: {$user->email}");

            // Clear any previous addresses and attachments
            $this->mail->clearAddresses();
            $this->mail->clearAttachments();
            $this->mail->clearReplyTos();

            // Re-add reply-to after clearing
            $fromAddress = config('joms.mail.from_address', config('joms.mail.username'));
            $fromName = config('joms.mail.from_name', 'EPAS-E LMS');
            $this->mail->addReplyTo($fromAddress, $fromName);

            // Validate email address
            if (empty($user->email) || !filter_var($user->email, FILTER_VALIDATE_EMAIL)) {
                Log::error("Invalid or empty email address: " . ($user->email ?? 'NULL'));
                return false;
            }

            // Get the recipient name safely
            $recipientName = trim($user->first_name . ' ' . $user->last_name);
            if (empty($recipientName)) {
                $recipientName = 'User';
            }

            Log::info("Recipient: {$user->email} ({$recipientName})");

            // Recipient
            $this->mail->addAddress($user->email, $recipientName);

            // Content
            $this->mail->isHTML(true);
            $this->mail->Subject = 'Verify Your Email - EPAS-E LMS';
            $this->mail->Body    = $this->getVerificationEmailTemplate($user, $verificationUrl);
            $this->mail->AltBody = $this->getPlainTextVerificationEmail($user, $verificationUrl);

            $result = $this->mail->send();

            if ($result) {
                Log::info("SUCCESS: Verification email sent to {$user->email}");
                return true;
            } else {
                Log::error("FAILED: PHPMailer send() returned false for {$user->email}");
                Log::error("PHPMailer Error: {$this->mail->ErrorInfo}");
                if (!empty($this->debugOutput)) {
                    Log::error("SMTP Debug Output:\n{$this->debugOutput}");
                }
                return false;
            }

        } catch (Exception|\Exception $e) {
            Log::error("Exception sending email to {$user->email}: " . $e->getMessage());
            if (!empty($this->mail->ErrorInfo)) {
                Log::error("PHPMailer ErrorInfo: {$this->mail->ErrorInfo}");
            }
            if (!empty($this->debugOutput)) {
                Log::error("SMTP Debug Output:\n{$this->debugOutput}");
            }
            return false;
        }
    }

    protected function getVerificationEmailTemplate($user, $verificationUrl)
    {
        $fullName = htmlspecialchars($user->first_name . ' ' . $user->last_name, ENT_QUOTES, 'UTF-8');
        $firstName = htmlspecialchars($user->first_name, ENT_QUOTES, 'UTF-8');
        $email = htmlspecialchars($user->email, ENT_QUOTES, 'UTF-8');
        $safeUrl = htmlspecialchars($verificationUrl, ENT_QUOTES, 'UTF-8');
        
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #007bff; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
                .content { padding: 20px; background: #f9f9f9; }
                .button { 
                    display: inline-block; 
                    padding: 12px 24px; 
                    background: #007bff; 
                    color: #ffffff !important; 
                    text-decoration: none; 
                    border-radius: 5px; 
                    font-weight: bold;
                    text-align: center;
                }
                .footer { padding: 20px; text-align: center; font-size: 0.9em; color: #666; }
                .details { background: white; padding: 15px; border-radius: 5px; margin: 15px 0; border: 1px solid #ddd; }
                .url-box { background: #f8f9fa; padding: 10px; border-radius: 5px; border: 1px solid #dee2e6; word-break: break-all; font-size: 0.9em; }
            </style>
        </head>
        <body>
            <div class='header'>
                <h1 style='margin:0; color: #ffffff;'>EPAS-E LMS</h1>
                <p style='margin:0; color: #ffffff;'>Electronic Products Assembly and Servicing</p>
            </div>
            
            <div class='content'>
                <h2 style='color: #333;'>Verify Your Email Address</h2>
                
                <p>Hello <strong>{$firstName}</strong>,</p>

                <p>Thank you for registering with EPAS-E Learning Management System. Please verify your email address to complete your registration.</p>

                <div class='details'>
                    <p><strong>Account Details:</strong></p>
                    <ul>
                        <li><strong>Name:</strong> {$fullName}</li>
                        <li><strong>Email:</strong> {$email}</li>
                    </ul>
                </div>

                <p style='text-align: center;'>
                    <a href='{$safeUrl}' class='button' style='color: #ffffff !important;'>
                        Verify Email Address
                    </a>
                </p>

                <p>If the button doesn't work, copy and paste this link in your browser:</p>
                <div class='url-box'>{$safeUrl}</div>
                
                <p>If you did not create an account, please ignore this email.</p>
                
                <p><strong>Note:</strong> Your account requires administrative approval before you can access the system.</p>
            </div>
            
            <div class='footer'>
                <p>&copy; " . date('Y') . " EPAS-E LMS. All rights reserved.</p>
                <p>This is an automated message, please do not reply to this email.</p>
            </div>
        </body>
        </html>
        ";
    }

    protected function getPlainTextVerificationEmail($user, $verificationUrl)
    {
        $fullName = $user->first_name . ' ' . $user->last_name;
        
        return "
        Verify Your Email - EPAS-E LMS

        Hello {$user->first_name},

        Thank you for registering with EPAS-E Learning Management System.

        Account Details:
        - Name: {$fullName}
        - Email: {$user->email}

        Please verify your email address by clicking the link below:
        {$verificationUrl}

        If you did not create an account, please ignore this email.

        Note: Your account requires administrative approval before you can access the system.

        © " . date('Y') . " EPAS-E LMS. All rights reserved.
        ";
    }

    public function sendPasswordResetEmail($user, $resetUrl)
    {
        try {
            $this->debugOutput = '';
            Log::info("=== SENDING PASSWORD RESET EMAIL ===");
            Log::info("To: {$user->email}");
            Log::info("URL: {$resetUrl}");

            // Clear any previous addresses and attachments
            $this->mail->clearAddresses();
            $this->mail->clearAttachments();
            $this->mail->clearReplyTos();

            // Re-add reply-to after clearing
            $fromAddress = config('joms.mail.from_address', config('joms.mail.username'));
            $fromName = config('joms.mail.from_name', 'EPAS-E LMS');
            $this->mail->addReplyTo($fromAddress, $fromName);

            // Validate email address
            if (empty($user->email) || !filter_var($user->email, FILTER_VALIDATE_EMAIL)) {
                Log::error("Invalid or empty email address: " . ($user->email ?? 'NULL'));
                return false;
            }

            // Get the recipient name safely
            $recipientName = trim($user->first_name . ' ' . $user->last_name);
            if (empty($recipientName)) {
                $recipientName = 'User';
            }

            // Recipient
            $this->mail->addAddress($user->email, $recipientName);

            // Content
            $this->mail->isHTML(true);
            $this->mail->Subject = 'Reset Your Password - EPAS-E LMS';
            $this->mail->Body    = $this->getPasswordResetEmailTemplate($user, $resetUrl);
            $this->mail->AltBody = $this->getPlainTextPasswordResetEmail($user, $resetUrl);

            $result = $this->mail->send();

            if ($result) {
                Log::info("SUCCESS: Password reset email sent to {$user->email}");
                return true;
            } else {
                Log::error("FAILED: PHPMailer send() returned false for password reset: {$user->email}");
                Log::error("PHPMailer Error: {$this->mail->ErrorInfo}");
                if (!empty($this->debugOutput)) {
                    Log::error("SMTP Debug Output:\n{$this->debugOutput}");
                }
                return false;
            }

        } catch (Exception|\Exception $e) {
            Log::error("Exception sending password reset to {$user->email}: " . $e->getMessage());
            if (!empty($this->mail->ErrorInfo)) {
                Log::error("PHPMailer ErrorInfo: {$this->mail->ErrorInfo}");
            }
            if (!empty($this->debugOutput)) {
                Log::error("SMTP Debug Output:\n{$this->debugOutput}");
            }
            return false;
        }
    }

    protected function getPasswordResetEmailTemplate($user, $resetUrl)
    {
        $fullName = htmlspecialchars($user->first_name . ' ' . $user->last_name, ENT_QUOTES, 'UTF-8');
        $firstName = htmlspecialchars($user->first_name, ENT_QUOTES, 'UTF-8');
        $email = htmlspecialchars($user->email, ENT_QUOTES, 'UTF-8');
        $safeUrl = htmlspecialchars($resetUrl, ENT_QUOTES, 'UTF-8');
        
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #007bff; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
                .content { padding: 20px; background-color: #f9f9f9; }
                .button { 
                    display: inline-block; 
                    padding: 12px 24px; 
                    background-color: #007bff; 
                    color: #ffffff !important; 
                    text-decoration: none; 
                    border-radius: 5px; 
                    font-weight: bold;
                    text-align: center;
                }
                .footer { padding: 20px; text-align: center; font-size: 0.9em; color: #666; }
                .details { background-color: white; padding: 15px; border-radius: 5px; margin: 15px 0; border: 1px solid #ddd; }
                .warning { background-color: #fff3cd; border: 1px solid #ffeaa7; padding: 10px; border-radius: 5px; margin: 15px 0; color: #856404; }
                .url-box { background-color: #f8f9fa; padding: 10px; border-radius: 5px; border: 1px solid #dee2e6; word-break: break-all; font-size: 0.9em; }
            </style>
        </head>
        <body>
            <div class='header'>
                <h1 style='margin:0; color: #ffffff;'>EPAS-E LMS</h1>
                <p style='margin:0; color: #ffffff;'>Electronic Products Assembly and Servicing</p>
            </div>
            
            <div class='content'>
                <h2 style='color: #333;'>Reset Your Password</h2>
                
                <p>Hello <strong>{$firstName}</strong>,</p>

                <p>You are receiving this email because we received a password reset request for your account.</p>

                <div class='details'>
                    <p><strong>Account Details:</strong></p>
                    <ul>
                        <li><strong>Name:</strong> {$fullName}</li>
                        <li><strong>Email:</strong> {$email}</li>
                    </ul>
                </div>

                <p style='text-align: center;'>
                    <a href='{$safeUrl}' class='button' style='color: #ffffff !important;'>
                        Reset Password
                    </a>
                </p>

                <p>If the button doesn't work, copy and paste this link in your browser:</p>
                <div class='url-box'>{$safeUrl}</div>
                
                <div class='warning'>
                    <p><strong>Important:</strong> This password reset link will expire in 1 hour.</p>
                    <p>If you did not request a password reset, no further action is required.</p>
                </div>
            </div>
            
            <div class='footer'>
                <p>&copy; " . date('Y') . " EPAS-E LMS. All rights reserved.</p>
                <p>This is an automated message, please do not reply to this email.</p>
            </div>
        </body>
        </html>
        ";
    }

    protected function getPlainTextPasswordResetEmail($user, $resetUrl)
    {
        $fullName = $user->first_name . ' ' . $user->last_name;
        
        return "
        Reset Your Password - EPAS-E LMS

        Hello {$user->first_name},

        You are receiving this email because we received a password reset request for your account.

        Account Details:
        - Name: {$fullName}
        - Email: {$user->email}

        Please reset your password by clicking the link below:
        {$resetUrl}

        Important: This password reset link will expire in 1 hour.

        If you did not request a password reset, no further action is required.

        © " . date('Y') . " EPAS-E LMS. All rights reserved.
        ";
    }

    /**
     * Send a test email to verify SMTP configuration is working
     *
     * @param string $toEmail The email address to send the test to
     * @return array ['success' => bool, 'message' => string, 'debug' => string]
     */
    public function sendTestEmail(string $toEmail): array
    {
        try {
            $this->debugOutput = '';
            Log::info("Sending test email to: {$toEmail}");

            // Clear previous addresses
            $this->mail->clearAddresses();
            $this->mail->clearAttachments();
            $this->mail->clearReplyTos();

            // Re-add reply-to
            $fromAddress = config('joms.mail.from_address', config('joms.mail.username'));
            $fromName = config('joms.mail.from_name', 'EPAS-E LMS');
            $this->mail->addReplyTo($fromAddress, $fromName);

            // Validate email
            if (empty($toEmail) || !filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
                return [
                    'success' => false,
                    'message' => 'Invalid email address provided',
                    'debug' => ''
                ];
            }

            $this->mail->addAddress($toEmail, 'Test Recipient');

            $this->mail->isHTML(true);
            $this->mail->Subject = 'Test Email - EPAS-E LMS (' . date('Y-m-d H:i:s') . ')';
            $this->mail->Body = "
            <html>
            <body style='font-family: Arial, sans-serif; padding: 20px;'>
                <h2 style='color: #007bff;'>Email Configuration Test</h2>
                <p>This is a test email from EPAS-E LMS.</p>
                <p>If you received this email, your SMTP configuration is working correctly!</p>
                <hr>
                <p><strong>Sent at:</strong> " . date('Y-m-d H:i:s') . "</p>
            </body>
            </html>";
            $this->mail->AltBody = "Test email from EPAS-E LMS. Sent at: " . date('Y-m-d H:i:s');

            $result = $this->mail->send();

            if ($result) {
                Log::info("SUCCESS: Test email sent to {$toEmail}");
                return [
                    'success' => true,
                    'message' => "Test email sent successfully to {$toEmail}",
                    'debug' => $this->debugOutput
                ];
            } else {
                Log::error("FAILED: Test email not sent to {$toEmail}");
                Log::error("PHPMailer Error: {$this->mail->ErrorInfo}");
                return [
                    'success' => false,
                    'message' => "Failed to send: {$this->mail->ErrorInfo}",
                    'debug' => $this->debugOutput
                ];
            }

        } catch (Exception|\Exception $e) {
            Log::error("Exception sending test email: " . $e->getMessage());
            return [
                'success' => false,
                'message' => "Exception: " . $e->getMessage(),
                'debug' => $this->debugOutput
            ];
        }
    }

    /**
     * Send contact inquiry email to admin
     */
    public function sendContactInquiry(array $data): bool
    {
        try {
            if (!$this->hasValidCredentials()) {
                Log::warning('SMTP credentials not configured. Contact inquiry not sent.');
                return false;
            }

            $this->debugOutput = '';
            $this->mail->clearAddresses();
            $this->mail->clearAttachments();
            $this->mail->clearReplyTos();

            // Set reply-to as the person who submitted the form
            $this->mail->addReplyTo($data['email'], $data['name']);

            // Send to admin email
            $adminEmail = config('joms.mail.admin_email', config('joms.mail.username'));
            if (empty($adminEmail) || !filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
                Log::error('Invalid admin email for contact inquiry');
                return false;
            }

            $this->mail->addAddress($adminEmail, 'EPAS-E Admin');

            $this->mail->isHTML(true);
            $this->mail->Subject = '[EPAS-E Inquiry] ' . $data['subject'];
            $this->mail->Body = $this->getContactInquiryTemplate($data);
            $this->mail->AltBody = $this->getPlainTextContactInquiry($data);

            $result = $this->mail->send();
            if ($result) {
                Log::info("Contact inquiry sent to admin", ['from' => $data['email'], 'subject' => $data['subject']]);
            }
            return $result;
        } catch (\Exception $e) {
            Log::error("Failed to send contact inquiry: " . $e->getMessage());
            return false;
        }
    }

    protected function getContactInquiryTemplate(array $data): string
    {
        $name = htmlspecialchars($data['name'], ENT_QUOTES, 'UTF-8');
        $email = htmlspecialchars($data['email'], ENT_QUOTES, 'UTF-8');
        $subject = htmlspecialchars($data['subject'], ENT_QUOTES, 'UTF-8');
        $message = nl2br(htmlspecialchars($data['message'], ENT_QUOTES, 'UTF-8'));

        return "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #007bff; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
                .content { padding: 20px; background: #f9f9f9; }
                .details { background: white; padding: 15px; border-radius: 5px; margin: 15px 0; border: 1px solid #ddd; }
                .message-box { background: white; padding: 15px; border-radius: 5px; border-left: 4px solid #007bff; margin: 15px 0; }
                .footer { padding: 20px; text-align: center; font-size: 0.9em; color: #666; }
            </style>
        </head>
        <body>
            <div class='header'>
                <h1 style='margin:0; color: #ffffff;'>New Contact Inquiry</h1>
                <p style='margin:0; color: #ffffff;'>EPAS-E LMS</p>
            </div>

            <div class='content'>
                <h2 style='color: #333;'>{$subject}</h2>

                <div class='details'>
                    <p><strong>From:</strong> {$name}</p>
                    <p><strong>Email:</strong> <a href='mailto:{$email}'>{$email}</a></p>
                    <p><strong>Received:</strong> " . date('F j, Y g:i A') . "</p>
                </div>

                <h3>Message:</h3>
                <div class='message-box'>
                    {$message}
                </div>

                <p style='text-align: center;'>
                    <a href='mailto:{$email}?subject=Re: {$subject}'
                       style='display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px;'>
                        Reply to {$name}
                    </a>
                </p>
            </div>

            <div class='footer'>
                <p>This inquiry was submitted through the EPAS-E LMS contact form.</p>
            </div>
        </body>
        </html>
        ";
    }

    protected function getPlainTextContactInquiry(array $data): string
    {
        return "
New Contact Inquiry - EPAS-E LMS

Subject: {$data['subject']}

From: {$data['name']}
Email: {$data['email']}
Received: " . date('F j, Y g:i A') . "

Message:
{$data['message']}

---
This inquiry was submitted through the EPAS-E LMS contact form.
        ";
    }

    /**
     * Send a generic notification email (used for admin alerts)
     */
    public function sendNotificationEmail(string $toEmail, string $toName, string $subject, string $bodyHtml, string $bodyText): bool
    {
        try {
            $this->debugOutput = '';
            $this->mail->clearAddresses();
            $this->mail->clearAttachments();
            $this->mail->clearReplyTos();

            $fromAddress = config('joms.mail.from_address', config('joms.mail.username'));
            $fromName = config('joms.mail.from_name', 'EPAS-E LMS');
            $this->mail->addReplyTo($fromAddress, $fromName);

            if (empty($toEmail) || !filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
                Log::error("Invalid email for notification: " . ($toEmail ?? 'NULL'));
                return false;
            }

            $this->mail->addAddress($toEmail, $toName);
            $this->mail->isHTML(true);
            $this->mail->Subject = $subject;
            $this->mail->Body = $bodyHtml;
            $this->mail->AltBody = $bodyText;

            $result = $this->mail->send();
            if ($result) {
                Log::info("Notification email sent to {$toEmail}: {$subject}");
            }
            return $result;
        } catch (\Exception $e) {
            Log::error("Failed to send notification email to {$toEmail}: " . $e->getMessage());
            return false;
        }
    }

}
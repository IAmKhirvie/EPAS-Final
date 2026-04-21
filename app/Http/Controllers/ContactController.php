<?php

namespace App\Http\Controllers;

use App\Services\PHPMailerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ContactController extends Controller
{
    public function submit(Request $request, PHPMailerService $mailer)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:2000',
        ]);

        try {
            // Log the inquiry
            Log::info('Contact Form Submission', [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'subject' => $validated['subject'],
                'submitted_at' => now(),
            ]);

            // Send email notification to admin
            $emailSent = $mailer->sendContactInquiry($validated);

            if ($emailSent) {
                return back()->with('success', 'Thank you for your inquiry! We will get back to you within 1-2 business days.');
            } else {
                // Email failed but submission was logged
                Log::warning('Contact form email failed to send', ['email' => $validated['email']]);
                return back()->with('success', 'Thank you for your inquiry! We have received your message and will get back to you soon.');
            }
        } catch (\Exception $e) {
            Log::error('Contact form submission failed: ' . $e->getMessage());
            return back()->with('error', 'Sorry, there was an error sending your message. Please try again later.')->withInput();
        }
    }
}

<?php

namespace App\Services;

use App\Models\User;
use App\Models\Course;
use App\Models\Module;
use App\Models\Certificate;
use App\Mail\CertificateIssued;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CertificateService
{
    /**
     * Available certificate templates.
     */
    public const TEMPLATES = [
        'tesda' => 'TESDA NC II (Default)',
        'default' => 'Classic (Blue)',
        'gold' => 'Gold Premium',
        'modern' => 'Modern Minimal',
        'formal' => 'Formal/Traditional',
        'custom' => 'Custom Background',
    ];

    /**
     * Get available templates list.
     */
    public function getAvailableTemplates(): array
    {
        return self::TEMPLATES;
    }

    public function generateCertificate(User $user, Course $course, array $metadata = []): Certificate
    {
        // Check if certificate already exists
        $existing = Certificate::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->where('status', 'issued')
            ->first();

        if ($existing) {
            return $existing;
        }

        // Generate unique certificate number
        $certificateNumber = Certificate::generateCertificateNumber();

        // Get template from course or use default
        $template = $course->certificate_template ?? 'tesda';

        // Create certificate record
        $certificate = Certificate::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'certificate_number' => $certificateNumber,
            'title' => "Certificate of Completion - {$course->course_name}",
            'description' => "This certifies that {$user->full_name} has successfully completed the {$course->course_name} course.",
            'issue_date' => now(),
            'status' => 'issued',
            'template_used' => $template,
            'metadata' => array_merge([
                'completion_date' => now()->toDateString(),
                'course_name' => $course->course_name,
                'student_name' => $user->full_name,
            ], $metadata),
        ]);

        // Generate PDF
        $this->generatePdf($certificate);

        return $certificate;
    }


    /**
     * Generate and save the PDF – ALWAYS overwrites old file.
     */
    public function generatePdf(Certificate $certificate)
    {
        $data = [
            'user' => $certificate->user,
            'course' => $certificate->course,
            'certificate_number' => $certificate->certificate_number,
            'issue_date' => $certificate->issued_at ? $certificate->issued_at->format('F d, Y') : now()->format('F d, Y'),
            'config' => [
                'organization' => 'EPAS-E Learning Management System',
                'institution' => config('joms.institution_name', 'IETI College of Technology - Marikina'),
                'signatory_left_title' => 'School Administrator',
                'signatory_right_title' => 'Lead Instructor / Trainer',
            ],
        ];

        $template = $certificate->template_used ?? 'tesda';
        // Check if view exists in certificates.templates.*
        if (!view()->exists("certificates.templates.{$template}")) {
            Log::warning("Template '{$template}' not found, falling back to 'default'");
            $template = 'default';
        }
        $pdf = Pdf::loadView("certificates.templates.{$template}", $data);

        // Force A4 landscape and remove all margins
        $pdf->setPaper('a4', 'landscape');
        $pdf->setOptions([
            'defaultFont' => 'sans-serif',
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => false,
            'margin_top' => 0,
            'margin_right' => 0,
            'margin_bottom' => 0,
            'margin_left' => 0,
        ]);

        $pdfContent = $pdf->output();

        // Ensure a storage path exists
        $path = $certificate->pdf_path;
        if (!$path) {
            $path = 'certificates/' . uniqid() . '.pdf';
            $certificate->pdf_path = $path;
            $certificate->save();
        }

        // Overwrite the existing file (or create new)
        Storage::disk('public')->put($path, $pdfContent);

        return $pdfContent;
    }

    /**
     * Preview a certificate template without saving.
     */
    public function previewTemplate(string $template, User $user, Course $course): \Barryvdh\DomPDF\PDF
    {
        $config = $course->certificate_config ?? [];

        $data = [
            'certificate' => null,
            'user' => $user,
            'course' => $course,
            'issue_date' => now()->format('F d, Y'),
            'certificate_number' => 'CERT-PREVIEW-XXXXX',
            'config' => $config,
        ];

        $viewName = "certificates.templates.{$template}";
        if (!view()->exists($viewName)) {
            $viewName = 'certificates.templates.default';
        }

        return Pdf::loadView($viewName, $data)
            ->setPaper('a4', 'landscape')
            ->setOptions(['isRemoteEnabled' => true]);
    }

    /**
     * Download – always delete old and regenerate fresh.
     */
    public function downloadPdf(Certificate $certificate)
    {
        // Delete old PDF if it exists
        if ($certificate->pdf_path && Storage::disk('public')->exists($certificate->pdf_path)) {
            Storage::disk('public')->delete($certificate->pdf_path);
        }

        // Generate a brand new PDF
        $this->generatePdf($certificate);

        // Force browser to download with unique name (prevents caching)
        $filename = 'certificate_' . $certificate->id . '_' . time() . '.pdf';

        return response()->download(
            Storage::disk('public')->path($certificate->pdf_path),
            $filename,
            ['Content-Type' => 'application/pdf']
        );
    }

    public function verifyCertificate(string $certificateNumber): ?Certificate
    {
        return Certificate::where('certificate_number', $certificateNumber)
            ->where('status', 'issued')
            ->with(['user', 'course'])
            ->first();
    }

    public function revokeCertificate(Certificate $certificate, string $reason = null): bool
    {
        $certificate->update([
            'status' => 'revoked',
            'metadata' => array_merge($certificate->metadata ?? [], [
                'revoked_at' => now()->toDateTimeString(),
                'revoke_reason' => $reason,
            ]),
        ]);

        return true;
    }

    public function getUserCertificates(User $user)
    {
        return Certificate::forUser($user->id)
            ->issued()
            ->with('course')
            ->orderByDesc('issue_date')
            ->get();
    }

    public function checkCourseCompletion(User $user, Course $course): bool
    {
        // Get all modules for the course
        $modules = $course->modules()->where('is_active', true)->get();

        if ($modules->isEmpty()) {
            return false;
        }

        // Check if all modules are completed
        foreach ($modules as $module) {
            $progress = $user->progress()
                ->where('progressable_type', 'App\\Models\\Module')
                ->where('progressable_id', $module->id)
                ->where('status', 'completed')
                ->first();

            if (!$progress) {
                return false;
            }
        }

        return true;
    }

    /**
     * Generate a module-level certificate (requires approval workflow).
     */
    public function generateModuleCertificate(User $user, Module $module, array $metadata = []): Certificate
    {
        // Check if certificate already exists
        $existing = Certificate::where('user_id', $user->id)
            ->where('module_id', $module->id)
            ->whereNotIn('status', [Certificate::STATUS_REJECTED, Certificate::STATUS_REVOKED])
            ->first();

        if ($existing) {
            return $existing;
        }

        $course = $module->course;
        $certificateNumber = Certificate::generateCertificateNumber();
        $template = $course->certificate_template ?? 'tesda';

        // Create certificate with pending status (needs instructor approval first)
        $certificate = Certificate::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'module_id' => $module->id,
            'certificate_number' => $certificateNumber,
            'title' => "Certificate of Completion - {$module->module_title}",
            'description' => "This certifies that {$user->full_name} has successfully completed the {$module->module_title} module.",
            'status' => Certificate::STATUS_PENDING_INSTRUCTOR,
            'template_used' => $template,
            'requested_at' => now(),
            'metadata' => array_merge([
                'completion_date' => now()->toDateString(),
                'course_name' => $course->course_name,
                'module_name' => $module->module_title,
                'student_name' => $user->full_name,
            ], $metadata),
        ]);

        Log::info("Module certificate created for user {$user->id}, module {$module->id}", [
            'certificate_id' => $certificate->id,
        ]);

        return $certificate;
    }

    /**
     * Instructor approves a certificate.
     */
    public function instructorApprove(Certificate $certificate, User $instructor): bool
    {
        if ($certificate->status !== Certificate::STATUS_PENDING_INSTRUCTOR) {
            return false;
        }

        $certificate->update([
            'status' => Certificate::STATUS_PENDING_ADMIN,
            'instructor_approved_by' => $instructor->id,
            'instructor_approved_at' => now(),
        ]);

        Log::info("Certificate {$certificate->id} approved by instructor {$instructor->id}");

        return true;
    }

    /**
     * Admin approves a certificate (final approval - issues the certificate).
     */
    public function adminApprove(Certificate $certificate, User $admin): bool
    {
        if ($certificate->status !== Certificate::STATUS_PENDING_ADMIN) {
            return false;
        }

        $certificate->update([
            'status' => Certificate::STATUS_ISSUED,
            'admin_approved_by' => $admin->id,
            'admin_approved_at' => now(),
            'approved_by' => $admin->id,
            'approved_at' => now(),
            'issue_date' => now(),
        ]);

        // Generate the PDF
        $this->generatePdf($certificate);

        // Send email notification
        $this->sendCertificateEmail($certificate);

        Log::info("Certificate {$certificate->id} approved by admin {$admin->id} and issued");

        return true;
    }

    /**
     * Send certificate email notification to user.
     */
    public function sendCertificateEmail(Certificate $certificate, bool $force = false): bool
    {
        try {
            $certificate->load(['user', 'module', 'course']);

            if (!$certificate->user || !$certificate->user->email) {
                Log::warning("Cannot send certificate email - user has no email", [
                    'certificate_id' => $certificate->id,
                ]);
                return false;
            }

            // Check if already sent (unless forced)
            if (!$force && ($certificate->metadata['email_sent'] ?? false)) {
                Log::info("Certificate email already sent", ['certificate_id' => $certificate->id]);
                return true;
            }

            Mail::to($certificate->user->email)->send(new CertificateIssued($certificate));

            // Update metadata to track that email was sent
            $certificate->update([
                'metadata' => array_merge($certificate->metadata ?? [], [
                    'email_sent' => true,
                    'email_sent_at' => now()->toDateTimeString(),
                ]),
            ]);

            Log::info("Certificate email sent", [
                'certificate_id' => $certificate->id,
                'user_email' => $certificate->user->email,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error("Failed to send certificate email", [
                'certificate_id' => $certificate->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Reject a certificate request.
     */
    public function rejectCertificate(Certificate $certificate, User $rejectedBy, string $reason = null): bool
    {
        $certificate->update([
            'status' => Certificate::STATUS_REJECTED,
            'rejection_reason' => $reason,
            'metadata' => array_merge($certificate->metadata ?? [], [
                'rejected_at' => now()->toDateTimeString(),
                'rejected_by' => $rejectedBy->id,
                'rejection_reason' => $reason,
            ]),
        ]);

        Log::info("Certificate {$certificate->id} rejected by user {$rejectedBy->id}");

        return true;
    }

    /**
     * Manually release/issue a certificate (bypasses approval workflow - for testing/admin use).
     */
    public function manualRelease(User $user, Module $module, User $issuedBy, array $metadata = []): Certificate
    {
        // Check if certificate already exists and is issued
        $existing = Certificate::where('user_id', $user->id)
            ->where('module_id', $module->id)
            ->where('status', Certificate::STATUS_ISSUED)
            ->first();

        if ($existing) {
            return $existing;
        }

        $course = $module->course;
        $certificateNumber = Certificate::generateCertificateNumber();
        $template = $course->certificate_template ?? 'tesda';

        // Create certificate directly as issued
        $certificate = Certificate::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'module_id' => $module->id,
            'certificate_number' => $certificateNumber,
            'title' => "Certificate of Completion - {$module->module_title}",
            'description' => "This certifies that {$user->full_name} has successfully completed the {$module->module_title} module.",
            'issue_date' => now(),
            'status' => Certificate::STATUS_ISSUED,
            'template_used' => $template,
            'requested_at' => now(),
            'instructor_approved_by' => $issuedBy->id,
            'instructor_approved_at' => now(),
            'admin_approved_by' => $issuedBy->id,
            'admin_approved_at' => now(),
            'approved_by' => $issuedBy->id,
            'approved_at' => now(),
            'metadata' => array_merge([
                'completion_date' => now()->toDateString(),
                'course_name' => $course->course_name,
                'module_name' => $module->module_title,
                'student_name' => $user->full_name,
                'manual_release' => true,
                'issued_by' => $issuedBy->full_name,
            ], $metadata),
        ]);

        // Generate the PDF
        $this->generatePdf($certificate);

        // Send email notification
        $this->sendCertificateEmail($certificate);

        Log::info("Certificate manually released for user {$user->id}, module {$module->id} by {$issuedBy->id}", [
            'certificate_id' => $certificate->id,
        ]);

        return $certificate;
    }

    /**
     * Get pending certificates for instructor approval.
     */
    public function getPendingForInstructor(): \Illuminate\Database\Eloquent\Collection
    {
        return Certificate::pendingInstructor()
            ->with(['user', 'course', 'module'])
            ->orderBy('created_at', 'asc')
            ->get();
    }

    /**
     * Get pending certificates for admin approval.
     */
    public function getPendingForAdmin(): \Illuminate\Database\Eloquent\Collection
    {
        return Certificate::pendingAdmin()
            ->with(['user', 'course', 'module', 'instructorApprovedBy'])
            ->orderBy('instructor_approved_at', 'asc')
            ->get();
    }

    /**
     * Get all certificates for a module.
     */
    public function getModuleCertificates(Module $module): \Illuminate\Database\Eloquent\Collection
    {
        return Certificate::forModule($module->id)
            ->with(['user'])
            ->orderByDesc('issue_date')
            ->get();
    }
}

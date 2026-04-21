<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Models\Course;
use App\Models\Module;
use App\Models\User;
use App\Services\CertificateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class CertificateController extends Controller
{
    protected CertificateService $certificateService;

    public function __construct(CertificateService $certificateService)
    {
        $this->certificateService = $certificateService;
    }

    public function index()
    {
        $certificates = $this->certificateService->getUserCertificates(auth()->user());

        return view('certificates.index', compact('certificates'));
    }

    public function show(Certificate $certificate)
    {
        $this->authorize('view', $certificate);

        return view('certificates.show', compact('certificate'));
    }

    public function download(Certificate $certificate)
    {
        try {
            $this->authorize('view', $certificate);

            // Delete old PDF if exists
            if ($certificate->pdf_path && Storage::disk('public')->exists($certificate->pdf_path)) {
                Storage::disk('public')->delete($certificate->pdf_path);
            }

            // Generate fresh PDF
            $this->generatePdf($certificate);

            // Force browser to download fresh copy with unique filename
            $filename = 'certificate_' . $certificate->id . '_' . time() . '.pdf';

            return response()->download(
                Storage::disk('public')->path($certificate->pdf_path),
                $filename,
                ['Content-Type' => 'application/pdf']
            );
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('CertificateController::download failed', [
                'error' => $e->getMessage(),
                'user' => auth()->id(),
                'certificate_id' => $certificate->id,
            ]);
            return back()->with('error', 'Download failed. Please try again.');
        }
    }

    public function verify(Request $request)
    {
        try {
            $request->validate([
                'certificate_number' => 'required|string|max:100|regex:/^[A-Z0-9\-]+$/i',
            ]);

            $certificate = $this->certificateService->verifyCertificate(
                $request->certificate_number
            );

            if (!$certificate) {
                return response()->json([
                    'valid' => false,
                    'message' => 'Certificate not found or invalid.',
                ], 404);
            }

            return response()->json([
                'valid' => true,
                'certificate' => [
                    'number' => $certificate->certificate_number,
                    'title' => $certificate->title,
                    'recipient' => $certificate->user->full_name,
                    'course' => $certificate->course->course_name,
                    'issue_date' => $certificate->issue_date->format('F d, Y'),
                    'status' => $certificate->status,
                ],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('CertificateController::verify failed', [
                'error' => $e->getMessage(),
                'user' => auth()->id(),
            ]);
            return response()->json(['error' => 'Verification failed. Please try again.'], 500);
        }
    }

    public function generate(Course $course)
    {
        try {
            $user = auth()->user();

            // Check if course is complete
            if (!$this->certificateService->checkCourseCompletion($user, $course)) {
                return back()->with('error', 'You must complete all modules before receiving a certificate.');
            }

            $certificate = DB::transaction(function () use ($user, $course) {
                // Lock to prevent duplicate certificate creation from concurrent requests
                $existing = Certificate::where('user_id', $user->id)
                    ->where('course_id', $course->id)
                    ->where('status', 'issued')
                    ->lockForUpdate()
                    ->first();

                if ($existing) {
                    return $existing;
                }

                return $this->certificateService->generateCertificate($user, $course);
            });

            return redirect()->route('certificates.show', $certificate)
                ->with('success', 'Certificate generated successfully!');
        } catch (\Exception $e) {
            Log::error('CertificateController::generate failed', [
                'error' => $e->getMessage(),
                'user' => auth()->id(),
                'course_id' => $course->id,
            ]);
            return back()->with('error', 'Certificate generation failed. Please try again.');
        }
    }

    // Admin methods
    public function adminIndex()
    {
        $certificates = Certificate::with(['user', 'course'])
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('admin.certificates.index', compact('certificates'));
    }

    public function revoke(Certificate $certificate, Request $request)
    {
        try {
            $this->certificateService->revokeCertificate(
                $certificate,
                $request->get('reason')
            );

            return back()->with('success', 'Certificate revoked successfully.');
        } catch (\Exception $e) {
            Log::error('CertificateController::revoke failed', [
                'error' => $e->getMessage(),
                'user' => auth()->id(),
                'certificate_id' => $certificate->id,
            ]);
            return back()->with('error', 'Certificate revocation failed. Please try again.');
        }
    }

    /**
     * Show pending certificates for approval.
     */
    public function pending()
    {
        $pendingInstructor = $this->certificateService->getPendingForInstructor();
        $pendingAdmin = $this->certificateService->getPendingForAdmin();

        return view('admin.certificates.pending', compact('pendingInstructor', 'pendingAdmin'));
    }

    /**
     * Instructor approves a certificate.
     */
    public function instructorApprove(Certificate $certificate)
    {
        try {
            $result = $this->certificateService->instructorApprove($certificate, auth()->user());

            if ($result) {
                return back()->with('success', 'Certificate approved! Awaiting admin approval.');
            }

            return back()->with('error', 'Certificate cannot be approved at this stage.');
        } catch (\Exception $e) {
            Log::error('CertificateController::instructorApprove failed', [
                'error' => $e->getMessage(),
                'user' => auth()->id(),
                'certificate_id' => $certificate->id,
            ]);
            return back()->with('error', 'Approval failed. Please try again.');
        }
    }

    /**
     * Admin approves a certificate (final approval).
     */
    public function adminApprove(Certificate $certificate)
    {
        try {
            $result = $this->certificateService->adminApprove($certificate, auth()->user());

            if ($result) {
                return back()->with('success', 'Certificate issued successfully!');
            }

            return back()->with('error', 'Certificate cannot be approved at this stage.');
        } catch (\Exception $e) {
            Log::error('CertificateController::adminApprove failed', [
                'error' => $e->getMessage(),
                'user' => auth()->id(),
                'certificate_id' => $certificate->id,
            ]);
            return back()->with('error', 'Approval failed. Please try again.');
        }
    }

    /**
     * Reject a certificate request.
     */
    public function reject(Certificate $certificate, Request $request)
    {
        try {
            $request->validate([
                'reason' => 'nullable|string|max:500',
            ]);

            $this->certificateService->rejectCertificate(
                $certificate,
                auth()->user(),
                $request->get('reason')
            );

            return back()->with('success', 'Certificate request rejected.');
        } catch (\Exception $e) {
            Log::error('CertificateController::reject failed', [
                'error' => $e->getMessage(),
                'user' => auth()->id(),
                'certificate_id' => $certificate->id,
            ]);
            return back()->with('error', 'Rejection failed. Please try again.');
        }
    }

    /**
     * Show manual release form.
     */
    public function manualReleaseForm()
    {
        $students = User::where('role', 'student')->where('stat', 1)->orderBy('last_name')->get();
        $modules = Module::with('course')->where('is_active', true)->orderBy('module_title')->get();

        return view('admin.certificates.manual-release', compact('students', 'modules'));
    }

    /**
     * Manually release a certificate (bypasses approval workflow).
     */
    public function manualRelease(Request $request)
    {
        try {
            $request->validate([
                'user_id' => 'required|exists:users,id',
                'module_id' => 'required|exists:modules,id',
            ]);

            $user = User::findOrFail($request->user_id);
            $module = Module::findOrFail($request->module_id);

            $certificate = $this->certificateService->manualRelease(
                $user,
                $module,
                auth()->user()
            );

            return redirect()->route('admin.certificates.show', $certificate)
                ->with('success', "Certificate issued to {$user->full_name} for {$module->module_title}!");
        } catch (\Exception $e) {
            Log::error('CertificateController::manualRelease failed', [
                'error' => $e->getMessage(),
                'user' => auth()->id(),
            ]);
            return back()->with('error', 'Manual release failed: ' . $e->getMessage());
        }
    }

    /**
     * Bulk release certificates for testing.
     */
    public function bulkRelease(Request $request)
    {
        try {
            $request->validate([
                'user_id' => 'required|exists:users,id',
            ]);

            $user = User::findOrFail($request->user_id);
            $modules = Module::with('course')->where('is_active', true)->get();
            $issued = 0;

            foreach ($modules as $module) {
                $certificate = $this->certificateService->manualRelease(
                    $user,
                    $module,
                    auth()->user()
                );
                $issued++;
            }

            return back()->with('success', "Issued {$issued} certificates to {$user->full_name}!");
        } catch (\Exception $e) {
            Log::error('CertificateController::bulkRelease failed', [
                'error' => $e->getMessage(),
                'user' => auth()->id(),
            ]);
            return back()->with('error', 'Bulk release failed: ' . $e->getMessage());
        }
    }

    /**
     * Show a certificate (admin view).
     */
    public function adminShow(Certificate $certificate)
    {
        $certificate->load(['user', 'course', 'module', 'instructorApprovedBy', 'adminApprovedBy']);
        return view('admin.certificates.show', compact('certificate'));
    }

    /**
     * Show edit form for a certificate.
     */
    public function edit(Certificate $certificate)
    {
        $certificate->load(['user', 'course', 'module']);
        $templates = $this->certificateService->getAvailableTemplates();
        $users = User::where('role', 'student')->where('stat', 1)->orderBy('last_name')->get();
        $modules = Module::with('course')->where('is_active', true)->orderBy('module_title')->get();

        return view('admin.certificates.edit', compact('certificate', 'templates', 'users', 'modules'));
    }

    /**
     * Update a certificate.
     */
    public function update(Request $request, Certificate $certificate)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string|max:1000',
                'user_id' => 'required|exists:users,id',
                'module_id' => 'required|exists:modules,id',
                'issue_date' => 'nullable|date',
                'template_used' => 'required|string|in:' . implode(',', array_keys($this->certificateService->getAvailableTemplates())),
                'status' => 'required|string|in:pending_instructor,pending_admin,issued,revoked,rejected',
            ]);

            $module = Module::findOrFail($validated['module_id']);

            $certificate->update([
                'title' => $validated['title'],
                'description' => $validated['description'],
                'user_id' => $validated['user_id'],
                'module_id' => $validated['module_id'],
                'course_id' => $module->course_id,
                'issue_date' => $validated['issue_date'] ? \Carbon\Carbon::parse($validated['issue_date']) : $certificate->issue_date,
                'template_used' => $validated['template_used'],
                'status' => $validated['status'],
            ]);

            // Regenerate PDF if issued
            if ($certificate->status === 'issued') {
                $this->certificateService->generatePdf($certificate);
            }

            return redirect()->route('admin.certificates.index')
                ->with('success', 'Certificate updated successfully!');
        } catch (\Exception $e) {
            Log::error('CertificateController::update failed', [
                'error' => $e->getMessage(),
                'certificate_id' => $certificate->id,
            ]);
            return back()->with('error', 'Update failed: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Delete a certificate.
     */
    public function destroy(Certificate $certificate)
    {
        try {
            // Delete PDF file if exists
            if ($certificate->pdf_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($certificate->pdf_path)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($certificate->pdf_path);
            }

            $certificate->delete();

            return redirect()->route('admin.certificates.index')
                ->with('success', 'Certificate deleted successfully!');
        } catch (\Exception $e) {
            Log::error('CertificateController::destroy failed', [
                'error' => $e->getMessage(),
                'certificate_id' => $certificate->id,
            ]);
            return back()->with('error', 'Delete failed: ' . $e->getMessage());
        }
    }

    /**
     * Resend certificate email to user.
     */
    public function resendEmail(Certificate $certificate)
    {
        try {
            if ($certificate->status !== 'issued') {
                return back()->with('error', 'Can only send email for issued certificates.');
            }

            $sent = $this->certificateService->sendCertificateEmail($certificate, true);

            if ($sent) {
                return back()->with('success', 'Certificate email sent successfully!');
            }

            return back()->with('error', 'Failed to send email. Please check the user has a valid email address.');
        } catch (\Exception $e) {
            Log::error('CertificateController::resendEmail failed', [
                'error' => $e->getMessage(),
                'certificate_id' => $certificate->id,
            ]);
            return back()->with('error', 'Failed to send email: ' . $e->getMessage());
        }
    }

    /**
     * Issue a certificate to a user from user management page.
     */
    public function issueCertificateForUser(Request $request, User $user)
    {
        try {
            $request->validate([
                'module_id' => 'required|exists:modules,id',
            ]);

            $module = Module::findOrFail($request->module_id);

            $certificate = $this->certificateService->manualRelease(
                $user,
                $module,
                auth()->user()
            );

            return back()->with('success', "Certificate issued to {$user->full_name} for {$module->module_title}!");
        } catch (\Exception $e) {
            Log::error('CertificateController::issueCertificateForUser failed', [
                'error' => $e->getMessage(),
                'user' => auth()->id(),
                'target_user' => $user->id,
            ]);
            return back()->with('error', 'Certificate issuance failed: ' . $e->getMessage());
        }
    }

    /**
     * Distribute certificates to all students who completed all modules in any course.
     */
    public function distributeCertificates()
    {
        $courses = Course::where('is_active', true)->with('modules')->get();
        $issued = 0;

        foreach ($courses as $course) {
            $activeModules = $course->modules->where('is_active', true);
            if ($activeModules->isEmpty()) {
                continue;
            }

            // Get all active students
            $students = User::where('role', 'student')->where('stat', 1)->get();

            foreach ($students as $student) {
                // Skip if already has certificate for this course
                $existing = Certificate::where('user_id', $student->id)
                    ->where('course_id', $course->id)
                    ->whereIn('status', ['issued', 'pending', 'pending_instructor', 'pending_admin'])
                    ->exists();

                if ($existing) {
                    continue;
                }

                // Check if student completed all modules
                if ($this->certificateService->checkCourseCompletion($student, $course)) {
                    try {
                        $this->certificateService->generateCertificate($student, $course, [
                            'auto_distributed' => true,
                            'distributed_by' => auth()->id(),
                        ]);
                        $issued++;
                    } catch (\Exception $e) {
                        Log::error("Failed to distribute certificate to user {$student->id} for course {$course->id}", [
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            }
        }

        if ($issued > 0) {
            return back()->with('success', "Successfully distributed {$issued} certificate(s) to qualifying students.");
        }

        return back()->with('info', 'No new certificates to distribute. All qualifying students already have certificates.');
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
            \Log::warning("Template '{$template}' not found, falling back to 'default'");
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

    public function downloadPdf(Certificate $certificate)
    {
        // Delete old PDF if exists (ensures fresh generation)
        if ($certificate->pdf_path && Storage::disk('public')->exists($certificate->pdf_path)) {
            Storage::disk('public')->delete($certificate->pdf_path);
        }

        // Force regenerate with latest template & CSS
        $this->generatePdf($certificate);

        // Add a timestamp to the filename to prevent browser caching
        $filename = 'certificate_' . $certificate->id . '_' . time() . '.pdf';

        return response()->download(
            Storage::disk('public')->path($certificate->pdf_path),
            $filename,
            ['Content-Type' => 'application/pdf']
        );
    }
}

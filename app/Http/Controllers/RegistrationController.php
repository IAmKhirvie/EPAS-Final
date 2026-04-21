<?php

namespace App\Http\Controllers;

use App\Models\Registration;
use App\Services\DashboardStatisticsService;
use App\Services\RegistrationService;
use App\Services\PHPMailerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class RegistrationController extends Controller
{
    protected RegistrationService $registrationService;

    public function __construct()
    {
        $this->registrationService = new RegistrationService(new PHPMailerService());
    }

    /**
     * Display registration management page (Admin/Instructor)
     */
    public function index()
    {
        return view('admin.registrations.index');
    }

    /**
     * Show a single registration
     */
    public function show(Registration $registration)
    {
        return view('admin.registrations.show', compact('registration'));
    }

    /**
     * Approve a registration
     */
    public function approve(Registration $registration)
    {
        try {
            $result = $this->registrationService->approveRegistration($registration, Auth::id());

            if ($result['success']) {
                app(DashboardStatisticsService::class)->clearRegistrationCache();
                return redirect()->route('admin.registrations.index')
                    ->with('success', $result['message']);
            }

            return redirect()->back()
                ->withErrors(['error' => $result['message']]);
        } catch (\Exception $e) {
            Log::error('Registration approval failed', ['error' => $e->getMessage(), 'registration_id' => $registration->id]);

            return redirect()->back()->with('error', 'Failed to approve registration. Please try again.');
        }
    }

    /**
     * Reject a registration
     */
    public function reject(Request $request, Registration $registration)
    {
        $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        try {
            $result = $this->registrationService->rejectRegistration(
                $registration,
                Auth::id(),
                $request->reason
            );

            if ($result['success']) {
                app(DashboardStatisticsService::class)->clearRegistrationCache();
                return redirect()->route('admin.registrations.index')
                    ->with('success', $result['message']);
            }

            return redirect()->back()
                ->withErrors(['error' => $result['message']]);
        } catch (\Exception $e) {
            Log::error('Registration rejection failed', ['error' => $e->getMessage(), 'registration_id' => $registration->id]);

            return redirect()->back()->with('error', 'Failed to reject registration. Please try again.');
        }
    }

    /**
     * Bulk approve registrations
     */
    public function bulkApprove(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:registrations,id',
        ]);

        try {
            $approved = 0;
            $failed = 0;

            foreach ($request->ids as $id) {
                $registration = Registration::find($id);
                if ($registration && $registration->status !== Registration::STATUS_TRANSFERRED) {
                    $result = $this->registrationService->approveRegistration($registration, Auth::id());
                    if ($result['success']) {
                        $approved++;
                    } else {
                        $failed++;
                    }
                }
            }

            return redirect()->back()
                ->with('success', "Approved {$approved} registrations." . ($failed > 0 ? " {$failed} failed." : ''));
        } catch (\Exception $e) {
            Log::error('Bulk registration approval failed', ['error' => $e->getMessage()]);

            return redirect()->back()->with('error', 'Failed to approve registrations. Please try again.');
        }
    }

    /**
     * Bulk reject registrations
     */
    public function bulkReject(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:registrations,id',
            'reason' => 'nullable|string|max:500',
        ]);

        try {
            $rejected = 0;

            foreach ($request->ids as $id) {
                $registration = Registration::find($id);
                if ($registration && !in_array($registration->status, [Registration::STATUS_TRANSFERRED, Registration::STATUS_REJECTED])) {
                    $this->registrationService->rejectRegistration($registration, Auth::id(), $request->reason);
                    $rejected++;
                }
            }

            return redirect()->back()
                ->with('success', "Rejected {$rejected} registrations.");
        } catch (\Exception $e) {
            Log::error('Bulk registration rejection failed', ['error' => $e->getMessage()]);

            return redirect()->back()->with('error', 'Failed to reject registrations. Please try again.');
        }
    }

    /**
     * Resend verification email (Admin action)
     */
    public function resendVerification(Registration $registration)
    {
        if ($registration->isEmailVerified()) {
            return redirect()->back()
                ->withErrors(['error' => 'Email is already verified.']);
        }

        $sent = $this->registrationService->resendVerificationEmail($registration);

        if ($sent) {
            return redirect()->back()
                ->with('success', 'Verification email sent!');
        }

        return redirect()->back()
            ->withErrors(['error' => 'Failed to send verification email.']);
    }

    /**
     * Delete a rejected registration
     */
    public function destroy(Registration $registration)
    {
        if ($registration->status !== Registration::STATUS_REJECTED) {
            return redirect()->back()
                ->withErrors(['error' => 'Only rejected registrations can be deleted.']);
        }

        try {
            $registration->delete();

            return redirect()->back()
                ->with('success', 'Registration deleted.');
        } catch (\Exception $e) {
            Log::error('Registration deletion failed', ['error' => $e->getMessage(), 'registration_id' => $registration->id]);

            return redirect()->back()->with('error', 'Failed to delete registration. Please try again.');
        }
    }
}

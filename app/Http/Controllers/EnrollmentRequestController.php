<?php

namespace App\Http\Controllers;

use App\Constants\Roles;
use App\Models\EnrollmentRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\StoreEnrollmentRequest;

class EnrollmentRequestController extends Controller
{
    /**
     * Display enrollment requests management page
     * - Admins: See all requests
     * - Instructors: See their own requests
     */
    public function index()
    {
        return view('enrollment-requests.index');
    }

    /**
     * Show form to create enrollment request (Instructor only)
     */
    public function create()
    {
        $user = Auth::user();

        if ($user->role !== Roles::INSTRUCTOR) {
            abort(403, 'Only instructors can create enrollment requests.');
        }

        if (!$user->advisory_section) {
            return redirect()->route('enrollment-requests.index')
                ->with('error', 'You must be assigned to a section before requesting student enrollments.');
        }

        // Get unassigned students (no section) or students from other sections
        $unassignedStudents = User::where('role', Roles::STUDENT)
            ->where('stat', 1)
            ->where(function ($q) use ($user) {
                $q->whereNull('section')
                  ->orWhere('section', '!=', $user->advisory_section);
            })
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        return view('enrollment-requests.create', compact('user', 'unassignedStudents'));
    }

    /**
     * Store a new enrollment request (Instructor only)
     */
    public function store(StoreEnrollmentRequest $request)
    {
        $user = Auth::user();

        if ($user->role !== Roles::INSTRUCTOR) {
            abort(403, 'Only instructors can create enrollment requests.');
        }

        if (!$user->advisory_section) {
            return redirect()->back()->with('error', 'You must be assigned to a section first.');
        }

        $validated = $request->validated();

        // Verify the student exists and is a student
        $student = User::findOrFail($validated['student_id']);
        if ($student->role !== Roles::STUDENT) {
            return redirect()->back()->with('error', 'Selected user is not a student.');
        }

        // Check if there's already a pending request for this student
        $existingRequest = EnrollmentRequest::where('student_id', $student->id)
            ->where('section', $user->advisory_section)
            ->pending()
            ->first();

        if ($existingRequest) {
            return redirect()->back()->with('error', 'There is already a pending request for this student.');
        }

        // Check if student is already in this section
        if ($student->section === $user->advisory_section) {
            return redirect()->back()->with('error', 'This student is already enrolled in your section.');
        }

        try {
            EnrollmentRequest::create([
                'instructor_id' => $user->id,
                'student_id' => $student->id,
                'student_name' => $student->full_name,
                'student_email' => $student->email,
                'section' => $user->advisory_section,
                'notes' => $validated['notes'],
                'status' => 'pending',
            ]);

            return redirect()->route('enrollment-requests.index')
                ->with('success', 'Enrollment request submitted successfully. Waiting for admin approval.');
        } catch (\Exception $e) {
            Log::error('Enrollment request creation failed', ['error' => $e->getMessage(), 'user_id' => Auth::id()]);

            return back()->with('error', 'Failed to submit enrollment request. Please try again.');
        }
    }

    /**
     * Approve an enrollment request (Admin only)
     */
    public function approve(Request $request, EnrollmentRequest $enrollmentRequest)
    {
        if (Auth::user()->role !== Roles::ADMIN) {
            abort(403, 'Only administrators can approve enrollment requests.');
        }

        if (!$enrollmentRequest->isPending()) {
            return redirect()->back()->with('error', 'This request has already been processed.');
        }

        $request->validate([
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $adminNotes = $request->input('admin_notes');

        try {
            $enrollmentRequest->approve(Auth::user(), $adminNotes);

            return redirect()->back()
                ->with('success', "Student {$enrollmentRequest->student_display_name} has been enrolled in {$enrollmentRequest->section}.");
        } catch (\Exception $e) {
            Log::error('Enrollment request approval failed', ['error' => $e->getMessage(), 'enrollment_request_id' => $enrollmentRequest->id]);

            return redirect()->back()->with('error', 'Failed to approve enrollment request. Please try again.');
        }
    }

    /**
     * Reject an enrollment request (Admin only)
     */
    public function reject(Request $request, EnrollmentRequest $enrollmentRequest)
    {
        if (Auth::user()->role !== Roles::ADMIN) {
            abort(403, 'Only administrators can reject enrollment requests.');
        }

        if (!$enrollmentRequest->isPending()) {
            return redirect()->back()->with('error', 'This request has already been processed.');
        }

        $request->validate([
            'admin_notes' => 'required|string|max:500',
        ]);

        try {
            $enrollmentRequest->reject(Auth::user(), $request->admin_notes);

            return redirect()->back()
                ->with('success', 'Enrollment request has been rejected.');
        } catch (\Exception $e) {
            Log::error('Enrollment request rejection failed', ['error' => $e->getMessage(), 'enrollment_request_id' => $enrollmentRequest->id]);

            return redirect()->back()->with('error', 'Failed to reject enrollment request. Please try again.');
        }
    }

    /**
     * Cancel an enrollment request (Instructor only - their own pending requests)
     */
    public function cancel(EnrollmentRequest $enrollmentRequest)
    {
        $user = Auth::user();

        if ($enrollmentRequest->instructor_id !== $user->id) {
            abort(403, 'You can only cancel your own requests.');
        }

        if (!$enrollmentRequest->isPending()) {
            return redirect()->back()->with('error', 'Only pending requests can be cancelled.');
        }

        try {
            $enrollmentRequest->delete();

            return redirect()->back()->with('success', 'Enrollment request cancelled.');
        } catch (\Exception $e) {
            Log::error('Enrollment request cancellation failed', ['error' => $e->getMessage(), 'enrollment_request_id' => $enrollmentRequest->id]);

            return redirect()->back()->with('error', 'Failed to cancel enrollment request. Please try again.');
        }
    }

    /**
     * Get pending requests count for notifications (API)
     */
    public function getPendingCount()
    {
        $user = Auth::user();

        if ($user->role === Roles::ADMIN) {
            $count = EnrollmentRequest::pending()->count();
        } else {
            $count = 0;
        }

        return response()->json(['count' => $count]);
    }
}

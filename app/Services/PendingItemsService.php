<?php

namespace App\Services;

use App\Constants\Roles;
use App\Models\EnrollmentRequest;
use App\Models\Notification;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Support\Collection;

class PendingItemsService
{
    /**
     * Batch-query pending counts for a collection of user IDs.
     * Returns [userId => ['registrations' => n, 'enrollments' => n, 'unread_notifications' => n]]
     */
    public function getPendingCountsForUsers(Collection $userIds): array
    {
        if ($userIds->isEmpty()) {
            return [];
        }

        $ids = $userIds->toArray();
        $counts = [];

        foreach ($ids as $id) {
            $counts[$id] = ['registrations' => 0, 'enrollments' => 0, 'unread_notifications' => 0];
        }

        // Pending registrations — relevant for admins/instructors (global count, not per-user)
        $pendingRegistrations = Registration::whereIn('status', [
            Registration::STATUS_PENDING,
            Registration::STATUS_EMAIL_VERIFIED,
        ])->count();

        // Pending enrollment requests — scoped per instructor or global for admin
        $pendingEnrollmentsByInstructor = EnrollmentRequest::where('status', 'pending')
            ->whereIn('instructor_id', $ids)
            ->selectRaw('instructor_id, COUNT(*) as cnt')
            ->groupBy('instructor_id')
            ->pluck('cnt', 'instructor_id');

        $globalPendingEnrollments = EnrollmentRequest::where('status', 'pending')->count();

        // Unread notifications per user
        $unreadByUser = Notification::whereIn('user_id', $ids)
            ->whereNull('read_at')
            ->selectRaw('user_id, COUNT(*) as cnt')
            ->groupBy('user_id')
            ->pluck('cnt', 'user_id');

        // Pending account approvals (users with stat=0) — relevant for admin view
        $pendingApprovals = User::where('stat', 0)->count();

        foreach ($ids as $id) {
            $user = null;
            // We need the user role to determine what counts apply
            // Since we batch-load, grab roles in one query
            $counts[$id]['unread_notifications'] = (int) ($unreadByUser[$id] ?? 0);
        }

        // Get user roles in batch
        $userRoles = User::whereIn('id', $ids)->pluck('role', 'id');

        foreach ($ids as $id) {
            $role = $userRoles[$id] ?? null;

            if ($role === Roles::ADMIN) {
                $counts[$id]['registrations'] = $pendingRegistrations;
                $counts[$id]['enrollments'] = $globalPendingEnrollments;
            } elseif ($role === Roles::INSTRUCTOR) {
                $counts[$id]['registrations'] = $pendingRegistrations;
                $counts[$id]['enrollments'] = (int) ($pendingEnrollmentsByInstructor[$id] ?? 0);
            }
            // Students don't have registration/enrollment management counts

            $counts[$id]['unread_notifications'] = (int) ($unreadByUser[$id] ?? 0);
        }

        return $counts;
    }

    /**
     * Get detailed pending items for a specific user (for edit page / detail view).
     */
    public function getPendingItemsForUser(int $userId): array
    {
        $user = User::find($userId);
        if (!$user) {
            return [
                'pending_registrations' => collect(),
                'pending_enrollments' => collect(),
                'unread_notifications' => collect(),
                'pending_approval' => false,
            ];
        }

        $result = [
            'pending_registrations' => collect(),
            'pending_enrollments' => collect(),
            'unread_notifications' => Notification::where('user_id', $userId)
                ->whereNull('read_at')
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get(),
            'pending_approval' => (int) $user->stat === 0,
        ];

        if ($user->role === Roles::ADMIN) {
            $result['pending_registrations'] = Registration::whereIn('status', [
                Registration::STATUS_PENDING,
                Registration::STATUS_EMAIL_VERIFIED,
            ])->orderBy('created_at', 'desc')->limit(10)->get();

            $result['pending_enrollments'] = EnrollmentRequest::where('status', 'pending')
                ->with(['instructor', 'student'])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();
        } elseif ($user->role === Roles::INSTRUCTOR) {
            $result['pending_registrations'] = Registration::whereIn('status', [
                Registration::STATUS_PENDING,
                Registration::STATUS_EMAIL_VERIFIED,
            ])->orderBy('created_at', 'desc')->limit(10)->get();

            $result['pending_enrollments'] = EnrollmentRequest::where('status', 'pending')
                ->where('instructor_id', $userId)
                ->with(['student'])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();
        }

        return $result;
    }

    /**
     * Get the total pending count for a user (sum of all pending items).
     */
    public function getTotalPendingCount(array $pendingCounts): int
    {
        return ($pendingCounts['registrations'] ?? 0)
             + ($pendingCounts['enrollments'] ?? 0)
             + ($pendingCounts['unread_notifications'] ?? 0);
    }
}

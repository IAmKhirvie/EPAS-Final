<?php

namespace App\Policies;

use App\Constants\Roles;
use App\Models\Announcement;
use App\Models\User;

/**
 * Authorization policy for Announcement model.
 *
 * - Admin: full access.
 * - Instructor: can create announcements and update/delete their own.
 * - Student: can view announcements and comment on them.
 */
class AnnouncementPolicy
{
    /**
     * Determine whether the user can view any announcements.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the announcement.
     * Admin and instructor can view any announcement.
     * Other users can only view if target_roles includes their role.
     */
    public function view(User $user, Announcement $announcement): bool
    {
        if (in_array($user->role, [Roles::ADMIN, Roles::INSTRUCTOR])) {
            return true;
        }

        if (!$announcement->target_roles || $announcement->target_roles === 'all') {
            return true;
        }

        return in_array($user->role, explode(',', $announcement->target_roles));
    }

    /**
     * Determine whether the user can create announcements.
     * Admin or instructor can create.
     */
    public function create(User $user): bool
    {
        return in_array($user->role, [Roles::ADMIN, Roles::INSTRUCTOR]);
    }

    /**
     * Determine whether the user can update the announcement.
     * Admin can update any. Instructor can update their own.
     */
    public function update(User $user, Announcement $announcement): bool
    {
        if ($user->role === Roles::ADMIN) {
            return true;
        }

        return $user->role === Roles::INSTRUCTOR
            && $announcement->user_id === $user->id;
    }

    /**
     * Determine whether the user can delete the announcement.
     * Admin can delete any. Instructor can delete their own.
     */
    public function delete(User $user, Announcement $announcement): bool
    {
        if ($user->role === Roles::ADMIN) {
            return true;
        }

        return $user->role === Roles::INSTRUCTOR
            && $announcement->user_id === $user->id;
    }

    /**
     * Determine whether the user can comment on the announcement.
     * All authenticated users can comment.
     */
    public function comment(User $user, Announcement $announcement): bool
    {
        return true;
    }

    /**
     * Determine whether the user can restore the announcement.
     */
    public function restore(User $user, Announcement $announcement): bool
    {
        return $user->role === Roles::ADMIN;
    }

    /**
     * Determine whether the user can permanently delete the announcement.
     */
    public function forceDelete(User $user, Announcement $announcement): bool
    {
        return $user->role === Roles::ADMIN;
    }
}

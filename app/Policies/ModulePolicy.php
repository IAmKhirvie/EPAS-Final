<?php

namespace App\Policies;

use App\Constants\Roles;
use App\Models\Module;
use App\Models\User;

/**
 * Authorization policy for Module model.
 *
 * - Admin: full access.
 * - Instructor: can create, update, and delete modules belonging to their courses.
 * - Student: can view modules only.
 */
class ModulePolicy
{
    /**
     * Determine whether the user can view any modules.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the module.
     * Admin and instructor can view any module (including inactive).
     * Students can only view active modules.
     */
    public function view(User $user, Module $module): bool
    {
        if (in_array($user->role, [Roles::ADMIN, Roles::INSTRUCTOR])) {
            return true;
        }

        return $module->is_active;
    }

    /**
     * Determine whether the user can create modules.
     * Admin or instructor (instructor must own the parent course).
     */
    public function create(User $user): bool
    {
        return in_array($user->role, [Roles::ADMIN, Roles::INSTRUCTOR]);
    }

    /**
     * Determine whether the user can update the module.
     * Admin or the instructor assigned to the module's course.
     */
    public function update(User $user, Module $module): bool
    {
        if ($user->role === Roles::ADMIN) {
            return true;
        }

        return $user->role === Roles::INSTRUCTOR
            && $module->course
            && $module->course->instructor_id === $user->id;
    }

    /**
     * Determine whether the user can delete the module.
     * Admin or the instructor assigned to the module's course.
     */
    public function delete(User $user, Module $module): bool
    {
        if ($user->role === Roles::ADMIN) {
            return true;
        }

        return $user->role === Roles::INSTRUCTOR
            && $module->course
            && $module->course->instructor_id === $user->id;
    }

    /**
     * Determine whether the user can restore the module.
     */
    public function restore(User $user, Module $module): bool
    {
        if ($user->role === Roles::ADMIN) {
            return true;
        }

        return $user->role === Roles::INSTRUCTOR
            && $module->course
            && $module->course->instructor_id === $user->id;
    }

    /**
     * Determine whether the user can permanently delete the module.
     */
    public function forceDelete(User $user, Module $module): bool
    {
        return $user->role === Roles::ADMIN;
    }
}

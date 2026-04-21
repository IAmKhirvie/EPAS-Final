<?php

namespace App\Policies;

use App\Constants\Roles;
use App\Models\Course;
use App\Models\User;

/**
 * Authorization policy for Course model.
 *
 * - Admin: full access (create, view, update, delete).
 * - Instructor: can update courses they are assigned to; can view all.
 * - Student: can view all active courses.
 */
class CoursePolicy
{
    /**
     * Determine whether the user can view any courses.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the course.
     * Admin and instructor can view any course (including inactive).
     * Students can only view active courses.
     */
    public function view(User $user, Course $course): bool
    {
        if (in_array($user->role, [Roles::ADMIN, Roles::INSTRUCTOR])) {
            return true;
        }

        if (!$course->is_active) {
            return false;
        }

        // Check section targeting — null means all sections can access
        if ($course->target_sections && $user->section) {
            $sections = array_map('trim', explode(',', $course->target_sections));
            return in_array($user->section, $sections);
        }

        return true;
    }

    /**
     * Determine whether the user can create courses.
     * Admins and instructors can create courses.
     */
    public function create(User $user): bool
    {
        return in_array($user->role, [Roles::ADMIN, Roles::INSTRUCTOR]);
    }

    /**
     * Determine whether the user can update the course.
     * Admin or the assigned instructor can update.
     */
    public function update(User $user, Course $course): bool
    {
        if ($user->role === Roles::ADMIN) {
            return true;
        }

        return $user->role === Roles::INSTRUCTOR
            && $course->instructor_id === $user->id;
    }

    /**
     * Determine whether the user can delete the course.
     * Only admins can delete courses.
     */
    public function delete(User $user, Course $course): bool
    {
        return $user->role === Roles::ADMIN;
    }

    /**
     * Determine whether the user can restore the course.
     */
    public function restore(User $user, Course $course): bool
    {
        return $user->role === Roles::ADMIN;
    }

    /**
     * Determine whether the user can permanently delete the course.
     */
    public function forceDelete(User $user, Course $course): bool
    {
        return $user->role === Roles::ADMIN;
    }
}

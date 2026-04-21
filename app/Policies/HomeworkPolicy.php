<?php

namespace App\Policies;

use App\Constants\Roles;
use App\Models\Homework;
use App\Models\User;

/**
 * Authorization policy for Homework model.
 *
 * Homework belongs to InformationSheet -> Module -> Course.
 * The course's instructor_id determines instructor ownership.
 *
 * - Admin: full access.
 * - Instructor: can create, update, and delete homework for their courses.
 * - Student: can view homework and submit work.
 */
class HomeworkPolicy
{
    /**
     * Determine whether the user can view any homework.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the homework.
     */
    public function view(User $user, Homework $homework): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create homework.
     * Admin or instructor.
     */
    public function create(User $user): bool
    {
        return in_array($user->role, [Roles::ADMIN, Roles::INSTRUCTOR]);
    }

    /**
     * Determine whether the user can update the homework.
     * Admin or the instructor assigned to the homework's course.
     */
    public function update(User $user, Homework $homework): bool
    {
        if ($user->role === Roles::ADMIN) {
            return true;
        }

        return $user->role === Roles::INSTRUCTOR
            && $this->isHomeworkInstructor($user, $homework);
    }

    /**
     * Determine whether the user can delete the homework.
     * Admin or the instructor assigned to the homework's course.
     */
    public function delete(User $user, Homework $homework): bool
    {
        if ($user->role === Roles::ADMIN) {
            return true;
        }

        return $user->role === Roles::INSTRUCTOR
            && $this->isHomeworkInstructor($user, $homework);
    }

    /**
     * Determine whether the user can submit work for the homework.
     * Only students can submit homework.
     */
    public function submit(User $user, Homework $homework): bool
    {
        return $user->role === Roles::STUDENT;
    }

    /**
     * Determine whether the user can restore the homework.
     */
    public function restore(User $user, Homework $homework): bool
    {
        if ($user->role === Roles::ADMIN) {
            return true;
        }

        return $user->role === Roles::INSTRUCTOR
            && $this->isHomeworkInstructor($user, $homework);
    }

    /**
     * Determine whether the user can permanently delete the homework.
     */
    public function forceDelete(User $user, Homework $homework): bool
    {
        return $user->role === Roles::ADMIN;
    }

    /**
     * Check if the user is the instructor for the course that owns this homework.
     * Traverses: Homework -> InformationSheet -> Module -> Course.
     */
    private function isHomeworkInstructor(User $user, Homework $homework): bool
    {
        $informationSheet = $homework->informationSheet;

        if (!$informationSheet) {
            return false;
        }

        $module = $informationSheet->module;

        if (!$module) {
            return false;
        }

        $course = $module->course;

        if (!$course) {
            return false;
        }

        return $course->instructor_id === $user->id;
    }
}

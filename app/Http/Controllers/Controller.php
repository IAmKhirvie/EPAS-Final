<?php

namespace App\Http\Controllers;

use App\Constants\Roles;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;

/**
 * Base controller for the JOMS LMS application.
 *
 * Provides common authorization methods used across controllers.
 */
abstract class Controller
{
    use AuthorizesRequests;
    /**
     * Check if the current user is an admin.
     *
     * @return bool
     */
    protected function isAdmin(): bool
    {
        return Auth::check() && Auth::user()->role === Roles::ADMIN;
    }

    /**
     * Check if the current user is an instructor.
     *
     * @return bool
     */
    protected function isInstructor(): bool
    {
        return Auth::check() && Auth::user()->role === Roles::INSTRUCTOR;
    }

    /**
     * Check if the current user is a student.
     *
     * @return bool
     */
    protected function isStudent(): bool
    {
        return Auth::check() && Auth::user()->role === Roles::STUDENT;
    }

    /**
     * Check if the current user can manage students (admin or instructor).
     *
     * @return bool
     */
    protected function canManageStudents(): bool
    {
        return Auth::check() && Roles::canManageStudents(Auth::user()->role);
    }

    /**
     * Abort with 403 if user is not an admin.
     *
     * @param string $message Custom error message
     * @return void
     */
    protected function authorizeAdmin(string $message = 'Unauthorized. Admin access required.'): void
    {
        if (!$this->isAdmin()) {
            abort(403, $message);
        }
    }

    /**
     * Abort with 403 if user is not an instructor or admin.
     *
     * @param string $message Custom error message
     * @return void
     */
    protected function authorizeInstructor(string $message = 'Unauthorized. Instructor or admin access required.'): void
    {
        if (!$this->canManageStudents()) {
            abort(403, $message);
        }
    }
}

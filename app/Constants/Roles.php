<?php

namespace App\Constants;

/**
 * User role constants for the JOMS LMS.
 *
 * Used throughout the application to check user permissions
 * and avoid hardcoded role strings.
 */
class Roles
{
    /** Administrator with full system access */
    const ADMIN = 'admin';

    /** Instructor who manages courses and evaluates students */
    const INSTRUCTOR = 'instructor';

    /** Student who takes courses and submits work */
    const STUDENT = 'student';

    /**
     * Get all available roles.
     *
     * @return array<string>
     */
    public static function all(): array
    {
        return [
            self::ADMIN,
            self::INSTRUCTOR,
            self::STUDENT,
        ];
    }

    /**
     * Check if a role can manage students.
     *
     * @param string $role
     * @return bool
     */
    public static function canManageStudents(string $role): bool
    {
        return in_array($role, [self::ADMIN, self::INSTRUCTOR]);
    }

    /**
     * Check if a role has admin privileges.
     *
     * @param string $role
     * @return bool
     */
    public static function isAdmin(string $role): bool
    {
        return $role === self::ADMIN;
    }
}

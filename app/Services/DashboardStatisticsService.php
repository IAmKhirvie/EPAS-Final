<?php

namespace App\Services;

use App\Constants\Roles;
use App\Models\Announcement;
use App\Models\InformationSheet;
use App\Models\Module;
use App\Models\User;
use App\Models\UserProgress;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Provides dashboard statistics and data aggregation for all user roles.
 *
 * Extracted from DashboardController to keep controllers thin.
 * Handles student progress, instructor evaluations, and admin-level stats.
 *
 * Pending activities and registrations are delegated to PendingActivitiesService.
 */
class DashboardStatisticsService
{
    public function __construct(private PendingActivitiesService $pendingActivities)
    {
    }

    // =========================================================================
    // PUBLIC METHODS - Statistics
    // =========================================================================

    /**
     * Get statistics for admin/instructor dashboard.
     *
     * @param User $user
     * @return array
     */
    public function getAdminInstructorStats(User $user): array
    {
        return Cache::remember("dashboard_admin_stats_{$user->id}", config('joms.cache.dashboard_stats_ttl', 600), function () use ($user) {
            $isInstructor = $user->role === Roles::INSTRUCTOR;

            $totalStudents = $this->countStudents($user, $isInstructor);

            $totalInstructors = $user->role === Roles::ADMIN
                ? User::where('role', Roles::INSTRUCTOR)->where('stat', 1)->count()
                : 0;

            $totalModules = $this->countModules($user, $isInstructor);
            $ongoingBatches = $this->countSections($user, $isInstructor);

            return [
                'totalStudents' => $totalStudents,
                'totalInstructors' => $totalInstructors,
                'totalModules' => $totalModules,
                'ongoingBatches' => $ongoingBatches,
            ];
        });
    }

    /**
     * Count students visible to the user.
     *
     * @param User $user
     * @param bool $isInstructor
     * @return int
     */
    public function countStudents(User $user, bool $isInstructor): int
    {
        $query = User::where('role', Roles::STUDENT)->where('stat', 1);

        if ($isInstructor) {
            $sections = $user->getAllAccessibleSections();
            if ($sections->isNotEmpty()) {
                $query->whereIn('section', $sections);
            } else {
                return 0;
            }
        }

        return $query->count();
    }

    /**
     * Count modules visible to the user.
     *
     * @param User $user
     * @param bool $isInstructor
     * @return int
     */
    public function countModules(User $user, bool $isInstructor): int
    {
        $query = Module::where('is_active', true);

        if ($isInstructor) {
            $query->whereHas('course', function ($q) use ($user) {
                $q->where('instructor_id', $user->id);
            });
        }

        return $query->count();
    }

    /**
     * Count sections/batches visible to the user.
     *
     * @param User $user
     * @param bool $isInstructor
     * @return int
     */
    public function countSections(User $user, bool $isInstructor): int
    {
        if ($isInstructor) {
            return $user->getAllAccessibleSections()->count();
        }

        return User::where('role', Roles::STUDENT)
            ->whereNotNull('section')
            ->distinct('section')
            ->count('section');
    }

    /**
     * Get progress summary for a student.
     *
     * @param User $user
     * @return array
     */
    public function getProgressSummary(User $user): array
    {
        return Cache::remember("dashboard_progress_{$user->id}", 300, function () use ($user) {
            $completedModules = UserProgress::where('user_id', $user->id)
                ->where('progressable_type', Module::class)
                ->where('status', 'completed')
                ->count();

            $inProgressModules = UserProgress::where('user_id', $user->id)
                ->where('progressable_type', Module::class)
                ->where('status', 'in_progress')
                ->count();

            $totalModules = Module::where('is_active', true)->count();

            $averageScore = UserProgress::where('user_id', $user->id)
                ->whereNotNull('score')
                ->avg('score') ?? 0;

            return [
                'completed_modules' => $completedModules,
                'in_progress_modules' => $inProgressModules,
                'total_modules' => $totalModules,
                'average_score' => round($averageScore, 1)
            ];
        });
    }

    // =========================================================================
    // PUBLIC METHODS - Activities (delegated to PendingActivitiesService)
    // =========================================================================

    /**
     * Get pending activities for a student.
     *
     * Returns unsubmitted self-checks, homeworks, task sheets, and job sheets.
     *
     * @param User $user
     * @return Collection
     */
    public function getPendingActivitiesForStudent(User $user): Collection
    {
        return $this->pendingActivities->getPendingActivitiesForStudent($user);
    }

    /**
     * Get completed activities for a student.
     *
     * @param User $user
     * @return Collection
     */
    public function getCompletedActivitiesForStudent(User $user): Collection
    {
        return $this->pendingActivities->getCompletedActivitiesForStudent($user);
    }

    /**
     * Get recent submissions for instructor review.
     *
     * @param User $user
     * @return Collection
     */
    public function getRecentSubmissionsForInstructor(User $user): Collection
    {
        return $this->pendingActivities->getRecentSubmissionsForInstructor($user);
    }

    /**
     * Get count of pending evaluations for instructor.
     *
     * @param User $user
     * @return int
     */
    public function getPendingEvaluationsCount(User $user): int
    {
        return $this->pendingActivities->getPendingEvaluationsCount($user);
    }

    // =========================================================================
    // PUBLIC METHODS - Utility Helpers
    // =========================================================================

    /**
     * Check if user is admin or instructor.
     *
     * @param User $user
     * @return bool
     */
    public function isAdminOrInstructor(User $user): bool
    {
        return in_array($user->role, [Roles::ADMIN, Roles::INSTRUCTOR]);
    }

    /**
     * Get student IDs visible to an instructor.
     *
     * @param User $user
     * @return Collection
     */
    public function getStudentIdsForInstructor(User $user): Collection
    {
        return $this->pendingActivities->getStudentIdsForInstructor($user);
    }

    /**
     * Get recent announcements for the current user.
     * Filters by target_roles so users only see announcements meant for them.
     *
     * @param int $limit
     * @return Collection
     */
    public function getRecentAnnouncements(int $limit = 3): Collection
    {
        $user = auth()->user();
        $role = $user?->role ?? 'guest';

        return Cache::remember("dashboard_announcements_{$limit}_{$role}", 300, function () use ($limit, $user) {
            $query = Announcement::with(['user', 'comments'])
                ->where(function ($query) {
                    $query->whereNull('publish_at')
                        ->orWhere('publish_at', '<=', now());
                });

            // Apply role-based filtering if user is logged in
            if ($user) {
                $query->forUser($user);
            }

            return $query->orderBy('is_pinned', 'desc')
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get();
        });
    }

    /**
     * Get count of recent announcements for the current user.
     * Filters by target_roles so users only see announcements meant for them.
     *
     * @return int
     */
    public function getRecentAnnouncementsCount(): int
    {
        $user = auth()->user();
        $role = $user?->role ?? 'guest';

        return Cache::remember("dashboard_announcements_count_{$role}", 300, function () use ($user) {
            $query = Announcement::where(function ($query) {
                $query->whereNull('publish_at')
                    ->orWhere('publish_at', '<=', now());
            });

            // Apply role-based filtering if user is logged in
            if ($user) {
                $query->forUser($user);
            }

            return $query->count();
        });
    }

    /**
     * Get recent activity for a user.
     *
     * @param User $user
     * @return Collection
     */
    public function getRecentActivity(User $user): Collection
    {
        return UserProgress::where('user_id', $user->id)
            ->with('progressable')
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get()
            ->map(fn($progress) => [
                'type' => $this->getActivityType($progress),
                'title' => $this->getActivityTitle($progress),
                'timestamp' => $progress->updated_at->toISOString()
            ]);
    }

    /**
     * Get module progress for a user.
     *
     * @param User $user
     * @return Collection
     */
    public function getModuleProgress(User $user): Collection
    {
        return Module::where('is_active', true)
            ->with('informationSheets')
            ->get()
            ->map(function ($module) use ($user) {
                $progress = $this->calculateModuleProgress($module, $user);
                return [
                    'id' => $module->id,
                    'name' => $module->module_name,
                    'progress' => $progress['percentage'],
                    'status' => $progress['status']
                ];
            })
            ->filter(fn($module) => $module['progress'] > 0)
            ->sortByDesc('progress')
            ->values();
    }

    /**
     * Calculate progress for a specific module.
     *
     * @param Module $module
     * @param User $user
     * @return array
     */
    public function calculateModuleProgress(Module $module, User $user): array
    {
        $totalSheets = $module->informationSheets->count();

        if ($totalSheets === 0) {
            return ['percentage' => 0, 'status' => 'Not Started'];
        }

        $completedSheets = UserProgress::where('user_id', $user->id)
            ->where('progressable_type', InformationSheet::class)
            ->whereIn('progressable_id', $module->informationSheets->pluck('id'))
            ->where('status', 'completed')
            ->count();

        $percentage = ($completedSheets / $totalSheets) * 100;

        $status = match (true) {
            $percentage === 0.0 => 'Not Started',
            $percentage === 100.0 => 'Completed',
            default => 'In Progress',
        };

        return [
            'percentage' => round($percentage),
            'status' => $status
        ];
    }

    /**
     * Format seconds into human-readable time string.
     *
     * @param int $seconds
     * @return string
     */
    public function formatTime(int $seconds): string
    {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);

        if ($hours > 0) {
            return "{$hours}h {$minutes}m";
        }

        return "{$minutes}m";
    }

    // =========================================================================
    // PUBLIC METHODS - Pending Registrations (delegated to PendingActivitiesService)
    // =========================================================================

    /**
     * Get pending registrations awaiting approval (for admin dashboard).
     */
    public function getPendingRegistrations(): Collection
    {
        return $this->pendingActivities->getPendingRegistrations();
    }

    /**
     * Get count of pending registrations (email verified, awaiting approval).
     */
    public function getPendingRegistrationsCount(): int
    {
        return $this->pendingActivities->getPendingRegistrationsCount();
    }

    /**
     * Get upcoming deadlines for instructor dashboard.
     */
    public function getUpcomingDeadlinesForInstructor(User $user): Collection
    {
        return $this->pendingActivities->getUpcomingDeadlinesForInstructor($user);
    }

    /**
     * Get upcoming deadlines for student dashboard.
     */
    public function getUpcomingDeadlinesForStudent(User $user): Collection
    {
        return $this->pendingActivities->getUpcomingDeadlinesForStudent($user);
    }

    /**
     * Get count of upcoming deadlines.
     */
    public function getUpcomingDeadlinesCount(User $user): int
    {
        return $this->pendingActivities->getUpcomingDeadlinesCount($user);
    }

    // =========================================================================
    // CACHE MANAGEMENT
    // =========================================================================

    public function clearUserCache(User $user): void
    {
        Cache::forget("dashboard_admin_stats_{$user->id}");
        Cache::forget("dashboard_progress_{$user->id}");
        $this->pendingActivities->clearPendingEvaluationsCache($user);
    }

    public function clearRegistrationCache(): void
    {
        $this->pendingActivities->clearRegistrationCache();
    }

    public function clearAnnouncementCache(): void
    {
        // Clear role-qualified cache keys matching getRecentAnnouncements()
        foreach (['admin', 'instructor', 'student'] as $role) {
            Cache::forget("dashboard_announcements_count_{$role}");
            foreach ([3, 5, 10] as $limit) {
                Cache::forget("dashboard_announcements_{$limit}_{$role}");
            }
        }
    }

    // =========================================================================
    // PROTECTED METHODS - Internal Helpers
    // =========================================================================

    /**
     * Get activity type string for a progress record.
     *
     * @param UserProgress $progress
     * @return string
     */
    protected function getActivityType(UserProgress $progress): string
    {
        return match ($progress->progressable_type) {
            Module::class => 'module_completed',
            InformationSheet::class => 'sheet_completed',
            default => match ($progress->status) {
                'passed' => 'quiz_passed',
                'failed' => 'quiz_failed',
                default => 'started',
            },
        };
    }

    /**
     * Get activity title for a progress record.
     *
     * @param UserProgress $progress
     * @return string
     */
    protected function getActivityTitle(UserProgress $progress): string
    {
        $progressable = $progress->progressable;

        if (!$progressable) {
            return 'Unknown Activity';
        }

        return match ($progress->progressable_type) {
            Module::class => "Completed module: {$progressable->module_name}",
            InformationSheet::class => "Completed sheet: {$progressable->title}",
            default => 'Updated progress',
        };
    }
}

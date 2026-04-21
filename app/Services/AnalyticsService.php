<?php

namespace App\Services;

use App\Constants\Roles;
use App\Models\User;
use App\Models\Course;
use App\Models\Module;
use App\Models\UserProgress;
use App\Models\AuditLog;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

/**
 * Service for generating analytics and metrics for the JOMS LMS.
 *
 * Provides dashboard metrics including:
 * - User statistics (students, instructors, activity)
 * - Course and module metrics
 * - Engagement tracking
 * - Performance analysis
 *
 * Results are cached for 1 hour to improve performance.
 */
class AnalyticsService
{
    /** Cache duration in seconds (1 hour) */
    private const CACHE_DURATION = 3600;

    /** Cache key for dashboard metrics */
    private const CACHE_KEY = 'analytics_dashboard';

    // =========================================================================
    // PUBLIC METHODS - Dashboard Metrics
    // =========================================================================

    /**
     * Get all dashboard metrics (cached).
     *
     * @return array
     */
    public function getDashboardMetrics(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_DURATION, function () {
            return [
                'users' => $this->getUserMetrics(),
                'courses' => $this->getCourseMetrics(),
                'engagement' => $this->getEngagementMetrics(),
                'performance' => $this->getPerformanceMetrics(),
                'modules' => $this->getModuleMetrics(),
            ];
        });
    }

    /**
     * Get user-related metrics.
     *
     * @return array
     */
    public function getUserMetrics(): array
    {
        $today = Carbon::today();
        $weekAgo = $today->copy()->subWeek();

        return [
            'total_students' => User::where('role', Roles::STUDENT)->where('stat', 1)->count(),
            'total_instructors' => User::where('role', Roles::INSTRUCTOR)->where('stat', 1)->count(),
            'new_students_today' => User::where('role', Roles::STUDENT)
                ->whereDate('created_at', $today)
                ->count(),
            'new_students_week' => User::where('role', Roles::STUDENT)
                ->whereBetween('created_at', [$weekAgo, $today])
                ->count(),
            'active_today' => User::whereDate('last_login', $today)->count(),
            'active_week' => User::whereBetween('last_login', [$weekAgo, $today])->count(),
            'pending_approval' => User::where('stat', 0)
                ->whereNotNull('email_verified_at')
                ->count(),
        ];
    }

    /**
     * Get course-related metrics.
     *
     * @return array
     */
    public function getCourseMetrics(): array
    {
        return [
            'total_courses' => Course::where('is_active', true)->count(),
            'total_modules' => Module::where('is_active', true)->count(),
            'completion_rate' => $this->calculateOverallCompletionRate(),
            'average_progress' => $this->calculateAverageProgress(),
        ];
    }

    /**
     * Get engagement metrics for the past week.
     *
     * @return array
     */
    public function getEngagementMetrics(): array
    {
        $weekAgo = Carbon::now()->subWeek();

        return [
            'activities_completed_week' => UserProgress::where('status', 'completed')
                ->where('completed_at', '>=', $weekAgo)
                ->count(),
            'homework_submissions_week' => DB::table('homework_submissions')
                ->where('created_at', '>=', $weekAgo)
                ->count(),
            'quiz_attempts_week' => DB::table('quiz_attempts')
                ->where('created_at', '>=', $weekAgo)
                ->count(),
            'daily_active_users' => $this->getDailyActiveUsers(7),
        ];
    }

    /**
     * Get performance metrics.
     *
     * @return array
     */
    public function getPerformanceMetrics(): array
    {
        return [
            'average_score' => round(UserProgress::whereNotNull('score')->avg('score') ?? 0, 1),
            'pass_rate' => $this->calculatePassRate(),
            'top_performers' => $this->getTopPerformers(5),
            'at_risk_students' => $this->getAtRiskStudents(),
        ];
    }

    /**
     * Get detailed analytics for a specific module.
     *
     * @param int $moduleId
     * @return array
     */
    public function getModuleAnalytics(int $moduleId): array
    {
        $module = Module::findOrFail($moduleId);
        $moduleClass = Module::class;

        return [
            'total_enrollments' => UserProgress::where('progressable_type', $moduleClass)
                ->where('progressable_id', $moduleId)
                ->count(),
            'completions' => UserProgress::where('progressable_type', $moduleClass)
                ->where('progressable_id', $moduleId)
                ->where('status', 'completed')
                ->count(),
            'average_score' => UserProgress::where('progressable_type', $moduleClass)
                ->where('progressable_id', $moduleId)
                ->whereNotNull('score')
                ->avg('score'),
            'average_time' => UserProgress::where('progressable_type', $moduleClass)
                ->where('progressable_id', $moduleId)
                ->avg('time_spent'),
        ];
    }

    /**
     * Get metrics for all modules.
     *
     * @return array
     */
    public function getModuleMetrics(): array
    {
        $modules = Module::where('is_active', true)->get();
        $moduleIds = $modules->pluck('id');
        $moduleClass = Module::class;

        // Batch all stats in 3 aggregate queries instead of 4*N
        $progressStats = DB::table('user_progress')
            ->where('progressable_type', $moduleClass)
            ->whereIn('progressable_id', $moduleIds)
            ->selectRaw('progressable_id,
                COUNT(CASE WHEN score IS NOT NULL THEN 1 END) as total_attempts,
                COUNT(CASE WHEN status IN (\'passed\', \'completed\') THEN 1 END) as passed,
                COUNT(CASE WHEN status = \'failed\' THEN 1 END) as failed,
                AVG(score) as avg_score')
            ->groupBy('progressable_id')
            ->get()
            ->keyBy('progressable_id');

        $moduleStats = $modules->map(function ($module) use ($progressStats) {
            $stats = $progressStats->get($module->id);
            $totalAttempts = $stats->total_attempts ?? 0;
            $passedCount = $stats->passed ?? 0;
            $failedCount = $stats->failed ?? 0;
            $avgScore = $stats->avg_score ?? 0;

            return [
                'id' => $module->id,
                'name' => $module->module_title ?? $module->module_name ?? "Module {$module->id}",
                'module_number' => $module->module_number ?? '',
                'total_attempts' => $totalAttempts,
                'passed' => $passedCount,
                'failed' => $failedCount,
                'pass_rate' => $totalAttempts > 0 ? round(($passedCount / $totalAttempts) * 100, 1) : 0,
                'fail_rate' => $totalAttempts > 0 ? round(($failedCount / $totalAttempts) * 100, 1) : 0,
                'average_score' => round($avgScore, 1),
            ];
        })->toArray();

        $totalAttempts = array_sum(array_column($moduleStats, 'total_attempts'));
        $totalPassed = array_sum(array_column($moduleStats, 'passed'));
        $totalFailed = array_sum(array_column($moduleStats, 'failed'));

        return [
            'modules_list' => $moduleStats,
            'overall_pass_rate' => $totalAttempts > 0 ? round(($totalPassed / $totalAttempts) * 100, 1) : 0,
            'overall_fail_rate' => $totalAttempts > 0 ? round(($totalFailed / $totalAttempts) * 100, 1) : 0,
            'total_attempts' => $totalAttempts,
            'total_passed' => $totalPassed,
            'total_failed' => $totalFailed,
        ];
    }

    /**
     * Calculate progress for a specific student.
     *
     * @param User $student
     * @return float Progress percentage (0-100)
     */
    public function calculateStudentProgress(User $student): float
    {
        $totalModules = Module::where('is_active', true)->count();
        if ($totalModules === 0) {
            return 0;
        }

        $completedModules = UserProgress::where('user_id', $student->id)
            ->where('progressable_type', Module::class)
            ->where('status', 'completed')
            ->count();

        return round(($completedModules / $totalModules) * 100, 1);
    }

    /**
     * Get top performing students.
     *
     * @param int $limit
     * @return Collection
     */
    public function getTopPerformers(int $limit = 10): Collection
    {
        return User::where('role', Roles::STUDENT)
            ->where('stat', 1)
            ->orderByDesc('total_points')
            ->limit($limit)
            ->get(['id', 'first_name', 'last_name', 'total_points', 'profile_image']);
    }

    /**
     * Get students at risk (inactive for more than a week).
     *
     * @return Collection
     */
    public function getAtRiskStudents(): Collection
    {
        $weekAgo = Carbon::now()->subWeek();

        return User::where('role', Roles::STUDENT)
            ->where('stat', 1)
            ->where(function ($query) use ($weekAgo) {
                $query->whereNull('last_login')
                    ->orWhere('last_login', '<', $weekAgo);
            })
            ->limit(10)
            ->get(['id', 'first_name', 'last_name', 'last_login', 'email']);
    }

    /**
     * Clear the analytics cache.
     *
     * @return void
     */
    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    // =========================================================================
    // PRIVATE METHODS - Calculations
    // =========================================================================

    /**
     * Calculate overall course completion rate.
     *
     * @return float
     */
    private function calculateOverallCompletionRate(): float
    {
        $totalStudents = User::where('role', Roles::STUDENT)->where('stat', 1)->count();
        if ($totalStudents === 0) {
            return 0;
        }

        $completedCourses = DB::table('user_progress')
            ->where('progressable_type', Course::class)
            ->where('status', 'completed')
            ->distinct('user_id')
            ->count();

        return round(($completedCourses / $totalStudents) * 100, 1);
    }

    /**
     * Calculate average progress across all students.
     *
     * @return float
     */
    private function calculateAverageProgress(): float
    {
        $totalStudents = User::where('role', Roles::STUDENT)->where('stat', 1)->count();
        if ($totalStudents === 0) {
            return 0;
        }

        $totalModules = Module::where('is_active', true)->count();
        if ($totalModules === 0) {
            return 0;
        }

        // Single aggregate query instead of N+1 per student
        $avgCompletedModules = DB::table('user_progress')
            ->where('progressable_type', Module::class)
            ->where('status', 'completed')
            ->whereIn('user_id', User::where('role', Roles::STUDENT)->where('stat', 1)->select('id'))
            ->selectRaw('COUNT(*) / ? as avg_completed', [$totalStudents])
            ->value('avg_completed');

        return round(($avgCompletedModules / $totalModules) * 100, 1);
    }

    /**
     * Calculate overall pass rate.
     *
     * @return float
     */
    private function calculatePassRate(): float
    {
        $attempts = UserProgress::whereNotNull('score')->count();
        if ($attempts === 0) {
            return 0;
        }

        $passes = UserProgress::whereNotNull('score')
            ->whereIn('status', ['passed', 'completed'])
            ->count();

        return round(($passes / $attempts) * 100, 1);
    }

    /**
     * Get daily active user counts for the past N days.
     *
     * @param int $days
     * @return array
     */
    private function getDailyActiveUsers(int $days): array
    {
        $startDate = Carbon::today()->subDays($days - 1);

        // Single query for all days instead of N queries
        $counts = DB::table('users')
            ->where('last_login', '>=', $startDate)
            ->selectRaw('DATE(last_login) as login_date, COUNT(*) as count')
            ->groupByRaw('DATE(last_login)')
            ->pluck('count', 'login_date');

        $data = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $data[] = [
                'date' => $date->format('M d'),
                'count' => $counts->get($date->toDateString(), 0),
            ];
        }

        return $data;
    }

}

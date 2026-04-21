<?php

namespace App\Http\Controllers;

use App\Models\UserProgress;
use App\Models\Module;
use App\Models\User;
use App\Services\DashboardStatisticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;

/**
 * Handles dashboard views for all user roles in the JOMS LMS.
 *
 * Provides role-specific dashboard data:
 * - Students: Progress, pending activities, completed work
 * - Instructors: Student stats for their section, pending evaluations
 * - Admins: System-wide statistics
 */
class DashboardController extends Controller
{
    public function __construct(private DashboardStatisticsService $stats)
    {
    }

    // =========================================================================
    // PUBLIC METHODS - Main Dashboard
    // =========================================================================

    /**
     * Display the main dashboard based on user role.
     *
     * @return View
     */
    public function index(): View
    {
        $user = Auth::user();

        $recentAnnouncements = $this->stats->getRecentAnnouncements(3);
        $recentAnnouncementsCount = $this->stats->getRecentAnnouncementsCount();

        if ($this->stats->isAdminOrInstructor($user)) {
            return $this->renderAdminInstructorDashboard($user, $recentAnnouncements, $recentAnnouncementsCount);
        }

        return $this->renderStudentDashboard($user, $recentAnnouncements, $recentAnnouncementsCount);
    }

    /**
     * Redirect user to their role-specific dashboard route.
     *
     * @return RedirectResponse
     */
    public function redirectToRoleDashboard(): RedirectResponse
    {
        $user = Auth::user();

        if ($user->role === \App\Constants\Roles::ADMIN) {
            return redirect()->route('admin.dashboard');
        }

        if ($user->role === \App\Constants\Roles::INSTRUCTOR) {
            return redirect()->route('instructor.dashboard');
        }

        return redirect()->route('student.dashboard');
    }

    // =========================================================================
    // PUBLIC METHODS - API Endpoints
    // =========================================================================

    /**
     * Get student dashboard data as JSON (for AJAX requests).
     *
     * @return JsonResponse
     */
    public function getStudentDashboardData(): JsonResponse
    {
        try {
            $user = Auth::user();
            $progressSummary = $this->stats->getProgressSummary($user);

            $progressPercentage = $progressSummary['total_modules'] > 0
                ? ($progressSummary['completed_modules'] / $progressSummary['total_modules']) * 100
                : 0;

            $totalActivities = $progressSummary['total_modules'];
            $completedActivities = $progressSummary['completed_modules'];

            return response()->json([
                'progress' => round($progressPercentage),
                'finished_activities' => $completedActivities . '/' . $totalActivities,
                'total_modules' => $progressSummary['total_modules'],
                'average_grade' => $progressSummary['average_score']
            ]);
        } catch (\Exception $e) {
            Log::error('DashboardController::getStudentDashboardData failed', [
                'error' => $e->getMessage(),
                'user' => auth()->id(),
            ]);
            return response()->json(['error' => 'Failed to load dashboard data.'], 500);
        }
    }

    /**
     * Get detailed progress data as JSON.
     *
     * @return JsonResponse
     */
    public function getProgressData(): JsonResponse
    {
        try {
            $user = Auth::user();

            return response()->json([
                'summary' => $this->stats->getProgressSummary($user),
                'recentActivity' => $this->stats->getRecentActivity($user),
                'moduleProgress' => $this->stats->getModuleProgress($user)
            ]);
        } catch (\Exception $e) {
            Log::error('DashboardController::getProgressData failed', [
                'error' => $e->getMessage(),
                'user' => auth()->id(),
            ]);
            return response()->json(['error' => 'Failed to load progress data.'], 500);
        }
    }

    /**
     * Get progress report statistics as JSON.
     *
     * @return JsonResponse
     */
    public function getProgressReport(): JsonResponse
    {
        try {
            $user = Auth::user();

            $totalLearningTime = UserProgress::where('user_id', $user->id)->sum('time_spent');

            $averageScore = UserProgress::where('user_id', $user->id)
                ->whereNotNull('score')
                ->avg('score');

            $completedModules = UserProgress::where('user_id', $user->id)
                ->where('progressable_type', Module::class)
                ->where('status', 'completed')
                ->count();

            $totalModules = Module::where('is_active', true)->count();
            $completionRate = $totalModules > 0 ? ($completedModules / $totalModules) * 100 : 0;

            return response()->json([
                'total_learning_time' => $this->stats->formatTime($totalLearningTime),
                'average_score' => round($averageScore ?? 0, 1),
                'completion_rate' => round($completionRate, 1)
            ]);
        } catch (\Exception $e) {
            Log::error('DashboardController::getProgressReport failed', [
                'error' => $e->getMessage(),
                'user' => auth()->id(),
            ]);
            return response()->json(['error' => 'Failed to load progress report.'], 500);
        }
    }

    // =========================================================================
    // PRIVATE METHODS - Dashboard Rendering
    // =========================================================================

    /**
     * Render dashboard for admin or instructor users.
     *
     * @param User $user
     * @param Collection $recentAnnouncements
     * @param int $recentAnnouncementsCount
     * @return View
     */
    private function renderAdminInstructorDashboard(User $user, Collection $recentAnnouncements, int $recentAnnouncementsCount): View
    {
        $data = $this->stats->getAdminInstructorStats($user);

        $data['recentAnnouncements'] = $recentAnnouncements;
        $data['recentAnnouncementsCount'] = $recentAnnouncementsCount;
        $data['unreadCount'] = 0;
        $data['recentSubmissions'] = $this->stats->getRecentSubmissionsForInstructor($user);
        $data['pendingEvaluations'] = $this->stats->getPendingEvaluationsCount($user);
        $data['calendarDeadlines'] = $this->stats->getUpcomingDeadlinesForInstructor($user);

        // Pending registrations only for admin
        if ($user->role === \App\Constants\Roles::ADMIN) {
            $data['pendingRegistrations'] = $this->stats->getPendingRegistrations();
            $data['pendingRegistrationsCount'] = $this->stats->getPendingRegistrationsCount();
        } else {
            $data['pendingRegistrations'] = collect();
            $data['pendingRegistrationsCount'] = 0;
        }

        // Upcoming deadlines for instructors
        $data['upcomingDeadlines'] = $this->stats->getUpcomingDeadlinesForInstructor($user);
        $data['upcomingDeadlinesCount'] = $this->stats->getUpcomingDeadlinesCount($user);

        return view('dashboard', $data);
    }

    /**
     * Render dashboard for student users.
     *
     * @param User $user
     * @param Collection $recentAnnouncements
     * @param int $recentAnnouncementsCount
     * @return View
     */
    private function renderStudentDashboard(User $user, Collection $recentAnnouncements, int $recentAnnouncementsCount): View
    {
        $progressSummary = $this->stats->getProgressSummary($user);

        $progressPercentage = $progressSummary['total_modules'] > 0
            ? ($progressSummary['completed_modules'] / $progressSummary['total_modules']) * 100
            : 0;

        $data = [
            'recentAnnouncements' => $recentAnnouncements,
            'recentAnnouncementsCount' => $recentAnnouncementsCount,
            'unreadCount' => 0,
            'student_progress' => round($progressPercentage),
            'finished_activities' => $progressSummary['completed_modules'] . '/' . $progressSummary['total_modules'],
            'total_modules' => $progressSummary['total_modules'],
            'average_grade' => $progressSummary['average_score'] . '%',
            'pendingActivities' => $this->stats->getPendingActivitiesForStudent($user),
            'completedActivitiesList' => $this->stats->getCompletedActivitiesForStudent($user),
            'upcomingDeadlines' => $this->stats->getUpcomingDeadlinesForStudent($user),
            'upcomingDeadlinesCount' => $this->stats->getUpcomingDeadlinesCount($user),
            'calendarDeadlines' => $this->stats->getUpcomingDeadlinesForStudent($user),
        ];

        return view('dashboard', $data);
    }
}

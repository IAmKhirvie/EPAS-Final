<?php

namespace App\Services;

use App\Constants\Roles;
use App\Models\Homework;
use App\Models\HomeworkSubmission;
use App\Models\JobSheet;
use App\Models\JobSheetSubmission;
use App\Models\Registration;
use App\Models\SelfCheck;
use App\Models\SelfCheckSubmission;
use App\Models\TaskSheet;
use App\Models\TaskSheetSubmission;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Handles pending activities, submissions, and registrations for dashboard display.
 *
 * Extracted from DashboardStatisticsService to keep services focused.
 * Covers student pending/completed activities, instructor submission review,
 * and admin pending registrations.
 */
class PendingActivitiesService
{
    private array $studentIdCache = [];
    // =========================================================================
    // PUBLIC METHODS - Student Activities
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
        $pending = collect();

        $pending = $pending->merge($this->getPendingSelfChecks($user));
        $pending = $pending->merge($this->getPendingHomeworks($user));
        $pending = $pending->merge($this->getPendingTaskSheets($user));
        $pending = $pending->merge($this->getPendingJobSheets($user));

        return $pending->sortByDesc('created_at')->take(10)->values();
    }

    /**
     * Get completed activities for a student.
     *
     * @param User $user
     * @return Collection
     */
    public function getCompletedActivitiesForStudent(User $user): Collection
    {
        $completed = collect();

        $selfCheckSubmissions = SelfCheckSubmission::where('user_id', $user->id)
            ->with('selfCheck.informationSheet.module')
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn($s) => $this->formatCompletedSelfCheck($s));

        $completed = $completed->merge($selfCheckSubmissions);

        $homeworkSubmissions = HomeworkSubmission::where('user_id', $user->id)
            ->with('homework.informationSheet.module')
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn($s) => $this->formatCompletedHomework($s));

        $completed = $completed->merge($homeworkSubmissions);

        return $completed->sortByDesc('completed_at')->take(5)->values();
    }

    // =========================================================================
    // PUBLIC METHODS - Instructor Submissions
    // =========================================================================

    /**
     * Get recent submissions for instructor review.
     *
     * @param User $user
     * @return Collection
     */
    public function getRecentSubmissionsForInstructor(User $user): Collection
    {
        $studentIds = $this->getStudentIdsForInstructor($user);
        $submissions = collect();

        $homeworkSubmissions = HomeworkSubmission::whereIn('user_id', $studentIds)
            ->whereNull('evaluated_at')
            ->with(['user', 'homework.informationSheet.module'])
            ->latest('submitted_at')
            ->limit(5)
            ->get()
            ->map(fn($s) => $this->formatInstructorHomeworkSubmission($s));

        $submissions = $submissions->merge($homeworkSubmissions);

        $taskSheetSubmissions = TaskSheetSubmission::whereIn('user_id', $studentIds)
            ->with(['user', 'taskSheet.informationSheet.module'])
            ->latest('submitted_at')
            ->limit(5)
            ->get()
            ->map(fn($s) => $this->formatInstructorTaskSheetSubmission($s));

        $submissions = $submissions->merge($taskSheetSubmissions);

        $jobSheetSubmissions = JobSheetSubmission::whereIn('user_id', $studentIds)
            ->whereNull('evaluated_at')
            ->with(['user', 'jobSheet.informationSheet.module'])
            ->latest('submitted_at')
            ->limit(5)
            ->get()
            ->map(fn($s) => $this->formatInstructorJobSheetSubmission($s));

        $submissions = $submissions->merge($jobSheetSubmissions);

        return $submissions->sortByDesc('submitted_at')->take(10)->values();
    }

    /**
     * Get count of pending evaluations for instructor.
     *
     * @param User $user
     * @return int
     */
    public function getPendingEvaluationsCount(User $user): int
    {
        return Cache::remember("dashboard_pending_evals_{$user->id}", 300, function () use ($user) {
            $studentIds = $this->getStudentIdsForInstructor($user);

            $homeworkCount = HomeworkSubmission::whereIn('user_id', $studentIds)
                ->whereNull('evaluated_at')
                ->count();

            $jobSheetCount = JobSheetSubmission::whereIn('user_id', $studentIds)
                ->whereNull('evaluated_at')
                ->count();

            return $homeworkCount + $jobSheetCount;
        });
    }

    // =========================================================================
    // PUBLIC METHODS - Pending Registrations
    // =========================================================================

    /**
     * Get pending registrations (pending or email verified, for admin dashboard).
     */
    public function getPendingRegistrations(): Collection
    {
        return Cache::remember('dashboard_pending_registrations', 300, function () {
            return Registration::pending()
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();
        });
    }

    /**
     * Get count of pending registrations (pending or email verified).
     */
    public function getPendingRegistrationsCount(): int
    {
        return Cache::remember('dashboard_pending_registrations_count', 300, function () {
            return Registration::pending()->count();
        });
    }

    // =========================================================================
    // PUBLIC METHODS - Upcoming Deadlines (for Instructors)
    // =========================================================================

    /**
     * Get upcoming deadlines for instructor dashboard.
     * Shows self-checks, competency tests, homeworks, etc. with due dates.
     */
    public function getUpcomingDeadlinesForInstructor(User $user): Collection
    {
        $deadlines = collect();

        // Get self-checks with due dates
        $selfChecks = SelfCheck::whereHas('informationSheet.module.course', function ($q) use ($user) {
                if ($user->role === Roles::INSTRUCTOR) {
                    $q->where('instructor_id', $user->id);
                }
            })
            ->whereNotNull('due_date')
            ->where('due_date', '>=', now())
            ->where('is_active', true)
            ->orderBy('due_date')
            ->limit(10)
            ->get();

        foreach ($selfChecks as $selfCheck) {
            $deadlines->push([
                'type' => 'self_check',
                'title' => $selfCheck->title,
                'subtitle' => $selfCheck->informationSheet?->module?->module_title ?? 'Self-Check',
                'due_date' => $selfCheck->due_date,
                'icon' => 'fas fa-clipboard-check',
                'color' => '#6f42c1',
                'url' => route('modules.self-checks.show', $selfCheck->id),
            ]);
        }

        // Get homeworks with due dates
        $homeworks = Homework::whereHas('informationSheet.module.course', function ($q) use ($user) {
                if ($user->role === Roles::INSTRUCTOR) {
                    $q->where('instructor_id', $user->id);
                }
            })
            ->whereNotNull('due_date')
            ->where('due_date', '>=', now())
            ->orderBy('due_date')
            ->limit(10)
            ->get();

        foreach ($homeworks as $homework) {
            $deadlines->push([
                'type' => 'homework',
                'title' => $homework->title,
                'subtitle' => $homework->informationSheet?->module?->module_title ?? 'Homework',
                'due_date' => $homework->due_date,
                'icon' => 'fas fa-book',
                'color' => '#fd7e14',
                'url' => route('homeworks.show', $homework->id),
            ]);
        }

        // Get competency tests with due dates (if they have time-based deadlines)
        if (class_exists(\App\Models\CompetencyTest::class)) {
            $tests = \App\Models\CompetencyTest::whereHas('module.course', function ($q) use ($user) {
                    if ($user->role === Roles::INSTRUCTOR) {
                        $q->where('instructor_id', $user->id);
                    }
                })
                ->whereNotNull('due_date')
                ->where('due_date', '>=', now())
                ->where('is_active', true)
                ->orderBy('due_date')
                ->limit(10)
                ->get();

            foreach ($tests as $test) {
                $deadlines->push([
                    'type' => 'competency_test',
                    'title' => $test->title,
                    'subtitle' => $test->module?->module_title ?? 'Competency Test',
                    'due_date' => $test->due_date,
                    'icon' => 'fas fa-file-alt',
                    'color' => '#dc3545',
                    'url' => $test->module ? route('competency-tests.show', ['module' => $test->module->id, 'competencyTest' => $test->id]) : '#',
                ]);
            }
        }

        return $deadlines->sortBy('due_date')->take(10)->values();
    }

    /**
     * Get count of upcoming deadlines.
     */
    public function getUpcomingDeadlinesCount(User $user): int
    {
        if ($user->role === Roles::STUDENT) {
            return $this->getUpcomingDeadlinesForStudent($user)->count();
        }
        return $this->getUpcomingDeadlinesForInstructor($user)->count();
    }

    /**
     * Get upcoming deadlines for student dashboard.
     * Shows activities with due dates that the student hasn't completed yet.
     */
    public function getUpcomingDeadlinesForStudent(User $user): Collection
    {
        $deadlines = collect();

        // Get submitted IDs to exclude completed items
        $submittedSelfCheckIds = SelfCheckSubmission::where('user_id', $user->id)->pluck('self_check_id');
        $submittedHomeworkIds = HomeworkSubmission::where('user_id', $user->id)->pluck('homework_id');

        // Get self-checks with due dates that student hasn't submitted
        $selfChecks = SelfCheck::whereNotIn('id', $submittedSelfCheckIds)
            ->whereNotNull('due_date')
            ->where('due_date', '>=', now())
            ->where('is_active', true)
            ->with('informationSheet.module')
            ->orderBy('due_date')
            ->limit(10)
            ->get();

        foreach ($selfChecks as $selfCheck) {
            $deadlines->push([
                'type' => 'self_check',
                'title' => $selfCheck->title,
                'subtitle' => $selfCheck->informationSheet?->module?->module_name ?? 'Self-Check',
                'due_date' => $selfCheck->due_date,
                'icon' => 'fas fa-clipboard-check',
                'color' => '#6f42c1',
                'url' => route('self-checks.show', $selfCheck->id),
            ]);
        }

        // Get homeworks with due dates that student hasn't submitted
        $homeworks = Homework::whereNotIn('id', $submittedHomeworkIds)
            ->whereNotNull('due_date')
            ->where('due_date', '>=', now())
            ->with('informationSheet.module')
            ->orderBy('due_date')
            ->limit(10)
            ->get();

        foreach ($homeworks as $homework) {
            $deadlines->push([
                'type' => 'homework',
                'title' => $homework->title,
                'subtitle' => $homework->informationSheet?->module?->module_name ?? 'Homework',
                'due_date' => $homework->due_date,
                'icon' => 'fas fa-book',
                'color' => '#fd7e14',
                'url' => route('homeworks.show', $homework->id),
            ]);
        }

        return $deadlines->sortBy('due_date')->take(10)->values();
    }

    // =========================================================================
    // PUBLIC METHODS - Utility Helpers
    // =========================================================================

    /**
     * Get student IDs visible to an instructor.
     *
     * @param User $user
     * @return Collection
     */
    public function getStudentIdsForInstructor(User $user): Collection
    {
        if (isset($this->studentIdCache[$user->id])) {
            return $this->studentIdCache[$user->id];
        }

        $query = User::where('role', Roles::STUDENT);

        if ($user->role === Roles::INSTRUCTOR) {
            $sections = $user->getAllAccessibleSections();
            if ($sections->isNotEmpty()) {
                $query->whereIn('section', $sections);
            } else {
                return $this->studentIdCache[$user->id] = collect();
            }
        }

        return $this->studentIdCache[$user->id] = $query->pluck('id');
    }

    // =========================================================================
    // CACHE MANAGEMENT
    // =========================================================================

    public function clearRegistrationCache(): void
    {
        Cache::forget('dashboard_pending_registrations');
        Cache::forget('dashboard_pending_registrations_count');
    }

    public function clearPendingEvaluationsCache(User $user): void
    {
        Cache::forget("dashboard_pending_evals_{$user->id}");
    }

    // =========================================================================
    // PROTECTED METHODS - Pending Activity Helpers
    // =========================================================================

    /**
     * Get pending self-checks for a student.
     *
     * @param User $user
     * @return Collection
     */
    protected function getPendingSelfChecks(User $user): Collection
    {
        $submittedIds = SelfCheckSubmission::where('user_id', $user->id)->pluck('self_check_id');

        return SelfCheck::whereNotIn('id', $submittedIds)
            ->with('informationSheet.module')
            ->latest()
            ->limit(20)
            ->get()
            ->map(fn($item) => [
                'type' => 'self_check',
                'icon' => 'fas fa-clipboard-check',
                'color' => '#ffb902',
                'title' => $item->title,
                'subtitle' => $item->informationSheet?->module?->module_name ?? 'Unknown Module',
                'url' => route('self-checks.show', $item),
                'created_at' => $item->created_at,
            ]);
    }

    /**
     * Get pending homeworks for a student.
     *
     * @param User $user
     * @return Collection
     */
    protected function getPendingHomeworks(User $user): Collection
    {
        $submittedIds = HomeworkSubmission::where('user_id', $user->id)->pluck('homework_id');

        return Homework::whereNotIn('id', $submittedIds)
            ->with('informationSheet.module')
            ->latest()
            ->limit(20)
            ->get()
            ->map(fn($item) => [
                'type' => 'homework',
                'icon' => 'fas fa-home',
                'color' => '#198754',
                'title' => $item->title,
                'subtitle' => $item->informationSheet?->module?->module_name ?? 'Unknown Module',
                'url' => route('homeworks.show', $item),
                'created_at' => $item->created_at,
                'deadline' => $item->due_date,
            ]);
    }

    /**
     * Get pending task sheets for a student.
     *
     * @param User $user
     * @return Collection
     */
    protected function getPendingTaskSheets(User $user): Collection
    {
        $submittedIds = TaskSheetSubmission::where('user_id', $user->id)->pluck('task_sheet_id');

        return TaskSheet::whereNotIn('id', $submittedIds)
            ->with('informationSheet.module')
            ->latest()
            ->limit(20)
            ->get()
            ->map(fn($item) => [
                'type' => 'task_sheet',
                'icon' => 'fas fa-tasks',
                'color' => '#fd7e14',
                'title' => $item->title,
                'subtitle' => $item->informationSheet?->module?->module_name ?? 'Unknown Module',
                'url' => route('task-sheets.show', $item),
                'created_at' => $item->created_at,
            ]);
    }

    /**
     * Get pending job sheets for a student.
     *
     * @param User $user
     * @return Collection
     */
    protected function getPendingJobSheets(User $user): Collection
    {
        $submittedIds = JobSheetSubmission::where('user_id', $user->id)->pluck('job_sheet_id');

        return JobSheet::whereNotIn('id', $submittedIds)
            ->with('informationSheet.module')
            ->latest()
            ->limit(20)
            ->get()
            ->map(fn($item) => [
                'type' => 'job_sheet',
                'icon' => 'fas fa-briefcase',
                'color' => '#6f42c1',
                'title' => $item->title,
                'subtitle' => $item->informationSheet?->module?->module_name ?? 'Unknown Module',
                'url' => route('job-sheets.show', $item),
                'created_at' => $item->created_at,
            ]);
    }

    // =========================================================================
    // PROTECTED METHODS - Formatting Helpers
    // =========================================================================

    /**
     * Format completed self-check submission for display.
     *
     * @param SelfCheckSubmission $submission
     * @return array
     */
    protected function formatCompletedSelfCheck(SelfCheckSubmission $submission): array
    {
        return [
            'type' => 'self_check',
            'icon' => 'fas fa-clipboard-check',
            'color' => '#ffb902',
            'title' => $submission->selfCheck?->title ?? 'Unknown',
            'subtitle' => $submission->selfCheck?->informationSheet?->module?->module_name ?? 'Unknown Module',
            'score' => $submission->percentage . '%',
            'passed' => $submission->passed,
            'completed_at' => $submission->completed_at ?? $submission->created_at,
        ];
    }

    /**
     * Format completed homework submission for display.
     *
     * @param HomeworkSubmission $submission
     * @return array
     */
    protected function formatCompletedHomework(HomeworkSubmission $submission): array
    {
        $passThreshold = config('joms.grading.homework_pass_threshold', 0.6);

        return [
            'type' => 'homework',
            'icon' => 'fas fa-home',
            'color' => '#198754',
            'title' => $submission->homework?->title ?? 'Unknown',
            'subtitle' => $submission->homework?->informationSheet?->module?->module_name ?? 'Unknown Module',
            'score' => $submission->score ? $submission->percentage . '%' : 'Pending evaluation',
            'passed' => $submission->score >= ($submission->max_points * $passThreshold),
            'completed_at' => $submission->submitted_at ?? $submission->created_at,
        ];
    }

    /**
     * Format homework submission for instructor view.
     *
     * @param HomeworkSubmission $submission
     * @return array
     */
    protected function formatInstructorHomeworkSubmission(HomeworkSubmission $submission): array
    {
        return [
            'type' => 'homework',
            'icon' => 'fas fa-home',
            'color' => '#198754',
            'student_name' => $submission->user?->full_name ?? 'Unknown',
            'student_avatar' => $submission->user?->profile_image_url,
            'title' => $submission->homework?->title ?? 'Unknown',
            'module' => $submission->homework?->informationSheet?->module?->module_name ?? 'Unknown',
            'submitted_at' => $submission->submitted_at,
            'status' => 'Pending Evaluation',
            'url' => route('homework-submissions.evaluate', $submission),
        ];
    }

    /**
     * Format task sheet submission for instructor view.
     *
     * @param TaskSheetSubmission $submission
     * @return array
     */
    protected function formatInstructorTaskSheetSubmission(TaskSheetSubmission $submission): array
    {
        return [
            'type' => 'task_sheet',
            'icon' => 'fas fa-tasks',
            'color' => '#fd7e14',
            'student_name' => $submission->user?->full_name ?? 'Unknown',
            'student_avatar' => $submission->user?->profile_image_url,
            'title' => $submission->taskSheet?->title ?? 'Unknown',
            'module' => $submission->taskSheet?->informationSheet?->module?->module_name ?? 'Unknown',
            'submitted_at' => $submission->submitted_at ?? $submission->created_at,
            'status' => 'Submitted',
            'url' => '#',
        ];
    }

    /**
     * Format job sheet submission for instructor view.
     *
     * @param JobSheetSubmission $submission
     * @return array
     */
    protected function formatInstructorJobSheetSubmission(JobSheetSubmission $submission): array
    {
        return [
            'type' => 'job_sheet',
            'icon' => 'fas fa-briefcase',
            'color' => '#6f42c1',
            'student_name' => $submission->user?->full_name ?? 'Unknown',
            'student_avatar' => $submission->user?->profile_image_url,
            'title' => $submission->jobSheet?->title ?? 'Unknown',
            'module' => $submission->jobSheet?->informationSheet?->module?->module_name ?? 'Unknown',
            'submitted_at' => $submission->submitted_at ?? $submission->created_at,
            'status' => 'Pending Evaluation',
            'url' => '#',
        ];
    }
}

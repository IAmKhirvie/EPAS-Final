<?php

namespace App\Services;

use App\Models\InformationSheet;
use App\Models\Module;
use App\Models\SelfCheck;
use App\Models\Homework;
use App\Models\TaskSheet;
use App\Models\JobSheet;
use App\Models\Checklist;
use App\Models\DocumentAssessment;
use App\Models\UserProgress;
use App\Models\Course;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

/**
 * ProgressTrackingService
 *
 * Centralized service for tracking student progress through modules.
 * Handles progress recording for all activity types and auto-completion logic.
 */
class ProgressTrackingService
{
    /**
     * Record progress when a self-check (quiz) is submitted.
     */
    public function recordSelfCheckProgress(SelfCheck $selfCheck, int $userId, array $submissionData): void
    {
        $informationSheet = $selfCheck->informationSheet;
        if (!$informationSheet || !$informationSheet->module_id) {
            return;
        }

        $newStatus = ($submissionData['passed'] ?? false) ? 'passed' : 'failed';
        $newScore = $submissionData['score'] ?? null;

        $progress = UserProgress::firstOrNew([
            'user_id' => $userId,
            'module_id' => $informationSheet->module_id,
            'progressable_type' => SelfCheck::class,
            'progressable_id' => $selfCheck->id,
        ]);

        // Keep best score — only update if new score is higher or first attempt
        if (!$progress->exists || $newScore === null || $newScore >= ($progress->score ?? 0)) {
            $progress->status = $newStatus;
            $progress->score = $newScore;
            $progress->max_score = $submissionData['total_points'] ?? null;
        }

        $progress->attempts = ($progress->attempts ?? 0) + 1;
        $progress->completed_at = now();
        $progress->save();

        // Check if information sheet should be marked complete
        $this->checkAndUpdateSheetCompletion($informationSheet, $userId);
    }

    /**
     * Record progress when homework is submitted.
     */
    public function recordHomeworkProgress(Homework $homework, int $userId, array $submissionData = []): void
    {
        $informationSheet = $homework->informationSheet;
        if (!$informationSheet || !$informationSheet->module_id) {
            return;
        }

        // 'submitted' counts toward sheet completion; 'completed' when graded
        $status = isset($submissionData['grade']) ? 'completed' : 'submitted';

        $progress = UserProgress::firstOrNew([
            'user_id' => $userId,
            'module_id' => $informationSheet->module_id,
            'progressable_type' => Homework::class,
            'progressable_id' => $homework->id,
        ]);

        // Don't downgrade from completed to submitted
        if (!($progress->exists && $progress->status === 'completed' && $status === 'submitted')) {
            $progress->status = $status;
        }
        $progress->score = $submissionData['grade'] ?? $progress->score;
        $progress->max_score = 100;
        $progress->attempts = ($progress->attempts ?? 0) + 1;
        $progress->started_at = $progress->started_at ?? now();
        $progress->completed_at = now();
        $progress->save();

        $this->checkAndUpdateSheetCompletion($informationSheet, $userId);
    }

    /**
     * Record progress when task sheet is submitted.
     */
    public function recordTaskSheetProgress(TaskSheet $taskSheet, int $userId, array $submissionData = []): void
    {
        $informationSheet = $taskSheet->informationSheet;
        if (!$informationSheet || !$informationSheet->module_id) {
            return;
        }

        $status = isset($submissionData['grade']) ? 'completed' : 'submitted';

        $progress = UserProgress::firstOrNew([
            'user_id' => $userId,
            'module_id' => $informationSheet->module_id,
            'progressable_type' => TaskSheet::class,
            'progressable_id' => $taskSheet->id,
        ]);

        if (!($progress->exists && $progress->status === 'completed' && $status === 'submitted')) {
            $progress->status = $status;
        }
        $progress->score = $submissionData['grade'] ?? $progress->score;
        $progress->max_score = $submissionData['max_score'] ?? 100;
        $progress->attempts = ($progress->attempts ?? 0) + 1;
        $progress->started_at = $progress->started_at ?? now();
        $progress->completed_at = now();
        $progress->save();

        $this->checkAndUpdateSheetCompletion($informationSheet, $userId);
    }

    /**
     * Record progress when job sheet is submitted.
     */
    public function recordJobSheetProgress(JobSheet $jobSheet, int $userId, array $submissionData = []): void
    {
        $informationSheet = $jobSheet->informationSheet;
        if (!$informationSheet || !$informationSheet->module_id) {
            return;
        }

        $status = isset($submissionData['grade']) ? 'completed' : 'submitted';

        $progress = UserProgress::firstOrNew([
            'user_id' => $userId,
            'module_id' => $informationSheet->module_id,
            'progressable_type' => JobSheet::class,
            'progressable_id' => $jobSheet->id,
        ]);

        if (!($progress->exists && $progress->status === 'completed' && $status === 'submitted')) {
            $progress->status = $status;
        }
        $progress->score = $submissionData['grade'] ?? $progress->score;
        $progress->max_score = $submissionData['max_score'] ?? 100;
        $progress->attempts = ($progress->attempts ?? 0) + 1;
        $progress->started_at = $progress->started_at ?? now();
        $progress->completed_at = now();
        $progress->save();

        $this->checkAndUpdateSheetCompletion($informationSheet, $userId);
    }

    /**
     * Record progress when checklist is evaluated.
     */
    public function recordChecklistProgress(Checklist $checklist, int $userId, array $submissionData = []): void
    {
        $informationSheet = $checklist->informationSheet;
        if (!$informationSheet || !$informationSheet->module_id) {
            return;
        }

        $status = 'completed';

        UserProgress::updateOrCreate(
            [
                'user_id' => $userId,
                'module_id' => $informationSheet->module_id,
                'progressable_type' => Checklist::class,
                'progressable_id' => $checklist->id,
            ],
            [
                'status' => $status,
                'score' => $submissionData['score'] ?? null,
                'max_score' => $submissionData['max_score'] ?? null,
                'completed_at' => now(),
            ]
        );

        $this->checkAndUpdateSheetCompletion($informationSheet, $userId);
    }

    /**
     * Record progress when document assessment is submitted.
     */
    public function recordDocumentAssessmentProgress(DocumentAssessment $assessment, int $userId, array $submissionData = []): void
    {
        $informationSheet = $assessment->informationSheet;
        if (!$informationSheet || !$informationSheet->module_id) {
            return;
        }

        $status = isset($submissionData['grade']) ? 'completed' : 'in_progress';

        UserProgress::updateOrCreate(
            [
                'user_id' => $userId,
                'module_id' => $informationSheet->module_id,
                'progressable_type' => DocumentAssessment::class,
                'progressable_id' => $assessment->id,
            ],
            [
                'status' => $status,
                'score' => $submissionData['grade'] ?? null,
                'max_score' => 100,
                'attempts' => \DB::raw('attempts + 1'),
                'started_at' => \DB::raw('COALESCE(started_at, NOW())'),
                'completed_at' => $status === 'completed' ? now() : null,
            ]
        );

        $this->checkAndUpdateSheetCompletion($informationSheet, $userId);
    }

    /**
     * Record when a topic is viewed/read.
     */
    public function recordTopicViewed(int $topicId, int $informationSheetId, int $moduleId, int $userId): void
    {
        UserProgress::updateOrCreate(
            [
                'user_id' => $userId,
                'module_id' => $moduleId,
                'progressable_type' => 'App\Models\Topic',
                'progressable_id' => $topicId,
            ],
            [
                'status' => 'completed',
                'completed_at' => now(),
            ]
        );

        // Mark sheet as in progress if not already
        $this->markSheetInProgress($informationSheetId, $moduleId, $userId);
    }

    /**
     * Mark an information sheet as in progress.
     */
    public function markSheetInProgress(int $sheetId, int $moduleId, int $userId): void
    {
        $existing = UserProgress::where([
            'user_id' => $userId,
            'module_id' => $moduleId,
            'progressable_type' => InformationSheet::class,
            'progressable_id' => $sheetId,
        ])->first();

        // Only update if not already completed
        if (!$existing) {
            UserProgress::create([
                'user_id' => $userId,
                'module_id' => $moduleId,
                'progressable_type' => InformationSheet::class,
                'progressable_id' => $sheetId,
                'status' => 'in_progress',
                'started_at' => now(),
            ]);
        } elseif ($existing->status !== 'completed') {
            $existing->update(['status' => 'in_progress']);
        }

        // Also mark module as in progress
        $this->markModuleInProgress($moduleId, $userId);
    }

    /**
     * Mark a module as in progress.
     */
    public function markModuleInProgress(int $moduleId, int $userId): void
    {
        $existing = UserProgress::where([
            'user_id' => $userId,
            'module_id' => $moduleId,
            'progressable_type' => Module::class,
            'progressable_id' => $moduleId,
        ])->first();

        // Only update if not already completed
        if (!$existing) {
            UserProgress::create([
                'user_id' => $userId,
                'module_id' => $moduleId,
                'progressable_type' => Module::class,
                'progressable_id' => $moduleId,
                'status' => 'in_progress',
                'started_at' => now(),
            ]);
        } elseif ($existing->status !== 'completed') {
            $existing->update(['status' => 'in_progress']);
        }
    }

    /**
     * Check if all activities in an information sheet are completed.
     * If so, mark the sheet as completed.
     */
    public function checkAndUpdateSheetCompletion(InformationSheet $sheet, int $userId): void
    {
        // Get all activities for this sheet
        $selfChecks = $sheet->selfChecks()->pluck('id')->toArray();
        $homeworks = $sheet->homeworks()->pluck('id')->toArray();
        $taskSheets = $sheet->taskSheets()->pluck('id')->toArray();
        $jobSheets = $sheet->jobSheets()->pluck('id')->toArray();
        $checklists = $sheet->checklists()->pluck('id')->toArray();
        $documentAssessments = $sheet->documentAssessments()->pluck('id')->toArray();

        $totalActivities = count($selfChecks) + count($homeworks) + count($taskSheets) +
                          count($jobSheets) + count($checklists) + count($documentAssessments);

        // If no activities, consider reading topics as completion
        if ($totalActivities === 0) {
            $topics = $sheet->topics()->pluck('id')->toArray();
            if (!empty($topics)) {
                $completedTopics = UserProgress::where('user_id', $userId)
                    ->where('module_id', $sheet->module_id)
                    ->where('progressable_type', 'App\Models\Topic')
                    ->whereIn('progressable_id', $topics)
                    ->where('status', 'completed')
                    ->count();

                if ($completedTopics >= count($topics)) {
                    $this->markSheetCompleted($sheet, $userId);
                }
            }
            return;
        }

        // Count completed activities
        $completedCount = 0;

        if (!empty($selfChecks)) {
            $completedCount += UserProgress::where('user_id', $userId)
                ->where('module_id', $sheet->module_id)
                ->where('progressable_type', SelfCheck::class)
                ->whereIn('progressable_id', $selfChecks)
                ->whereIn('status', ['passed', 'completed'])
                ->count();
        }

        if (!empty($homeworks)) {
            $completedCount += UserProgress::where('user_id', $userId)
                ->where('module_id', $sheet->module_id)
                ->where('progressable_type', Homework::class)
                ->whereIn('progressable_id', $homeworks)
                ->whereIn('status', ['completed', 'submitted'])
                ->count();
        }

        if (!empty($taskSheets)) {
            $completedCount += UserProgress::where('user_id', $userId)
                ->where('module_id', $sheet->module_id)
                ->where('progressable_type', TaskSheet::class)
                ->whereIn('progressable_id', $taskSheets)
                ->whereIn('status', ['completed', 'submitted'])
                ->count();
        }

        if (!empty($jobSheets)) {
            $completedCount += UserProgress::where('user_id', $userId)
                ->where('module_id', $sheet->module_id)
                ->where('progressable_type', JobSheet::class)
                ->whereIn('progressable_id', $jobSheets)
                ->whereIn('status', ['completed', 'submitted'])
                ->count();
        }

        if (!empty($checklists)) {
            $completedCount += UserProgress::where('user_id', $userId)
                ->where('module_id', $sheet->module_id)
                ->where('progressable_type', Checklist::class)
                ->whereIn('progressable_id', $checklists)
                ->where('status', 'completed')
                ->count();
        }

        if (!empty($documentAssessments)) {
            $completedCount += UserProgress::where('user_id', $userId)
                ->where('module_id', $sheet->module_id)
                ->where('progressable_type', DocumentAssessment::class)
                ->whereIn('progressable_id', $documentAssessments)
                ->where('status', 'completed')
                ->count();
        }

        // If all activities completed, mark sheet as completed
        if ($completedCount >= $totalActivities) {
            $this->markSheetCompleted($sheet, $userId);
        }
    }

    /**
     * Mark an information sheet as completed.
     */
    public function markSheetCompleted(InformationSheet $sheet, int $userId): void
    {
        UserProgress::updateOrCreate(
            [
                'user_id' => $userId,
                'module_id' => $sheet->module_id,
                'progressable_type' => InformationSheet::class,
                'progressable_id' => $sheet->id,
            ],
            [
                'status' => 'completed',
                'completed_at' => now(),
            ]
        );

        // Check if module should be marked complete
        $this->checkAndUpdateModuleCompletion($sheet->module_id, $userId);

        // Clear dashboard cache
        $this->clearProgressCache($userId);
    }

    /**
     * Check if all sheets in a module are completed.
     * If so, mark the module as completed.
     */
    public function checkAndUpdateModuleCompletion(int $moduleId, int $userId): void
    {
        $module = Module::find($moduleId);
        if (!$module) {
            return;
        }

        $totalSheets = $module->informationSheets()->count();
        if ($totalSheets === 0) {
            return;
        }

        $sheetIds = $module->informationSheets()->pluck('id')->toArray();

        $completedSheets = UserProgress::where('user_id', $userId)
            ->where('module_id', $moduleId)
            ->where('progressable_type', InformationSheet::class)
            ->whereIn('progressable_id', $sheetIds)
            ->where('status', 'completed')
            ->count();

        if ($completedSheets >= $totalSheets) {
            $this->markModuleCompleted($moduleId, $userId);
        }
    }

    /**
     * Mark a module as completed.
     */
    public function markModuleCompleted(int $moduleId, int $userId): void
    {
        // Calculate average score from all activities in this module
        $avgScore = UserProgress::where('user_id', $userId)
            ->where('module_id', $moduleId)
            ->whereNotNull('score')
            ->avg('score');

        UserProgress::updateOrCreate(
            [
                'user_id' => $userId,
                'module_id' => $moduleId,
                'progressable_type' => Module::class,
                'progressable_id' => $moduleId,
            ],
            [
                'status' => 'completed',
                'score' => $avgScore ? round($avgScore) : null,
                'completed_at' => now(),
            ]
        );

        $this->clearProgressCache($userId);

        Log::info("Module {$moduleId} marked as completed for user {$userId}");

        // Award gamification points for module completion
        try {
            $user = User::find($userId);
            $module = Module::find($moduleId);
            if ($user && $module) {
                app(GamificationService::class)->awardForActivity($user, 'module_complete', $module);
                app(AchievementService::class)->checkAndAward($user, 'module_complete');
            }
        } catch (\Exception $e) {
            Log::error("Failed to award module completion points: " . $e->getMessage());
        }

        // Check progress milestones
        $this->checkMilestones($moduleId, $userId);

        // Auto-issue certificate if all modules in the course are completed
        $this->checkAndIssueCertificate($moduleId, $userId);
    }

    /**
     * Check if the user has reached a progress milestone (25%, 50%, 75%, 100%) in the course.
     */
    protected function checkMilestones(int $moduleId, int $userId): void
    {
        try {
            $module = Module::find($moduleId);
            if (!$module || !$module->course_id) {
                return;
            }

            $course = Course::withCount('modules')->find($module->course_id);
            if (!$course || $course->modules_count === 0) {
                return;
            }

            $completedModules = UserProgress::where('user_id', $userId)
                ->where('progressable_type', Module::class)
                ->whereIn('progressable_id', $course->modules()->pluck('id'))
                ->where('status', 'completed')
                ->count();

            $percentage = ($completedModules / $course->modules_count) * 100;

            $milestones = config('joms.gamification.milestones', [25 => 25, 50 => 50, 75 => 75, 100 => 100]);
            $user = User::find($userId);
            if (!$user) {
                return;
            }

            foreach ($milestones as $threshold => $points) {
                if ($percentage >= $threshold) {
                    $cacheKey = "milestone_{$userId}_{$course->id}_{$threshold}";
                    if (!Cache::has($cacheKey)) {
                        app(GamificationService::class)->awardForActivity($user, "milestone_{$threshold}");
                        Cache::put($cacheKey, true, now()->addYear());
                        Log::info("User {$userId} reached {$threshold}% milestone in course {$course->id}");
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error("Milestone check failed: " . $e->getMessage());
        }
    }

    /**
     * Check if all modules in a course are completed and auto-issue a certificate.
     */
    protected function checkAndIssueCertificate(int $moduleId, int $userId): void
    {
        try {
            $module = Module::with('course')->find($moduleId);
            if (!$module || !$module->course) {
                return;
            }

            $user = User::find($userId);
            if (!$user) {
                return;
            }

            $certificateService = app(CertificateService::class);

            if ($certificateService->checkCourseCompletion($user, $module->course)) {
                // Award course completion points and achievement
                app(GamificationService::class)->awardForActivity($user, 'course_complete', $module->course);
                app(AchievementService::class)->checkAndAward($user, 'course_complete');

                $certificateService->generateCertificate($user, $module->course, [
                    'auto_issued' => true,
                    'trigger' => 'module_completion',
                    'trigger_module_id' => $moduleId,
                ]);

                Log::info("Auto-issued certificate for user {$userId} - completed all modules in course {$module->course->id}");
            }
        } catch (\Exception $e) {
            Log::error("Auto-certificate issuance failed for user {$userId}, module {$moduleId}", [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get detailed progress for a module.
     * Calculates progress based on individual items (topics, self-checks, etc.)
     */
    public function getModuleProgress(int $moduleId, int $userId): array
    {
        $module = Module::with(['informationSheets.topics', 'informationSheets.selfChecks',
                                'informationSheets.taskSheets', 'informationSheets.jobSheets'])->find($moduleId);
        if (!$module) {
            return ['percentage' => 0, 'status' => 'not_started', 'completed_sheets' => 0, 'total_sheets' => 0];
        }

        $totalSheets = $module->informationSheets->count();
        if ($totalSheets === 0) {
            return ['percentage' => 0, 'status' => 'not_started', 'completed_sheets' => 0, 'total_sheets' => 0];
        }

        // Count all trackable items across all sheets
        $totalItems = 0;
        $completedItems = 0;

        foreach ($module->informationSheets as $sheet) {
            // Topics
            $topicIds = $sheet->topics->pluck('id')->toArray();
            $totalItems += count($topicIds);
            if (!empty($topicIds)) {
                $completedItems += UserProgress::where('user_id', $userId)
                    ->where('module_id', $moduleId)
                    ->where('progressable_type', 'App\Models\Topic')
                    ->whereIn('progressable_id', $topicIds)
                    ->where('status', 'completed')
                    ->count();
            }

            // Self-checks
            $selfCheckIds = $sheet->selfChecks->pluck('id')->toArray();
            $totalItems += count($selfCheckIds);
            if (!empty($selfCheckIds)) {
                $completedItems += UserProgress::where('user_id', $userId)
                    ->where('module_id', $moduleId)
                    ->where('progressable_type', SelfCheck::class)
                    ->whereIn('progressable_id', $selfCheckIds)
                    ->whereIn('status', ['passed', 'completed'])
                    ->count();
            }

            // Task sheets
            $taskSheetIds = $sheet->taskSheets->pluck('id')->toArray();
            $totalItems += count($taskSheetIds);
            if (!empty($taskSheetIds)) {
                $completedItems += UserProgress::where('user_id', $userId)
                    ->where('module_id', $moduleId)
                    ->where('progressable_type', TaskSheet::class)
                    ->whereIn('progressable_id', $taskSheetIds)
                    ->where('status', 'completed')
                    ->count();
            }

            // Job sheets
            $jobSheetIds = $sheet->jobSheets->pluck('id')->toArray();
            $totalItems += count($jobSheetIds);
            if (!empty($jobSheetIds)) {
                $completedItems += UserProgress::where('user_id', $userId)
                    ->where('module_id', $moduleId)
                    ->where('progressable_type', JobSheet::class)
                    ->whereIn('progressable_id', $jobSheetIds)
                    ->where('status', 'completed')
                    ->count();
            }
        }

        // Calculate percentage based on individual items
        $percentage = $totalItems > 0 ? round(($completedItems / $totalItems) * 100) : 0;

        // Count completed sheets for display
        $sheetIds = $module->informationSheets->pluck('id')->toArray();
        $completedSheets = UserProgress::where('user_id', $userId)
            ->where('module_id', $moduleId)
            ->where('progressable_type', InformationSheet::class)
            ->whereIn('progressable_id', $sheetIds)
            ->where('status', 'completed')
            ->count();

        $status = match(true) {
            $percentage >= 100 => 'completed',
            $completedItems > 0 => 'in_progress',
            default => 'not_started',
        };

        return [
            'percentage' => $percentage,
            'status' => $status,
            'completed_sheets' => $completedSheets,
            'total_sheets' => $totalSheets,
            'completed_items' => $completedItems,
            'total_items' => $totalItems,
        ];
    }

    /**
     * Get overall progress summary for a user.
     */
    public function getOverallProgress(int $userId): array
    {
        $totalModules = Module::where('is_active', true)->count();

        $completedModules = UserProgress::where('user_id', $userId)
            ->where('progressable_type', Module::class)
            ->where('status', 'completed')
            ->count();

        $inProgressModules = UserProgress::where('user_id', $userId)
            ->where('progressable_type', Module::class)
            ->where('status', 'in_progress')
            ->count();

        $averageScore = UserProgress::where('user_id', $userId)
            ->whereNotNull('score')
            ->whereNotNull('max_score')
            ->where('max_score', '>', 0)
            ->selectRaw('AVG(score / max_score * 100) as avg_percentage')
            ->value('avg_percentage') ?? 0;

        $percentage = $totalModules > 0 ? round(($completedModules / $totalModules) * 100) : 0;

        return [
            'percentage' => $percentage,
            'completed_modules' => $completedModules,
            'in_progress_modules' => $inProgressModules,
            'total_modules' => $totalModules,
            'average_score' => round($averageScore, 1),
        ];
    }

    /**
     * Clear progress cache for a user.
     */
    public function clearProgressCache(int $userId): void
    {
        Cache::forget("dashboard_progress_{$userId}");
        Cache::forget("dashboard_admin_stats_{$userId}");
    }
}

<?php

namespace App\Services;

use App\Models\Module;
use App\Models\ModuleAssessmentSubmission;
use App\Models\SelfCheck;
use App\Models\SelfCheckQuestion;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ModuleAssessmentService
{
    protected SelfCheckGradingService $gradingService;

    public function __construct(SelfCheckGradingService $gradingService)
    {
        $this->gradingService = $gradingService;
    }

    /**
     * Get all available questions from the module's self-checks.
     */
    public function getAvailableQuestions(Module $module): Collection
    {
        $sources = $module->assessment_sources;
        $questions = collect();

        // Get all information sheets in the module
        $sheets = $module->informationSheets()->with(['selfChecks.questions'])->get();

        foreach ($sheets as $sheet) {
            // Include self-check questions
            if (in_array('self_check', $sources)) {
                foreach ($sheet->selfChecks as $selfCheck) {
                    foreach ($selfCheck->questions as $question) {
                        $questions->push([
                            'question' => $question,
                            'self_check' => $selfCheck,
                            'sheet' => $sheet,
                            'source_type' => 'self_check',
                        ]);
                    }
                }
            }

            // Future: Add support for other sources like task_sheet, job_sheet, homework
            // if (in_array('task_sheet', $sources)) { ... }
        }

        return $questions;
    }

    /**
     * Get questions for an assessment, applying randomization if enabled.
     */
    public function getAssessmentQuestions(Module $module, ?ModuleAssessmentSubmission $submission = null): Collection
    {
        $allQuestions = $this->getAvailableQuestions($module);

        // If resuming an existing submission, use the saved question order
        if ($submission && $submission->question_ids) {
            $questionIds = $submission->question_ids;
            return $allQuestions->filter(function ($item) use ($questionIds) {
                return in_array($item['question']->id, $questionIds);
            })->sortBy(function ($item) use ($questionIds) {
                return array_search($item['question']->id, $questionIds);
            })->values();
        }

        // Apply randomization if enabled
        if ($module->assessment_randomize_questions) {
            $allQuestions = $allQuestions->shuffle();
        }

        // Apply question count limit if using random subset mode
        if ($module->assessment_question_mode === 'random_subset' && $module->assessment_question_count) {
            $allQuestions = $allQuestions->take($module->assessment_question_count);
        }

        return $allQuestions->values();
    }

    /**
     * Check if a user can take the assessment.
     */
    public function canUserTakeAssessment(Module $module, User $user): array
    {
        $result = [
            'can_take' => false,
            'reason' => null,
            'details' => [],
        ];

        // Check if assessment is enabled
        if (!$module->require_final_assessment) {
            $result['reason'] = 'Assessment is not enabled for this module.';
            return $result;
        }

        // Check if already passed
        if ($module->hasPassedAssessment($user)) {
            $result['reason'] = 'You have already passed this assessment.';
            $result['details']['best_attempt'] = $module->getBestAssessmentFor($user);
            return $result;
        }

        // Check max attempts
        if ($module->assessment_max_attempts) {
            $attempts = $module->getAssessmentAttemptCount($user);
            if ($attempts >= $module->assessment_max_attempts) {
                $result['reason'] = 'You have reached the maximum number of attempts.';
                $result['details']['attempts_used'] = $attempts;
                $result['details']['max_attempts'] = $module->assessment_max_attempts;
                return $result;
            }
        }

        // Check if completion is required
        if ($module->assessment_require_completion) {
            $completionStatus = $this->getCompletionStatus($module, $user);
            if (!$completionStatus['completed']) {
                $result['reason'] = 'You must complete all activities before taking the assessment.';
                $result['details'] = $completionStatus;
                return $result;
            }
        }

        // Check for in-progress assessment
        $inProgress = $module->getInProgressAssessmentFor($user);
        if ($inProgress) {
            if ($inProgress->hasExpired()) {
                // Mark as timed out
                $inProgress->markAsTimedOut();
            } else {
                $result['can_take'] = true;
                $result['in_progress'] = $inProgress;
                return $result;
            }
        }

        $result['can_take'] = true;
        return $result;
    }

    /**
     * Get completion status for module activities.
     */
    public function getCompletionStatus(Module $module, User $user): array
    {
        $sheets = $module->informationSheets()->with(['selfChecks.submissions' => function ($q) use ($user) {
            $q->where('user_id', $user->id);
        }])->get();

        $totalSelfChecks = 0;
        $completedSelfChecks = 0;
        $pendingSelfChecks = [];

        foreach ($sheets as $sheet) {
            foreach ($sheet->selfChecks as $selfCheck) {
                $totalSelfChecks++;
                $submission = $selfCheck->submissions->first();

                if ($submission && $submission->status === 'completed') {
                    $completedSelfChecks++;
                } else {
                    $pendingSelfChecks[] = [
                        'self_check' => $selfCheck,
                        'sheet' => $sheet,
                    ];
                }
            }
        }

        return [
            'completed' => $completedSelfChecks === $totalSelfChecks && $totalSelfChecks > 0,
            'total' => $totalSelfChecks,
            'completed_count' => $completedSelfChecks,
            'pending' => $pendingSelfChecks,
            'percentage' => $totalSelfChecks > 0 ? round(($completedSelfChecks / $totalSelfChecks) * 100) : 0,
        ];
    }

    /**
     * Start a new assessment for a user.
     */
    public function startAssessment(Module $module, User $user): ModuleAssessmentSubmission
    {
        // Check for existing in-progress assessment
        $existing = $module->getInProgressAssessmentFor($user);
        if ($existing && !$existing->hasExpired()) {
            return $existing;
        }

        // If there's an expired one, mark it as timed out
        if ($existing) {
            $existing->markAsTimedOut();
        }

        // Get questions for this attempt
        $questions = $this->getAssessmentQuestions($module);
        $questionIds = $questions->pluck('question.id')->toArray();

        // Calculate total points
        $totalPoints = $questions->sum(function ($item) {
            return $item['question']->points ?? 1;
        });

        // Determine attempt number
        $attemptNumber = $module->getAssessmentAttemptCount($user) + 1;

        return ModuleAssessmentSubmission::create([
            'module_id' => $module->id,
            'user_id' => $user->id,
            'attempt_number' => $attemptNumber,
            'question_ids' => $questionIds,
            'total_points' => $totalPoints,
            'started_at' => Carbon::now(),
            'status' => 'in_progress',
        ]);
    }

    /**
     * Submit and grade an assessment.
     */
    public function submitAssessment(ModuleAssessmentSubmission $submission, array $answers): ModuleAssessmentSubmission
    {
        $module = $submission->module;
        $questions = $this->getAssessmentQuestions($module, $submission);

        $score = 0;
        $totalPoints = 0;
        $gradingDetails = [];

        foreach ($questions as $index => $item) {
            /** @var SelfCheckQuestion $question */
            $question = $item['question'];
            $points = $question->points ?? 1;
            $totalPoints += $points;

            $userAnswer = $answers[$question->id] ?? null;
            $gradingResult = $this->gradingService->gradeQuestion($question, $userAnswer);

            // Calculate points earned
            $pointsEarned = 0;
            if ($gradingResult === true) {
                $pointsEarned = $points;
            } elseif (is_float($gradingResult)) {
                $pointsEarned = round($points * $gradingResult, 2);
            }
            // null (manual grading) or false = 0 points

            $score += $pointsEarned;

            $gradingDetails[] = [
                'question_id' => $question->id,
                'question_text' => $question->question_text,
                'question_type' => $question->question_type,
                'user_answer' => $userAnswer,
                'correct_answer' => $question->correct_answer,
                'grading_result' => $gradingResult,
                'points_possible' => $points,
                'points_earned' => $pointsEarned,
                'is_correct' => $gradingResult === true,
                'is_partial' => is_float($gradingResult) && $gradingResult > 0,
                'requires_manual_grading' => $gradingResult === null,
            ];
        }

        // Save answers and complete the submission
        $submission->answers = $answers;
        $submission->complete($score, $totalPoints, $gradingDetails);

        return $submission->fresh();
    }

    /**
     * Get assessment statistics for a module.
     */
    public function getAssessmentStats(Module $module): array
    {
        $submissions = $module->assessmentSubmissions()
            ->where('status', 'completed')
            ->get();

        if ($submissions->isEmpty()) {
            return [
                'total_attempts' => 0,
                'unique_users' => 0,
                'pass_rate' => 0,
                'average_score' => 0,
                'highest_score' => 0,
                'lowest_score' => 0,
                'average_time' => 0,
            ];
        }

        $passedCount = $submissions->where('passed', true)->count();
        $times = $submissions->pluck('time_taken')->filter();

        return [
            'total_attempts' => $submissions->count(),
            'unique_users' => $submissions->pluck('user_id')->unique()->count(),
            'pass_rate' => round(($passedCount / $submissions->count()) * 100, 1),
            'average_score' => round($submissions->avg('percentage'), 1),
            'highest_score' => round($submissions->max('percentage'), 1),
            'lowest_score' => round($submissions->min('percentage'), 1),
            'average_time' => $times->isNotEmpty() ? round($times->avg()) : 0,
        ];
    }

    /**
     * Get user's assessment history for a module.
     */
    public function getUserAssessmentHistory(Module $module, User $user): Collection
    {
        return $module->assessmentSubmissions()
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->get();
    }
}

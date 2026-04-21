<?php

namespace App\Services;

use App\Models\CompetencyTest;
use App\Models\CompetencyTestQuestion;
use App\Models\CompetencyTestSubmission;
use App\Models\Module;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class CompetencyTestService
{
    protected SelfCheckGradingService $gradingService;

    public function __construct(SelfCheckGradingService $gradingService)
    {
        $this->gradingService = $gradingService;
    }

    /**
     * Check if a user can take the test.
     */
    public function canUserTakeTest(CompetencyTest $test, User $user): array
    {
        $result = [
            'can_take' => false,
            'reason' => null,
            'details' => [],
        ];

        if (!$test->is_active) {
            $result['reason'] = 'This test is not currently active.';
            return $result;
        }

        // Check if already passed
        if ($test->hasPassedBy($user)) {
            $result['reason'] = 'You have already passed this test.';
            $result['details']['best_attempt'] = $test->getBestSubmissionFor($user);
            return $result;
        }

        // Check max attempts
        if ($test->max_attempts) {
            $attempts = $test->getAttemptCountFor($user);
            if ($attempts >= $test->max_attempts) {
                $result['reason'] = 'You have reached the maximum number of attempts.';
                $result['details']['attempts_used'] = $attempts;
                $result['details']['max_attempts'] = $test->max_attempts;
                return $result;
            }
        }

        // Check for in-progress submission
        $inProgress = $this->getInProgressSubmission($test, $user);
        if ($inProgress) {
            if ($inProgress->hasExpired()) {
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
     * Get in-progress submission for user.
     */
    public function getInProgressSubmission(CompetencyTest $test, User $user): ?CompetencyTestSubmission
    {
        return $test->submissions()
            ->where('user_id', $user->id)
            ->where('status', 'in_progress')
            ->first();
    }

    /**
     * Start a new test submission.
     */
    public function startTest(CompetencyTest $test, User $user): CompetencyTestSubmission
    {
        // Check for existing in-progress
        $existing = $this->getInProgressSubmission($test, $user);
        if ($existing && !$existing->hasExpired()) {
            return $existing;
        }

        // Mark expired as timed out
        if ($existing) {
            $existing->markAsTimedOut();
        }

        // Get attempt number
        $attemptNumber = $test->getAttemptCountFor($user) + 1;

        return CompetencyTestSubmission::create([
            'competency_test_id' => $test->id,
            'user_id' => $user->id,
            'attempt_number' => $attemptNumber,
            'total_points' => $test->total_points,
            'started_at' => Carbon::now(),
            'status' => 'in_progress',
        ]);
    }

    /**
     * Submit and grade the test.
     */
    public function submitTest(CompetencyTestSubmission $submission, array $answers): CompetencyTestSubmission
    {
        $test = $submission->competencyTest;
        $questions = $test->questions;

        $score = 0;
        $totalPoints = 0;
        $gradingDetails = [];

        foreach ($questions as $question) {
            $points = $question->points ?? 1;
            $totalPoints += $points;

            $userAnswer = $answers[$question->id] ?? null;

            // Create a temporary object that matches SelfCheckQuestion interface
            $tempQuestion = new \stdClass();
            $tempQuestion->question_type = $question->question_type;
            $tempQuestion->correct_answer = $question->correct_answer;
            $tempQuestion->options = $question->options;

            // Use the grading service
            $gradingResult = $this->gradeQuestion($tempQuestion, $userAnswer);

            // Calculate points earned
            $pointsEarned = 0;
            if ($gradingResult === true) {
                $pointsEarned = $points;
            } elseif (is_float($gradingResult)) {
                $pointsEarned = round($points * $gradingResult, 2);
            }

            $score += $pointsEarned;

            $gradingDetails[] = [
                'question_id' => $question->id,
                'question_text' => $question->question_text,
                'question_type' => $question->question_type,
                'part_index' => $question->part_index,
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

        // Save and complete
        $submission->answers = $answers;
        $submission->complete($score, $totalPoints, $gradingDetails);

        return $submission->fresh();
    }

    /**
     * Grade a single question.
     */
    protected function gradeQuestion($question, $userAnswer)
    {
        if ($userAnswer === null || $userAnswer === '' || (is_array($userAnswer) && empty(array_filter($userAnswer, fn($v) => $v !== null && $v !== '')))) {
            return false;
        }

        switch ($question->question_type) {
            case 'multiple_choice':
            case 'image_choice':
                return (string) $userAnswer === (string) $question->correct_answer;

            case 'multiple_select':
                $correctAnswers = json_decode($question->correct_answer, true) ?? [];
                $userAnswers = is_array($userAnswer) ? array_map('intval', $userAnswer) : [];
                if (empty($correctAnswers)) return 0;
                $correctCount = count(array_intersect($userAnswers, $correctAnswers));
                $incorrectCount = count(array_diff($userAnswers, $correctAnswers));
                return max(0, ($correctCount - $incorrectCount) / count($correctAnswers));

            case 'true_false':
                return strtolower(trim($userAnswer)) === strtolower(trim($question->correct_answer));

            case 'fill_blank':
                $acceptableAnswers = array_map(fn($a) => strtolower(trim($a)), explode(',', $question->correct_answer));
                return in_array(strtolower(trim($userAnswer)), $acceptableAnswers);

            case 'short_answer':
                if (empty($question->correct_answer)) return null;
                $keywords = array_map('trim', explode(',', $question->correct_answer));
                $answerLower = strtolower($userAnswer);
                $matchedKeywords = 0;
                foreach ($keywords as $keyword) {
                    if (stripos($answerLower, strtolower($keyword)) !== false) {
                        $matchedKeywords++;
                    }
                }
                return count($keywords) > 0 ? $matchedKeywords / count($keywords) : false;

            case 'numeric':
            case 'slider':
                $correctValue = floatval($question->correct_answer);
                $userValue = floatval($userAnswer);
                $tolerance = floatval($question->options['tolerance'] ?? 0);
                return abs($correctValue - $userValue) <= $tolerance;

            case 'essay':
                if (empty($question->correct_answer) || $question->correct_answer === 'essay') return null;
                $keywords = array_filter(array_map('trim', explode(',', $question->correct_answer)));
                if (empty($keywords)) return null;
                $answerLower = strtolower($userAnswer);
                $matchedKeywords = 0;
                foreach ($keywords as $keyword) {
                    if (stripos($answerLower, strtolower($keyword)) !== false) {
                        $matchedKeywords++;
                    }
                }
                return $matchedKeywords / count($keywords);

            case 'matching':
                $pairs = $question->options['pairs'] ?? [];
                if (empty($pairs) || !is_array($userAnswer)) return 0;
                $correctCount = 0;
                foreach ($userAnswer as $leftIndex => $rightIndex) {
                    if ($leftIndex == $rightIndex) $correctCount++;
                }
                return count($pairs) > 0 ? $correctCount / count($pairs) : 0;

            case 'ordering':
                $correctOrder = $question->options['items'] ?? [];
                if (empty($correctOrder) || !is_array($userAnswer)) return 0;
                $correctCount = 0;
                for ($i = 0; $i < count($correctOrder); $i++) {
                    if (isset($userAnswer[$i]) && $userAnswer[$i] == $i) $correctCount++;
                }
                return count($correctOrder) > 0 ? $correctCount / count($correctOrder) : 0;

            default:
                return null;
        }
    }

    /**
     * Save progress without submitting.
     */
    public function saveProgress(CompetencyTestSubmission $submission, array $answers): void
    {
        $submission->update(['answers' => $answers]);
    }

    /**
     * Get test statistics.
     */
    public function getTestStats(CompetencyTest $test): array
    {
        $submissions = $test->submissions()->where('status', 'completed')->get();

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
     * Get user's test history.
     */
    public function getUserTestHistory(CompetencyTest $test, User $user): Collection
    {
        return $test->submissions()
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Create a new competency test.
     */
    public function createTest(Module $module, array $data): CompetencyTest
    {
        $test = CompetencyTest::create([
            'module_id' => $module->id,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'instructions' => $data['instructions'] ?? null,
            'time_limit' => $data['time_limit'] ?? null,
            'passing_score' => $data['passing_score'] ?? 70,
            'max_attempts' => $data['max_attempts'] ?? null,
            'reveal_answers' => $data['reveal_answers'] ?? true,
            'randomize_questions' => $data['randomize_questions'] ?? false,
            'randomize_options' => $data['randomize_options'] ?? false,
            'parts' => $data['parts'] ?? null,
            'is_active' => $data['is_active'] ?? true,
        ]);

        return $test;
    }

    /**
     * Add questions to a test.
     */
    public function addQuestions(CompetencyTest $test, array $questions): void
    {
        $order = $test->questions()->max('order') ?? 0;

        foreach ($questions as $questionData) {
            $order++;
            CompetencyTestQuestion::create([
                'competency_test_id' => $test->id,
                'question_text' => $questionData['question_text'],
                'question_type' => $questionData['question_type'] ?? 'multiple_choice',
                'points' => $questionData['points'] ?? 1,
                'options' => $questionData['options'] ?? null,
                'correct_answer' => $questionData['correct_answer'] ?? null,
                'explanation' => $questionData['explanation'] ?? null,
                'order' => $order,
                'part_index' => $questionData['part_index'] ?? null,
            ]);
        }

        $test->recalculateTotalPoints();
    }
}

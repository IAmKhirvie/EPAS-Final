<?php

namespace App\Services;

use App\Models\SelfCheckQuestion;

/**
 * SelfCheckGradingService
 *
 * Handles grading logic for self-check quiz submissions.
 * Supports all question types: multiple choice, multiple select, true/false,
 * fill-in-the-blank, short answer, numeric, matching, ordering, classification,
 * image choice, image identification, hotspot, image labeling, audio/video
 * questions, drag & drop, and slider.
 *
 * Grading returns:
 * - true: fully correct
 * - false: incorrect
 * - float (0.0-1.0): partial credit
 * - null: requires manual grading
 */
class SelfCheckGradingService
{
    /**
     * Calculate total score for a set of graded results.
     *
     * @param  array  $results  Array of ['question' => SelfCheckQuestion, 'grading_result' => bool|float|null]
     * @return float
     */
    public function calculateScore(array $results): float
    {
        $score = 0;

        foreach ($results as $result) {
            $score += $result['points_earned'] ?? 0;
        }

        return $score;
    }

    /**
     * Calculate percentage from score and total points.
     *
     * @param  float  $score
     * @param  float  $totalPoints
     * @return float
     */
    public function calculatePercentage(float $score, float $totalPoints): float
    {
        return $totalPoints > 0 ? ($score / $totalPoints) * 100 : 0;
    }

    /**
     * Determine if a percentage meets the passing threshold.
     *
     * @param  float       $percentage
     * @param  int|null    $passingScore  Per-quiz override; falls back to config default.
     * @return bool
     */
    public function isPassing(float $percentage, ?int $passingScore = null): bool
    {
        return $percentage >= ($passingScore ?? config('joms.grading.default_passing_score', 70));
    }

    /**
     * Grade a single question based on its type.
     *
     * @param  SelfCheckQuestion  $question
     * @param  mixed              $userAnswer
     * @return bool|float|null  true if correct, false if wrong, float for partial credit (0.0-1.0), null for manual grading
     */
    public function gradeQuestion(SelfCheckQuestion $question, $userAnswer)
    {
        if ($userAnswer === null || $userAnswer === '' || (is_array($userAnswer) && empty(array_filter($userAnswer, fn($v) => $v !== null && $v !== '')))) {
            return false;
        }

        switch ($question->question_type) {
            case 'multiple_choice':
            case 'image_choice':
                // Both user answer and correct_answer are stored as indices
                return strtolower(trim((string) $userAnswer)) === strtolower(trim((string) $question->correct_answer));

            case 'multiple_select':
                return $this->gradeMultipleSelectQuestion($question, $userAnswer);

            case 'true_false':
                $userBool = strtolower(trim($userAnswer));
                $correctBool = strtolower(trim($question->correct_answer));
                return $userBool === $correctBool;

            case 'identification':
            case 'fill_blank':
                // Check if answer matches any acceptable answer (case-insensitive)
                $acceptableAnswers = array_map(
                    fn($a) => strtolower(trim($a)),
                    explode(',', $question->correct_answer)
                );
                $userLower = strtolower(trim($userAnswer));

                // Direct match
                if (in_array($userLower, $acceptableAnswers)) {
                    return true;
                }

                // Grouped question support: if question text contains patterns like
                // "Part 1", "Part 2", "Type 1", "Source 1", "Category 1", "Advantage 1"
                // then accept any answer from sibling questions in the same group
                if (preg_match('/\((Part|Type|Source|Category|Advantage|Item)\s*\d+\)/i', $question->question_text)) {
                    // Extract base question text without the (Part X) suffix
                    $baseText = preg_replace('/\s*\((Part|Type|Source|Category|Advantage|Item)\s*\d+\)\s*$/i', '', $question->question_text);
                    $baseText = trim($baseText);

                    // Find all sibling questions with same base text
                    $siblings = $question->selfCheck->questions()
                        ->where('id', '!=', $question->id)
                        ->where('question_text', 'like', $baseText . '%')
                        ->pluck('correct_answer')
                        ->toArray();

                    // Collect all valid answers from the group
                    $allGroupAnswers = $acceptableAnswers;
                    foreach ($siblings as $sibAnswer) {
                        foreach (explode(',', $sibAnswer) as $a) {
                            $allGroupAnswers[] = strtolower(trim($a));
                        }
                    }

                    return in_array($userLower, array_unique($allGroupAnswers));
                }

                return false;

            case 'enumeration':
                // User provides multiple answers (newline or comma separated)
                // Order does NOT matter — grade by how many correct items they listed
                $correctItems = array_map(fn($a) => strtolower(trim($a)), explode(',', $question->correct_answer));
                $correctItems = array_values(array_filter($correctItems));
                if (empty($correctItems)) return null;

                // Parse user's answers (newline or comma separated)
                $userItems = preg_split('/[\n,]+/', (string) $userAnswer);
                $userItems = array_map(fn($a) => strtolower(trim($a)), $userItems);
                $userItems = array_values(array_filter($userItems));

                $matched = 0;
                $usedCorrect = [];
                foreach ($userItems as $user) {
                    foreach ($correctItems as $cIdx => $correct) {
                        if (in_array($cIdx, $usedCorrect)) continue;
                        if ($user === $correct || str_contains($user, $correct) || str_contains($correct, $user)) {
                            $matched++;
                            $usedCorrect[] = $cIdx;
                            break;
                        }
                    }
                }
                return count($correctItems) > 0 ? $matched / count($correctItems) : 0;

            case 'short_answer':
                // Check for keywords if provided
                if (empty($question->correct_answer)) {
                    return null; // Manual grading required
                }
                $keywords = array_map('trim', explode(',', $question->correct_answer));
                $answerLower = strtolower($userAnswer);
                $matchedKeywords = 0;
                foreach ($keywords as $keyword) {
                    if (stripos($answerLower, strtolower($keyword)) !== false) {
                        $matchedKeywords++;
                    }
                }
                // Return partial credit based on keyword matches
                return count($keywords) > 0 ? $matchedKeywords / count($keywords) : false;

            case 'numeric':
                return $this->gradeNumericQuestion($question, $userAnswer);

            case 'slider':
                return $this->gradeSliderQuestion($question, $userAnswer);

            case 'matching':
                return $this->gradeMatchingQuestion($question, $userAnswer);

            case 'ordering':
                return $this->gradeOrderingQuestion($question, $userAnswer);

            case 'classification':
                return $this->gradeClassificationQuestion($question, $userAnswer);

            case 'image_identification':
                return $this->gradeImageIdentificationQuestion($question, $userAnswer);

            case 'hotspot':
                return $this->gradeHotspotQuestion($question, $userAnswer);

            case 'image_labeling':
                return $this->gradeLabelingQuestion($question, $userAnswer);

            case 'audio_question':
            case 'video_question':
                return $this->gradeMediaQuestion($question, $userAnswer);

            case 'drag_drop':
                return $this->gradeDragDropQuestion($question, $userAnswer);

            case 'essay':
                // Essays require manual grading; keyword matching if correct_answer has keywords
                if (empty($question->correct_answer) || $question->correct_answer === 'essay') {
                    return null;
                }
                $keywords = array_map('trim', explode(',', $question->correct_answer));
                $keywords = array_filter($keywords);
                if (empty($keywords)) {
                    return null;
                }
                $answerLower = strtolower($userAnswer);
                $matchedKeywords = 0;
                foreach ($keywords as $keyword) {
                    if (stripos($answerLower, strtolower($keyword)) !== false) {
                        $matchedKeywords++;
                    }
                }
                return $matchedKeywords / count($keywords);

            default:
                return null; // Unknown types require manual grading
        }
    }

    /**
     * Grade a matching question (Column A to Column B).
     */
    protected function gradeMatchingQuestion(SelfCheckQuestion $question, $userAnswer): float
    {
        $options = $question->options;
        $pairs = $options['pairs'] ?? [];

        if (empty($pairs) || !is_array($userAnswer)) {
            return 0;
        }

        // Build correct mapping (left -> right)
        $correctMapping = [];
        foreach ($pairs as $index => $pair) {
            $correctMapping[$index] = $index; // By default, pairs are aligned
        }

        // Count correct matches
        $correctCount = 0;
        foreach ($userAnswer as $leftIndex => $rightIndex) {
            if (isset($correctMapping[$leftIndex]) && $correctMapping[$leftIndex] == $rightIndex) {
                $correctCount++;
            }
        }

        return count($pairs) > 0 ? $correctCount / count($pairs) : 0;
    }

    /**
     * Grade an ordering question.
     */
    protected function gradeOrderingQuestion(SelfCheckQuestion $question, $userAnswer): float
    {
        $options = $question->options;
        $correctOrder = $options['items'] ?? [];

        if (empty($correctOrder) || !is_array($userAnswer)) {
            return 0;
        }

        // Count items in correct position
        $correctCount = 0;
        for ($i = 0; $i < count($correctOrder); $i++) {
            if (isset($userAnswer[$i]) && $userAnswer[$i] == $i) {
                $correctCount++;
            }
        }

        return count($correctOrder) > 0 ? $correctCount / count($correctOrder) : 0;
    }

    /**
     * Grade a multiple select question (checkboxes).
     * Returns partial credit: (correct - incorrect) / total, minimum 0
     */
    protected function gradeMultipleSelectQuestion(SelfCheckQuestion $question, $userAnswer): float
    {
        $correctAnswers = json_decode($question->correct_answer, true) ?? [];
        $userAnswers = is_array($userAnswer) ? array_map('intval', $userAnswer) : [];

        if (empty($correctAnswers)) {
            return 0;
        }

        // Calculate correct and incorrect selections
        $correctCount = count(array_intersect($userAnswers, $correctAnswers));
        $incorrectCount = count(array_diff($userAnswers, $correctAnswers));

        // Score formula: correct selections minus incorrect, divided by total correct
        $score = ($correctCount - $incorrectCount) / count($correctAnswers);
        return max(0, $score); // Minimum score is 0
    }

    /**
     * Grade a numeric question with tolerance.
     */
    protected function gradeNumericQuestion(SelfCheckQuestion $question, $userAnswer): bool
    {
        $correctValue = floatval($question->correct_answer);
        $userValue = floatval($userAnswer);
        $tolerance = floatval($question->options['tolerance'] ?? 0);

        return abs($correctValue - $userValue) <= $tolerance;
    }

    /**
     * Grade a slider question with tolerance.
     */
    protected function gradeSliderQuestion(SelfCheckQuestion $question, $userAnswer): bool
    {
        $correctValue = floatval($question->correct_answer);
        $userValue = floatval($userAnswer);
        $tolerance = floatval($question->options['tolerance'] ?? 0);

        return abs($correctValue - $userValue) <= $tolerance;
    }

    /**
     * Grade a classification question.
     * Returns partial credit based on correctly categorized items.
     */
    protected function gradeClassificationQuestion(SelfCheckQuestion $question, $userAnswer): float
    {
        $correctMapping = $question->options['item_categories'] ?? [];

        if (empty($correctMapping) || !is_array($userAnswer)) {
            return 0;
        }

        $correctCount = 0;
        $total = count($correctMapping);

        foreach ($correctMapping as $itemIndex => $correctCategory) {
            if (isset($userAnswer[$itemIndex]) && (string) $userAnswer[$itemIndex] === (string) $correctCategory) {
                $correctCount++;
            }
        }

        return $total > 0 ? $correctCount / $total : 0;
    }

    /**
     * Grade an image identification question.
     * Check if user answer matches any acceptable answer.
     */
    protected function gradeImageIdentificationQuestion(SelfCheckQuestion $question, $userAnswer): bool
    {
        $acceptableAnswers = $question->options['acceptable_answers'] ?? [];

        // Also check correct_answer field for backwards compatibility
        if (!empty($question->correct_answer)) {
            $additionalAnswers = array_map('trim', explode(',', $question->correct_answer));
            $acceptableAnswers = array_merge($acceptableAnswers, $additionalAnswers);
        }

        if (empty($acceptableAnswers)) {
            return false;
        }

        $normalizedAcceptable = array_map(fn($a) => strtolower(trim($a)), $acceptableAnswers);
        $normalizedUser = strtolower(trim($userAnswer));

        return in_array($normalizedUser, $normalizedAcceptable);
    }

    /**
     * Grade a hotspot question.
     * Check if user click is within the correct radius.
     */
    protected function gradeHotspotQuestion(SelfCheckQuestion $question, $userAnswer): bool
    {
        if (!is_array($userAnswer) || !isset($userAnswer['x'], $userAnswer['y'])) {
            return false;
        }

        $correctX = floatval($question->options['hotspot_x'] ?? 50);
        $correctY = floatval($question->options['hotspot_y'] ?? 50);
        $radius = floatval($question->options['hotspot_radius'] ?? 10);

        $userX = floatval($userAnswer['x']);
        $userY = floatval($userAnswer['y']);

        // Calculate Euclidean distance from center
        $distance = sqrt(pow($userX - $correctX, 2) + pow($userY - $correctY, 2));

        return $distance <= $radius;
    }

    /**
     * Grade an image labeling question.
     * Returns partial credit based on correctly labeled parts.
     */
    protected function gradeLabelingQuestion(SelfCheckQuestion $question, $userAnswer): float
    {
        $correctLabels = $question->options['labels'] ?? [];

        if (empty($correctLabels) || !is_array($userAnswer)) {
            return 0;
        }

        $correctCount = 0;
        $total = count($correctLabels);

        foreach ($correctLabels as $index => $correctLabel) {
            if (isset($userAnswer[$index])) {
                $userLabel = strtolower(trim($userAnswer[$index]));
                $correct = strtolower(trim($correctLabel));
                if ($userLabel === $correct) {
                    $correctCount++;
                }
            }
        }

        return $total > 0 ? $correctCount / $total : 0;
    }

    /**
     * Grade an audio or video question.
     * Grading depends on the response type (text or multiple_choice).
     */
    protected function gradeMediaQuestion(SelfCheckQuestion $question, $userAnswer)
    {
        $responseType = $question->options['response_type'] ?? 'text';

        if ($responseType === 'multiple_choice') {
            // Grade as multiple choice
            return (string) $userAnswer === (string) $question->correct_answer;
        }

        // Text response - grade with keyword matching like short_answer
        if (empty($question->correct_answer)) {
            return null; // Manual grading required
        }

        $keywords = array_map('trim', explode(',', $question->correct_answer));
        $answerLower = strtolower($userAnswer);
        $matchedKeywords = 0;

        foreach ($keywords as $keyword) {
            if (!empty($keyword) && stripos($answerLower, strtolower($keyword)) !== false) {
                $matchedKeywords++;
            }
        }

        return count($keywords) > 0 ? $matchedKeywords / count($keywords) : false;
    }

    /**
     * Grade a drag and drop question.
     * Returns partial credit based on correct placements.
     */
    protected function gradeDragDropQuestion(SelfCheckQuestion $question, $userAnswer): float
    {
        $correctMapping = $question->options['correct_mapping'] ?? [];

        if (empty($correctMapping) || !is_array($userAnswer)) {
            return 0;
        }

        $correctCount = 0;
        $total = count($correctMapping);

        foreach ($correctMapping as $dropzoneIndex => $correctDraggable) {
            if (isset($userAnswer[$dropzoneIndex]) && (string) $userAnswer[$dropzoneIndex] === (string) $correctDraggable) {
                $correctCount++;
            }
        }

        return $total > 0 ? $correctCount / $total : 0;
    }
}

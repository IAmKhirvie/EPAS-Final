<?php

namespace App\Services;

use App\Models\Module;
use App\Models\InformationSheet;
use App\Models\SelfCheck;
use App\Models\Topic;
use App\Models\UserProgress;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ModuleService
{
    public function __construct(private ?ProgressTrackingService $progressService = null)
    {
        $this->progressService = $progressService ?? app(ProgressTrackingService::class);
    }

    /**
     * Track topic progress for the authenticated user.
     */
    public function trackTopicProgress(Topic $topic): void
    {
        if (!$topic->informationSheet || !$topic->informationSheet->module_id) {
            return;
        }

        $userId = auth()->id();
        $sheet = $topic->informationSheet;

        // Record topic as viewed/completed
        $this->progressService->recordTopicViewed(
            $topic->id,
            $sheet->id,
            $sheet->module_id,
            $userId
        );

        // Check if all topics in sheet are viewed and sheet should be completed
        $this->progressService->checkAndUpdateSheetCompletion($sheet, $userId);
    }

    /**
     * Calculate score for a self-check submission.
     */
    public function calculateScore(SelfCheck $selfCheck, array $answers): int
    {
        $correctAnswers = json_decode($selfCheck->answer_key, true) ?? [];
        $score = 0;

        foreach ($answers as $questionId => $userAnswer) {
            if (isset($correctAnswers[$questionId])) {
                $correctAnswer = $correctAnswers[$questionId];

                if (is_array($correctAnswer)) {
                    if (in_array($userAnswer, $correctAnswer)) {
                        $score++;
                    }
                } else {
                    if ($correctAnswer === $userAnswer) {
                        $score++;
                    }
                }
            }
        }

        return $score;
    }

    /**
     * Get maximum possible score for a self-check.
     */
    public function getMaxScore(SelfCheck $selfCheck): int
    {
        $correctAnswers = json_decode($selfCheck->answer_key, true) ?? [];
        return count($correctAnswers);
    }

    /**
     * Get module progress for a user.
     */
    public function getProgress(Module $module, int $userId): array
    {
        $progress = $this->progressService->getModuleProgress($module->id, $userId);

        // Provide defaults for missing keys to avoid "undefined array key" errors
        return [
            'percentage'      => $progress['percentage'] ?? 0,
            'completed'       => $progress['completed_sheets'] ?? 0,
            'total'           => $progress['total_sheets'] ?? 0,
            'completed_items' => $progress['completed_items'] ?? 0,
            'total_items'     => $progress['total_items'] ?? 0,
            'status'          => $progress['status'] ?? 'not_started',
        ];
    }

    /**
     * Upload an image for a module.
     */
    public function uploadImage(Module $module, $imageFile, ?string $caption = null, ?string $section = null): array
    {
        $imageName = 'module_' . $module->id . '_' . time() . '.' . $imageFile->extension();
        $imageFile->storeAs('module-images', $imageName, 'public');

        $images = $module->images ?? [];
        $images[] = [
            'filename' => $imageName,
            'url' => asset('storage/module-images/' . $imageName),
            'caption' => $caption,
            'section' => $section ?? 'overview',
            'uploaded_at' => now()->toIso8601String(),
        ];

        $module->update(['images' => $images]);

        return $images;
    }

    /**
     * Delete an image from a module.
     */
    public function deleteImage(Module $module, int $imageIndex): void
    {
        $images = $module->images ?? [];

        if (isset($images[$imageIndex])) {
            $filename = $images[$imageIndex]['filename'] ?? null;
            if ($filename) {
                Storage::disk('public')->delete('module-images/' . $filename);
            }
            array_splice($images, $imageIndex, 1);
            $module->update(['images' => $images]);
        }
    }
}

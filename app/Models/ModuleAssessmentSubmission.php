<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class ModuleAssessmentSubmission extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'module_id',
        'user_id',
        'attempt_number',
        'score',
        'total_points',
        'percentage',
        'passed',
        'answers',
        'question_ids',
        'grading_details',
        'time_taken',
        'started_at',
        'completed_at',
        'status',
    ];

    protected $casts = [
        'passed' => 'boolean',
        'answers' => 'array',
        'question_ids' => 'array',
        'grading_details' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Get the module this submission belongs to.
     */
    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    /**
     * Get the user who made this submission.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if the submission is still in progress.
     */
    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    /**
     * Check if the submission is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if the assessment timed out.
     */
    public function isTimedOut(): bool
    {
        return $this->status === 'timed_out';
    }

    /**
     * Check if the assessment has expired (time limit exceeded).
     */
    public function hasExpired(): bool
    {
        if (!$this->isInProgress() || !$this->started_at) {
            return false;
        }

        $timeLimit = $this->module->assessment_time_limit;
        if (!$timeLimit) {
            return false;
        }

        $expiresAt = $this->started_at->addMinutes($timeLimit);
        return Carbon::now()->greaterThan($expiresAt);
    }

    /**
     * Get remaining time in seconds.
     */
    public function getRemainingTimeAttribute(): ?int
    {
        if (!$this->isInProgress() || !$this->started_at) {
            return null;
        }

        $timeLimit = $this->module->assessment_time_limit;
        if (!$timeLimit) {
            return null; // No time limit
        }

        $expiresAt = $this->started_at->addMinutes($timeLimit);
        $remaining = Carbon::now()->diffInSeconds($expiresAt, false);

        return max(0, $remaining);
    }

    /**
     * Get formatted time taken.
     */
    public function getFormattedTimeTakenAttribute(): string
    {
        if (!$this->time_taken) {
            return 'N/A';
        }

        $minutes = floor($this->time_taken / 60);
        $seconds = $this->time_taken % 60;

        if ($minutes > 0) {
            return sprintf('%d min %d sec', $minutes, $seconds);
        }

        return sprintf('%d sec', $seconds);
    }

    /**
     * Get the grade letter based on percentage.
     */
    public function getGradeLetterAttribute(): string
    {
        $percentage = $this->percentage;

        if ($percentage >= 90) return 'A';
        if ($percentage >= 80) return 'B';
        if ($percentage >= 70) return 'C';
        if ($percentage >= 60) return 'D';
        return 'F';
    }

    /**
     * Complete the assessment submission.
     */
    public function complete(int $score, int $totalPoints, array $gradingDetails = []): void
    {
        $percentage = $totalPoints > 0 ? round(($score / $totalPoints) * 100, 2) : 0;
        $passingScore = $this->module->assessment_passing_score ?? 70;

        $this->update([
            'score' => $score,
            'total_points' => $totalPoints,
            'percentage' => $percentage,
            'passed' => $percentage >= $passingScore,
            'grading_details' => $gradingDetails,
            'completed_at' => Carbon::now(),
            'time_taken' => $this->started_at ? Carbon::now()->diffInSeconds($this->started_at) : null,
            'status' => 'completed',
        ]);
    }

    /**
     * Mark the assessment as timed out.
     */
    public function markAsTimedOut(): void
    {
        $this->update([
            'status' => 'timed_out',
            'completed_at' => Carbon::now(),
            'time_taken' => $this->started_at ? Carbon::now()->diffInSeconds($this->started_at) : null,
        ]);
    }

    /**
     * Scope for completed submissions.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for passed submissions.
     */
    public function scopePassed($query)
    {
        return $query->where('passed', true)->where('status', 'completed');
    }

    /**
     * Scope for a specific user.
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}

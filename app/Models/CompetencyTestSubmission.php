<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompetencyTestSubmission extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'competency_test_id',
        'user_id',
        'attempt_number',
        'score',
        'total_points',
        'percentage',
        'passed',
        'answers',
        'grading_details',
        'time_taken',
        'started_at',
        'completed_at',
        'status',
    ];

    protected $casts = [
        'passed' => 'boolean',
        'answers' => 'array',
        'grading_details' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function competencyTest(): BelongsTo
    {
        return $this->belongsTo(CompetencyTest::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if in progress.
     */
    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    /**
     * Check if completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if timed out.
     */
    public function isTimedOut(): bool
    {
        return $this->status === 'timed_out';
    }

    /**
     * Check if expired.
     */
    public function hasExpired(): bool
    {
        if (!$this->isInProgress() || !$this->started_at) {
            return false;
        }

        $timeLimit = $this->competencyTest->time_limit;
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

        $timeLimit = $this->competencyTest->time_limit;
        if (!$timeLimit) {
            return null;
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
     * Get grade letter.
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
     * Complete the submission.
     */
    public function complete(int $score, int $totalPoints, array $gradingDetails = []): void
    {
        $percentage = $totalPoints > 0 ? round(($score / $totalPoints) * 100, 2) : 0;
        $passingScore = $this->competencyTest->passing_score ?? 70;

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
     * Mark as timed out.
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
}

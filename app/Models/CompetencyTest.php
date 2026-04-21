<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompetencyTest extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'module_id',
        'title',
        'description',
        'instructions',
        'time_limit',
        'due_date',
        'passing_score',
        'total_points',
        'is_active',
        'max_attempts',
        'reveal_answers',
        'randomize_questions',
        'randomize_options',
        'parts',
        'order',
    ];

    protected $casts = [
        'time_limit' => 'integer',
        'due_date' => 'datetime',
        'passing_score' => 'integer',
        'total_points' => 'integer',
        'is_active' => 'boolean',
        'max_attempts' => 'integer',
        'reveal_answers' => 'boolean',
        'randomize_questions' => 'boolean',
        'randomize_options' => 'boolean',
        'parts' => 'array',
    ];

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(CompetencyTestQuestion::class)->orderBy('order');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(CompetencyTestSubmission::class);
    }

    public function getQuestionCountAttribute(): int
    {
        return $this->questions()->count();
    }

    public function getAverageScoreAttribute(): ?float
    {
        $submissions = $this->submissions()->whereNotNull('percentage')->get();
        if ($submissions->isEmpty()) {
            return null;
        }
        return $submissions->avg('percentage');
    }

    /**
     * Get parts with their questions.
     */
    public function getPartsWithQuestionsAttribute(): array
    {
        $parts = $this->parts ?? [];
        $questions = $this->questions;

        $result = [];
        foreach ($parts as $index => $part) {
            $partQuestions = $questions->where('part_index', $index)->values();
            $result[] = [
                'index' => $index,
                'name' => $part['name'] ?? 'Part ' . ($index + 1),
                'instructions' => $part['instructions'] ?? null,
                'questions' => $partQuestions,
                'question_count' => $partQuestions->count(),
            ];
        }

        // Include questions without a part
        $unassigned = $questions->whereNull('part_index')->values();
        if ($unassigned->isNotEmpty() && empty($parts)) {
            $result[] = [
                'index' => null,
                'name' => 'Questions',
                'instructions' => null,
                'questions' => $unassigned,
                'question_count' => $unassigned->count(),
            ];
        }

        return $result;
    }

    /**
     * Get questions with optional randomization.
     */
    public function getRandomizedQuestions(?int $userId = null): \Illuminate\Support\Collection
    {
        $questions = $this->questions;

        if ($this->randomize_questions && $userId) {
            // Group by part and randomize within each part
            $parts = $this->parts ?? [];
            if (!empty($parts)) {
                $randomized = collect();
                foreach ($parts as $index => $part) {
                    $partQuestions = $questions->where('part_index', $index);
                    $seed = $userId + $this->id + $index;
                    $randomized = $randomized->merge($partQuestions->shuffle($seed));
                }
                // Add unassigned questions
                $unassigned = $questions->whereNull('part_index');
                if ($unassigned->isNotEmpty()) {
                    $seed = $userId + $this->id + 999;
                    $randomized = $randomized->merge($unassigned->shuffle($seed));
                }
                $questions = $randomized;
            } else {
                $seed = $userId + $this->id;
                $questions = $questions->shuffle($seed);
            }
        }

        // Randomize options if enabled
        if ($this->randomize_options && $userId) {
            $questions = $questions->map(function ($question) use ($userId) {
                if ($question->options && is_array($question->options) && count($question->options) > 0) {
                    $seed = $userId + $question->id;
                    srand($seed);
                    $options = $question->options;
                    shuffle($options);
                    $question->setAttribute('randomized_options', $options);
                }
                return $question;
            });
        }

        return $questions;
    }

    /**
     * Check if any randomization is enabled.
     */
    public function hasRandomization(): bool
    {
        return $this->randomize_questions || $this->randomize_options;
    }

    /**
     * Get user's submission for this test.
     */
    public function getSubmissionFor(User $user): ?CompetencyTestSubmission
    {
        return $this->submissions()
            ->where('user_id', $user->id)
            ->latest()
            ->first();
    }

    /**
     * Get user's best submission.
     */
    public function getBestSubmissionFor(User $user): ?CompetencyTestSubmission
    {
        return $this->submissions()
            ->where('user_id', $user->id)
            ->where('status', 'completed')
            ->orderByDesc('percentage')
            ->first();
    }

    /**
     * Check if user has passed this test.
     */
    public function hasPassedBy(User $user): bool
    {
        return $this->submissions()
            ->where('user_id', $user->id)
            ->where('passed', true)
            ->where('status', 'completed')
            ->exists();
    }

    /**
     * Get attempt count for user.
     */
    public function getAttemptCountFor(User $user): int
    {
        return $this->submissions()
            ->where('user_id', $user->id)
            ->where('status', 'completed')
            ->count();
    }

    /**
     * Check if user can take this test.
     */
    public function canBeTakenBy(User $user): bool
    {
        if (!$this->is_active) {
            return false;
        }

        // Check max attempts
        if ($this->max_attempts) {
            $attempts = $this->getAttemptCountFor($user);
            if ($attempts >= $this->max_attempts) {
                return false;
            }
        }

        // Check if already passed
        if ($this->hasPassedBy($user)) {
            return false;
        }

        return true;
    }

    /**
     * Recalculate total points from questions.
     */
    public function recalculateTotalPoints(): void
    {
        $total = $this->questions()->sum('points');
        $this->update(['total_points' => $total]);
    }
}

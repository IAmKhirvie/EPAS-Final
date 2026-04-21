<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SelfCheck extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'information_sheet_id',
        'check_number',
        'title',
        'description',
        'instructions',
        'file_path',
        'original_filename',
        'document_content',
        'time_limit',
        'due_date',
        'passing_score',
        'total_points',
        'is_active',
        'is_required',
        'max_attempts',
        'reveal_answers',
        'randomize_questions',
        'randomize_options',
        'parts',
    ];

    protected $casts = [
        'time_limit' => 'integer',
        'due_date' => 'datetime',
        'passing_score' => 'integer',
        'total_points' => 'integer',
        'is_active' => 'boolean',
        'is_required' => 'boolean',
        'max_attempts' => 'integer',
        'reveal_answers' => 'boolean',
        'randomize_questions' => 'boolean',
        'randomize_options' => 'boolean',
        'parts' => 'array',
    ];

    public function informationSheet(): BelongsTo
    {
        return $this->belongsTo(InformationSheet::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(SelfCheckQuestion::class)->orderBy('order');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(SelfCheckSubmission::class);
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

    public function getCompletionRateAttribute(): ?float
    {
        $totalStudents = User::where('role', \App\Constants\Roles::STUDENT)
            ->where('stat', 1)
            ->count();
        $completedUsers = $this->submissions()->distinct('user_id')->count();

        return $totalStudents > 0 ? ($completedUsers / $totalStudents) * 100 : 0;
    }

    /**
     * Get questions with optional randomization.
     * Randomization is seeded per user to ensure consistency within a session.
     */
    public function getRandomizedQuestions(?int $userId = null): \Illuminate\Support\Collection
    {
        $questions = $this->questions()->with('options')->get();

        if ($this->randomize_questions && $userId) {
            // Seed the randomizer with user ID + self check ID for consistent randomization per user
            $seed = $userId + $this->id;
            $questions = $questions->shuffle($seed);
        }

        // Randomize options if enabled
        if ($this->randomize_options && $userId) {
            $questions = $questions->map(function ($question) use ($userId) {
                if ($question->options && $question->options->count() > 0) {
                    $seed = $userId + $question->id;
                    $question->setRelation('options', $question->options->shuffle($seed));
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
}

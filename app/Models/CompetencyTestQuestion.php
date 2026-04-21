<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompetencyTestQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'competency_test_id',
        'question_text',
        'question_type',
        'points',
        'options',
        'correct_answer',
        'explanation',
        'order',
        'part_index',
    ];

    protected $casts = [
        'points' => 'integer',
        'order' => 'integer',
        'part_index' => 'integer',
        'options' => 'array',
    ];

    public function competencyTest(): BelongsTo
    {
        return $this->belongsTo(CompetencyTest::class);
    }

    /**
     * Get the part this question belongs to.
     */
    public function getPartAttribute(): ?array
    {
        if ($this->part_index === null) {
            return null;
        }

        $parts = $this->competencyTest->parts ?? [];
        return $parts[$this->part_index] ?? null;
    }

    /**
     * Get formatted options (A, B, C, D).
     */
    public function getFormattedOptionsAttribute(): array
    {
        $options = $this->options ?? [];
        $formatted = [];
        foreach ($options as $index => $option) {
            $letter = chr(65 + $index); // A, B, C, ...
            $formatted[$letter] = is_string($option) ? $option : ($option['text'] ?? $option);
        }
        return $formatted;
    }

    /**
     * Get correct answer formatted.
     */
    public function getCorrectAnswerFormattedAttribute(): string
    {
        if (in_array($this->question_type, ['multiple_choice', 'true_false'])) {
            $options = $this->formatted_options;
            return $options[$this->correct_answer] ?? $this->correct_answer ?? '';
        }

        return $this->correct_answer ?? '';
    }
}

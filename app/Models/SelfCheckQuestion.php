<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SelfCheckQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'self_check_id',
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

    public function selfCheck(): BelongsTo
    {
        return $this->belongsTo(SelfCheck::class);
    }

    public function questionOptions(): HasMany
    {
        return $this->hasMany(SelfCheckQuestionOption::class, 'question_id')->orderBy('order');
    }

    public function submissionAnswers(): HasMany
    {
        return $this->hasMany(SelfCheckSubmissionAnswer::class);
    }

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

    public function getCorrectAnswerFormattedAttribute(): string
    {
        if ($this->question_type === 'multiple_choice') {
            $options = $this->formatted_options;
            return $options[$this->correct_answer] ?? $this->correct_answer;
        }

        return $this->correct_answer;
    }
}

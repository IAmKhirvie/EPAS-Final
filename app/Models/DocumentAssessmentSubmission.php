<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DocumentAssessmentSubmission extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'document_assessment_id',
        'user_id',
        'answer_text',
        'score',
        'feedback',
        'evaluated_by',
        'evaluated_at',
        'submitted_at',
        'is_late',
    ];

    protected $casts = [
        'score' => 'integer',
        'evaluated_at' => 'datetime',
        'submitted_at' => 'datetime',
        'is_late' => 'boolean',
    ];

    public function documentAssessment(): BelongsTo
    {
        return $this->belongsTo(DocumentAssessment::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function evaluator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluated_by');
    }

    public function getPercentageAttribute(): ?float
    {
        if ($this->score === null) {
            return null;
        }
        $maxPoints = $this->documentAssessment->max_points;
        return $maxPoints > 0 ? round(($this->score / $maxPoints) * 100, 1) : 0;
    }

    public function getEvaluationStatusAttribute(): string
    {
        return $this->evaluated_at ? 'Evaluated' : 'Pending Evaluation';
    }
}

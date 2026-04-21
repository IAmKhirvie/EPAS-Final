<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class JobSheetSubmission extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'job_sheet_id',
        'user_id',
        'observations',
        'challenges',
        'solutions',
        'time_taken',
        'submitted_at',
        'evaluator_notes',
        'evaluated_by',
        'evaluated_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'evaluated_at' => 'datetime',
        'time_taken' => 'integer',
    ];

    public function jobSheet(): BelongsTo
    {
        return $this->belongsTo(JobSheet::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function evaluatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluated_by');
    }

    public function steps(): HasMany
    {
        return $this->hasMany(JobSheetSubmissionStep::class);
    }

    public function getCompletedStepsArrayAttribute(): array
    {
        return $this->steps->where('completed', true)->pluck('step_id')->toArray();
    }

    public function getCompletionPercentageAttribute(): float
    {
        $totalSteps = $this->jobSheet->step_count;
        if ($totalSteps === 0) return 0;
        $completedSteps = $this->steps()->where('completed', true)->count();
        return ($completedSteps / $totalSteps) * 100;
    }

    public function getTimeTakenFormattedAttribute(): string
    {
        if (!$this->time_taken) return 'N/A';

        $hours = floor($this->time_taken / 60);
        $minutes = $this->time_taken % 60;

        if ($hours > 0) {
            return "{$hours}h {$minutes}m";
        }
        return "{$minutes}m";
    }

    public function getEvaluationStatusAttribute(): string
    {
        if ($this->evaluated_at) return 'Evaluated';
        return 'Pending Evaluation';
    }
}

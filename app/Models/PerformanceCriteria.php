<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * DEPRECATED: This model is kept for backward compatibility.
 * Use TaskSheetPerformanceCriterion or JobSheetPerformanceCriterion instead.
 *
 * @deprecated Use TaskSheetPerformanceCriterion or JobSheetPerformanceCriterion
 */
class PerformanceCriteria extends Model
{
    use HasFactory;

    protected $table = 'performance_criteria';

    protected $fillable = [
        'type',
        'related_id',
        'related_type',
        'user_id',
        'criteria',
        'score',
        'evaluator_notes',
        'completed_at',
    ];

    protected $casts = [
        'criteria' => 'array',
        'score' => 'decimal:2',
        'completed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function evaluator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluated_by');
    }

    public function getCriteriaListAttribute(): array
    {
        return $this->criteria ?? [];
    }

    public function getObservedCountAttribute(): int
    {
        return count(array_filter($this->criteria ?? [], function($criterion) {
            return $criterion['observed'] ?? false;
        }));
    }

    public function getTotalCriteriaAttribute(): int
    {
        return count($this->criteria ?? []);
    }

    public function getPerformancePercentageAttribute(): float
    {
        $total = $this->total_criteria;
        if ($total === 0) return 0;
        return ($this->observed_count / $total) * 100;
    }

    public function getGradeAttribute(): string
    {
        $percentage = $this->performance_percentage;
        if ($percentage >= 90) return 'Excellent';
        if ($percentage >= 80) return 'Very Good';
        if ($percentage >= 70) return 'Good';
        if ($percentage >= 60) return 'Satisfactory';
        return 'Needs Improvement';
    }
}

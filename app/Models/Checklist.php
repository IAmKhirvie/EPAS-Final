<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Checklist extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'information_sheet_id',
        'checklist_number',
        'title',
        'description',
        'completed_by',
        'completed_at',
        'evaluated_by',
        'evaluated_at',
        'evaluator_notes',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
        'evaluated_at' => 'datetime',
    ];

    public function informationSheet(): BelongsTo
    {
        return $this->belongsTo(InformationSheet::class);
    }

    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    public function evaluatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluated_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ChecklistItem::class)->orderBy('order');
    }

    public function getItemsListAttribute(): array
    {
        return $this->items->map(function ($item) {
            return [
                'id' => $item->id,
                'item' => $item->item,
                'max_rating' => $item->max_rating,
                'rating' => $item->ratings->where('user_id', auth()->id())->first()?->rating ?? 0,
                'remarks' => $item->ratings->where('user_id', auth()->id())->first()?->remarks ?? '',
            ];
        })->toArray();
    }

    public function getAverageRatingAttribute(): float
    {
        $ratings = $this->items()->with('ratings')->get()
            ->pluck('ratings')
            ->flatten()
            ->pluck('rating');

        if ($ratings->isEmpty()) return 0;
        return $ratings->avg();
    }

    public function getMaxScoreAttribute(): int
    {
        return $this->items()->sum('max_rating');
    }

    public function getTotalScoreAttribute(): int
    {
        return (int) $this->items()->with('ratings')->get()
            ->pluck('ratings')
            ->flatten()
            ->sum('rating');
    }

    public function getRatingPercentageAttribute(): float
    {
        $maxScore = $this->max_score;
        if ($maxScore === 0) return 0;
        return ($this->total_score / $maxScore) * 100;
    }

    public function getGradeAttribute(): string
    {
        $percentage = $this->rating_percentage;
        if ($percentage >= 90) return '5 - Excellent';
        if ($percentage >= 80) return '4 - Very Good';
        if ($percentage >= 70) return '3 - Good';
        if ($percentage >= 60) return '2 - Satisfactory';
        return '1 - Needs Improvement';
    }

    public function getCompletionStatusAttribute(): string
    {
        if ($this->evaluated_at) return 'Evaluated';
        if ($this->completed_at) return 'Completed';
        return 'Pending';
    }
}

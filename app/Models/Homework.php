<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Homework extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'homeworks';

    protected $fillable = [
        'information_sheet_id',
        'homework_number',
        'title',
        'description',
        'instructions',
        'due_date',
        'max_points',
        'allow_late_submission',
        'late_penalty',
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'max_points' => 'integer',
        'allow_late_submission' => 'boolean',
        'late_penalty' => 'integer',
    ];

    public function informationSheet(): BelongsTo
    {
        return $this->belongsTo(InformationSheet::class);
    }

    public function requirements(): HasMany
    {
        return $this->hasMany(HomeworkRequirement::class)->orderBy('order');
    }

    public function guidelines(): HasMany
    {
        return $this->hasMany(HomeworkGuideline::class)->orderBy('order');
    }

    public function referenceImages(): HasMany
    {
        return $this->hasMany(HomeworkReferenceImage::class)->orderBy('order');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(HomeworkSubmission::class);
    }


    public function getRequirementsListAttribute(): array
    {
        return $this->requirements->pluck('requirement')->toArray();
    }

    public function getSubmissionGuidelinesListAttribute(): array
    {
        return $this->guidelines->pluck('guideline')->toArray();
    }

    public function getReferenceImagesListAttribute(): array
    {
        return $this->referenceImages->map(function ($image) {
            return [
                'path' => $image->image_path,
                'caption' => $image->caption,
            ];
        })->toArray();
    }

    public function getSubmissionCountAttribute(): int
    {
        return $this->submissions()->count();
    }

    public function getLateSubmissionCountAttribute(): int
    {
        return $this->submissions()->where('is_late', true)->count();
    }

    public function getAverageScoreAttribute(): ?float
    {
        $submissions = $this->submissions()->whereNotNull('score')->get();
        if ($submissions->isEmpty()) {
            return null;
        }
        return $submissions->avg('score');
    }

    public function getIsPastDueAttribute(): bool
    {
        return $this->due_date !== null && now()->greaterThan($this->due_date);
    }

    public function getDaysUntilDueAttribute(): ?int
    {
        if ($this->due_date === null) {
            return null;
        }

        return now()->diffInDays($this->due_date, false);
    }

    public function scopeOverdue($query)
    {
        return $query->whereNotNull('due_date')->where('due_date', '<', now());
    }
}

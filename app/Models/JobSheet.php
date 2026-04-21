<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class JobSheet extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'information_sheet_id',
        'job_number',
        'title',
        'description',
        'file_path',
        'original_filename',
        'document_content',
        'procedures',
        'performance_criteria',
        'estimated_duration',
        'difficulty_level',
        'randomize_steps',
    ];

    protected $casts = [
        'estimated_duration' => 'integer',
        'randomize_steps' => 'boolean',
    ];

    public function informationSheet(): BelongsTo
    {
        return $this->belongsTo(InformationSheet::class);
    }

    public function objectives(): HasMany
    {
        return $this->hasMany(JobSheetObjective::class)->orderBy('order');
    }

    public function tools(): HasMany
    {
        return $this->hasMany(JobSheetTool::class)->orderBy('order');
    }

    public function safetyRequirements(): HasMany
    {
        return $this->hasMany(JobSheetSafetyRequirement::class)->orderBy('order');
    }

    public function references(): HasMany
    {
        return $this->hasMany(JobSheetReference::class)->orderBy('order');
    }

    public function steps(): HasMany
    {
        return $this->hasMany(JobSheetStep::class)->orderBy('step_number');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(JobSheetSubmission::class);
    }

    public function performanceCriteria(): HasMany
    {
        return $this->hasMany(JobSheetPerformanceCriterion::class);
    }

    public function getObjectivesListAttribute(): array
    {
        return $this->objectives->pluck('objective')->toArray();
    }

    public function getToolsRequiredListAttribute(): array
    {
        return $this->tools->map(function ($item) {
            return [
                'name' => $item->tool_name,
                'quantity' => $item->quantity,
            ];
        })->toArray();
    }

    public function getSafetyRequirementsListAttribute(): array
    {
        return $this->safetyRequirements->pluck('requirement')->toArray();
    }

    public function getReferenceMaterialsListAttribute(): array
    {
        return $this->references->map(function ($item) {
            return [
                'title' => $item->reference_title,
                'type' => $item->reference_type,
            ];
        })->toArray();
    }

    public function getStepCountAttribute(): int
    {
        return $this->steps()->count();
    }

    public function getAverageCompletionTimeAttribute(): ?float
    {
        $submissions = $this->submissions()->whereNotNull('submitted_at')->get();
        if ($submissions->isEmpty()) {
            return null;
        }
        return $submissions->avg('time_taken');
    }

    /**
     * Get steps with optional randomization.
     */
    public function getRandomizedSteps(?int $userId = null): \Illuminate\Support\Collection
    {
        $steps = $this->steps;

        if ($this->randomize_steps && $userId) {
            $seed = $userId + $this->id;
            $steps = $steps->shuffle($seed);
        }

        return $steps;
    }
}

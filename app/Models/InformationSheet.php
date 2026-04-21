<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class InformationSheet extends Model
{
    use HasFactory, SoftDeletes;

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($sheet) {
            // Delete submissions through assessments
            foreach ($sheet->selfChecks as $selfCheck) {
                $selfCheck->submissions()->delete();
                $selfCheck->delete();
            }
            foreach ($sheet->homeworks as $homework) {
                $homework->submissions()->delete();
                $homework->delete();
            }
            foreach ($sheet->taskSheets as $taskSheet) {
                $taskSheet->submissions()->delete();
                $taskSheet->delete();
            }
            foreach ($sheet->jobSheets as $jobSheet) {
                $jobSheet->submissions()->delete();
                $jobSheet->delete();
            }
            $sheet->checklists()->delete();
            $sheet->topics()->delete();
        });
    }

    protected $fillable = [
        'module_id',
        'sheet_number',
        'title',
        'description',
        'content',
        'file_path',
        'original_filename',
        'parts',
        'order',
        'is_active',
    ];

    protected $casts = [
        'order' => 'integer',
        'is_active' => 'boolean',
        'parts' => 'array',
    ];

    // Existing relationships (make sure you have these)
    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    public function topics(): HasMany
    {
        return $this->hasMany(Topic::class);
    }

    public function selfChecks(): HasMany
    {
        return $this->hasMany(SelfCheck::class);
    }

    public function taskSheets(): HasMany
    {
        return $this->hasMany(TaskSheet::class);
    }

    public function jobSheets(): HasMany
    {
        return $this->hasMany(JobSheet::class);
    }

    public function homeworks(): HasMany
    {
        return $this->hasMany(Homework::class);
    }

    public function checklists(): HasMany
    {
        return $this->hasMany(Checklist::class);
    }

    public function documentAssessments(): HasMany
    {
        return $this->hasMany(DocumentAssessment::class);
    }

    public function performanceCriteria()
    {
        return $this->hasManyThrough(PerformanceCriteria::class, TaskSheet::class);
    }

    // Helper method to get all content items count
    public function getContentItemsCountAttribute(): array
    {
        return [
            'topics' => $this->topics->count(),
            'self_checks' => $this->selfChecks->count(),
            'task_sheets' => $this->taskSheets->count(),
            'job_sheets' => $this->jobSheets->count(),
            'homeworks' => $this->homeworks->count(),
            'checklists' => $this->checklists->count(),
            'document_assessments' => $this->documentAssessments->count(),
        ];
    }

    // Helper method to check if information sheet has any content
    public function getHasContentAttribute(): bool
    {
        return $this->topics->count() > 0 
            || $this->selfChecks->count() > 0
            || $this->taskSheets->count() > 0
            || $this->jobSheets->count() > 0
            || $this->homeworks->count() > 0
            || $this->checklists->count() > 0
            || $this->documentAssessments->count() > 0;
    }

    // Scope for active information sheets
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Scope for ordered information sheets
    public function scopeOrdered($query)
    {
        return $query->orderBy('order')->orderBy('sheet_number');
    }
}
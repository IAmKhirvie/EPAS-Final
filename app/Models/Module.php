<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Module extends Model
{
    use SoftDeletes;

    /**
     * Use the slug field for route model binding
     * so URLs show meaningful identifiers (e.g., module-1-assembling-electronic-products)
     * instead of database IDs.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($module) {
            if (empty($module->slug)) {
                $module->slug = static::generateUniqueSlug($module->module_title, $module->course_id);
            }
        });

        static::updating(function ($module) {
            if ($module->isDirty('module_title') && !$module->isDirty('slug')) {
                $module->slug = static::generateUniqueSlug($module->module_title, $module->course_id, $module->id);
            }
        });

        static::deleting(function ($module) {
            $module->informationSheets()->each(function ($sheet) {
                $sheet->delete();
            });
        });
    }

    protected static function generateUniqueSlug(string $title, ?int $courseId, ?int $excludeId = null): string
    {
        $baseSlug = Str::slug($title);
        $slug = $baseSlug;
        $counter = 1;

        // Include soft-deleted records to avoid unique constraint violations at DB level
        while (static::withTrashed()
            ->where('course_id', $courseId)
            ->where('slug', $slug)
            ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
            ->exists()
        ) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    protected $fillable = [
        'course_id',
        'sector',
        'qualification_title',
        'unit_of_competency',
        'module_title',
        'slug',
        'module_number',
        'module_name',
        'thumbnail',
        'table_of_contents',
        'how_to_use_cblm',
        'introduction',
        'learning_outcomes',
        'is_active',
        'order',
        'images',
        // Assessment fields
        'require_final_assessment',
        'assessment_randomize_questions',
        'assessment_show_answers',
        'assessment_passing_score',
        'assessment_time_limit',
        'assessment_max_attempts',
        'assessment_question_count',
        'assessment_question_mode',
        'assessment_include_sources',
        'assessment_require_completion',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'images' => 'array',
        // Assessment casts
        'require_final_assessment' => 'boolean',
        'assessment_randomize_questions' => 'boolean',
        'assessment_show_answers' => 'boolean',
        'assessment_require_completion' => 'boolean',
        'assessment_include_sources' => 'array',
    ];

    public function getThumbnailUrlAttribute(): ?string
    {
        return $this->thumbnail ? asset('storage/' . $this->thumbnail) : null;
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function informationSheets(): HasMany
    {
        return $this->hasMany(InformationSheet::class)->orderBy('order');
    }

    /**
     * Get competency tests for this module.
     */
    public function competencyTests(): HasMany
    {
        return $this->hasMany(CompetencyTest::class)->orderBy('order');
    }

    /**
     * Get assessment submissions for this module.
     */
    public function assessmentSubmissions(): HasMany
    {
        return $this->hasMany(ModuleAssessmentSubmission::class);
    }

    /**
     * Get the default assessment sources.
     */
    public function getAssessmentSourcesAttribute(): array
    {
        return $this->assessment_include_sources ?? ['self_check'];
    }

    /**
     * Check if user has passed the final assessment.
     */
    public function hasPassedAssessment(User $user): bool
    {
        return $this->assessmentSubmissions()
            ->where('user_id', $user->id)
            ->where('passed', true)
            ->where('status', 'completed')
            ->exists();
    }

    /**
     * Get the number of assessment attempts by a user.
     */
    public function getAssessmentAttemptCount(User $user): int
    {
        return $this->assessmentSubmissions()
            ->where('user_id', $user->id)
            ->where('status', 'completed')
            ->count();
    }

    /**
     * Check if user can take the assessment.
     */
    public function canTakeAssessment(User $user): bool
    {
        // Check if assessment is enabled
        if (!$this->require_final_assessment) {
            return false;
        }

        // Check max attempts
        if ($this->assessment_max_attempts) {
            $attempts = $this->getAssessmentAttemptCount($user);
            if ($attempts >= $this->assessment_max_attempts) {
                return false;
            }
        }

        // Check if already passed
        if ($this->hasPassedAssessment($user)) {
            return false;
        }

        // Check if completion is required
        if ($this->assessment_require_completion && !$this->hasCompletedAllActivities($user)) {
            return false;
        }

        return true;
    }

    /**
     * Check if user has completed all activities (self-checks, etc.)
     */
    public function hasCompletedAllActivities(User $user): bool
    {
        $sheets = $this->informationSheets;

        foreach ($sheets as $sheet) {
            $selfChecks = $sheet->selfChecks;
            foreach ($selfChecks as $selfCheck) {
                $submission = $selfCheck->submissions()
                    ->where('user_id', $user->id)
                    ->where('status', 'completed')
                    ->first();

                if (!$submission) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Get best assessment submission for a user.
     */
    public function getBestAssessmentFor(User $user): ?ModuleAssessmentSubmission
    {
        return $this->assessmentSubmissions()
            ->where('user_id', $user->id)
            ->where('status', 'completed')
            ->orderByDesc('percentage')
            ->first();
    }

    /**
     * Get latest assessment submission for a user.
     */
    public function getLatestAssessmentFor(User $user): ?ModuleAssessmentSubmission
    {
        return $this->assessmentSubmissions()
            ->where('user_id', $user->id)
            ->latest()
            ->first();
    }

    /**
     * Get in-progress assessment submission for a user.
     */
    public function getInProgressAssessmentFor(User $user): ?ModuleAssessmentSubmission
    {
        return $this->assessmentSubmissions()
            ->where('user_id', $user->id)
            ->where('status', 'in_progress')
            ->first();
    }

    /**
     * Get prerequisites for this module.
     */
    public function prerequisites(): HasMany
    {
        return $this->hasMany(ModulePrerequisite::class, 'module_id');
    }

    /**
     * Get modules that require this module as a prerequisite.
     */
    public function dependentModules(): HasMany
    {
        return $this->hasMany(ModulePrerequisite::class, 'prerequisite_module_id');
    }

    /**
     * Check if a user has completed this module.
     */
    public function isCompletedBy(User $user): bool
    {
        // Check if user has completed all required content
        $sheets = $this->informationSheets;
        if ($sheets->isEmpty()) {
            return false;
        }

        foreach ($sheets as $sheet) {
            // Check if user has completed self-checks, task sheets, etc.
            // This is a simplified check - you may want to add more conditions
            $selfChecks = $sheet->selfChecks;
            foreach ($selfChecks as $selfCheck) {
                $submission = $selfCheck->submissions()->where('user_id', $user->id)->first();
                if (!$submission || !$submission->is_passed) {
                    return false;
                }
            }
        }

        // If final assessment is required, check if user has passed it
        if ($this->require_final_assessment) {
            return $this->hasPassedAssessment($user);
        }

        return true;
    }

    /**
     * Get the grade for a specific user.
     */
    public function getGradeFor(User $user): ?float
    {
        // Calculate average grade from submissions
        $sheets = $this->informationSheets;
        $totalScore = 0;
        $count = 0;

        foreach ($sheets as $sheet) {
            $selfChecks = $sheet->selfChecks;
            foreach ($selfChecks as $selfCheck) {
                $submission = $selfCheck->submissions()->where('user_id', $user->id)->first();
                if ($submission && $submission->score !== null) {
                    $totalScore += $submission->score;
                    $count++;
                }
            }
        }

        return $count > 0 ? round($totalScore / $count, 2) : null;
    }

    /**
     * Check if a user can access this module based on prerequisites.
     */
    public function canBeAccessedBy(User $user): bool
    {
        return $this->getUnmetPrerequisitesFor($user)->isEmpty();
    }

    /**
     * Get unmet prerequisites for a user.
     */
    public function getUnmetPrerequisitesFor(User $user): \Illuminate\Support\Collection
    {
        $prerequisites = $this->prerequisites()->with('prerequisiteModule')->get();

        return $prerequisites->filter(function ($prerequisite) use ($user) {
            $prereqModule = $prerequisite->prerequisiteModule;
            return $prereqModule && !$prereqModule->isCompletedBy($user);
        })->map(function ($prerequisite) {
            return $prerequisite->prerequisiteModule;
        });
    }

    /**
     * Get the prerequisite modules (the actual Module models).
     */
    public function getPrerequisiteModules(): \Illuminate\Support\Collection
    {
        return $this->prerequisites()
            ->with('prerequisiteModule')
            ->get()
            ->map(fn($prereq) => $prereq->prerequisiteModule)
            ->filter();
    }
}
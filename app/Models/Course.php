<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Course extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'course_name',
        'course_code',
        'description',
        'sector',
        'category_id',
        'thumbnail',
        'start_date',
        'end_date',
        'schedule_days',
        'schedule_time_start',
        'schedule_time_end',
        'duration_hours',
        'is_active',
        'order',
        'instructor_id',
        'target_sections',
        'certificate_template',
        'certificate_background',
        'certificate_config',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'certificate_config' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * Get formatted schedule string
     */
    public function getFormattedScheduleAttribute(): ?string
    {
        if (!$this->schedule_days) {
            return null;
        }

        $days = $this->schedule_days;
        $time = '';

        if ($this->schedule_time_start && $this->schedule_time_end) {
            $start = \Carbon\Carbon::parse($this->schedule_time_start)->format('g:i A');
            $end = \Carbon\Carbon::parse($this->schedule_time_end)->format('g:i A');
            $time = " ({$start} - {$end})";
        }

        return $days . $time;
    }

    /**
     * Get formatted date range string
     */
    public function getFormattedDateRangeAttribute(): ?string
    {
        if (!$this->start_date) {
            return null;
        }

        $start = $this->start_date->format('M d, Y');

        if ($this->end_date) {
            return $start . ' - ' . $this->end_date->format('M d, Y');
        }

        return 'Starts ' . $start;
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($course) {
            $course->modules()->each(function ($module) {
                $module->delete(); // This will trigger Module's deleting event
            });
        });
    }

    public function modules(): HasMany
    {
        return $this->hasMany(Module::class)->orderBy('order');
    }

    public function activeModules(): HasMany
    {
        return $this->hasMany(Module::class)->where('is_active', true)->orderBy('order');
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(CourseCategory::class, 'category_id');
    }

    /**
     * Get the category color or default
     */
    public function getCategoryColorAttribute(): string
    {
        return $this->category?->color ?? '#6d9773';
    }

    /**
     * Get the thumbnail URL or null
     */
    public function getThumbnailUrlAttribute(): ?string
    {
        return $this->thumbnail ? asset('storage/' . $this->thumbnail) : null;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByInstructor($query, $instructorId)
    {
        return $query->where('instructor_id', $instructorId);
    }

    /**
     * Scope: courses visible to a given section.
     * Null/empty target_sections means visible to ALL sections.
     */
    public function scopeForSection($query, ?string $section)
    {
        if (!$section) {
            return $query;
        }

        return $query->where(function ($q) use ($section) {
            $q->whereNull('target_sections')
              ->orWhere('target_sections', '')
              ->orWhere('target_sections', $section)
              ->orWhere('target_sections', 'like', $section . ',%')
              ->orWhere('target_sections', 'like', '%,' . $section . ',%')
              ->orWhere('target_sections', 'like', '%,' . $section);
        });
    }
}
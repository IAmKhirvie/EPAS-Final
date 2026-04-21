<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class HomeworkSubmission extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'homework_id',
        'user_id',
        'file_path',
        'description',
        'work_hours',
        'submitted_at',
        'score',
        'evaluator_notes',
        'evaluated_by',
        'evaluated_at',
        'is_late',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'evaluated_at' => 'datetime',
        'work_hours' => 'decimal:2',
        'score' => 'integer',
        'is_late' => 'boolean',
    ];

    public function homework(): BelongsTo
    {
        return $this->belongsTo(Homework::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function evaluatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluated_by');
    }

    public function getMaxPointsAttribute(): int
    {
        return $this->homework->max_points ?? 100;
    }

    public function getPercentageAttribute(): ?float
    {
        if (!$this->score || !$this->max_points) return null;
        return ($this->score / $this->max_points) * 100;
    }

    public function getGradeAttribute(): ?string
    {
        $percentage = $this->percentage;
        if ($percentage === null) return null;

        if ($percentage >= 90) return 'A';
        if ($percentage >= 80) return 'B';
        if ($percentage >= 70) return 'C';
        if ($percentage >= 60) return 'D';
        return 'F';
    }

    public function getEvaluationStatusAttribute(): string
    {
        if ($this->evaluated_at) return 'Evaluated';
        return 'Pending Evaluation';
    }

    public function getFileNameAttribute(): string
    {
        return basename($this->file_path);
    }

    public function getFileSizeAttribute(): string
    {
        $path = storage_path('app/public/' . $this->file_path);
        if (!file_exists($path)) return 'N/A';

        $size = filesize($path);
        $units = ['B', 'KB', 'MB', 'GB'];
        $unitIndex = 0;

        while ($size >= 1024 && $unitIndex < count($units) - 1) {
            $size /= 1024;
            $unitIndex++;
        }

        return round($size, 2) . ' ' . $units[$unitIndex];
    }
}

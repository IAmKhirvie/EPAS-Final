<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaskSheetSubmission extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'task_sheet_id',
        'user_id',
        'observations',
        'challenges',
        'time_taken',
        'submitted_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'time_taken' => 'integer',
    ];

    public function taskSheet(): BelongsTo
    {
        return $this->belongsTo(TaskSheet::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function findings(): HasMany
    {
        return $this->hasMany(TaskSheetSubmissionFinding::class);
    }

    public function getFindingsArrayAttribute(): array
    {
        return $this->findings->map(function ($finding) {
            return [
                'item_id' => $finding->item_id,
                'finding' => $finding->finding,
                'is_within_range' => $finding->is_within_range,
            ];
        })->toArray();
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
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskSheetPerformanceCriterion extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_sheet_id',
        'user_id',
        'criteria',
        'score',
        'evaluator_notes',
        'evaluated_by',
        'completed_at',
    ];

    protected $casts = [
        'score' => 'decimal:2',
        'completed_at' => 'datetime',
    ];

    public function taskSheet(): BelongsTo
    {
        return $this->belongsTo(TaskSheet::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function evaluatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluated_by');
    }
}

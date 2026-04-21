<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobSheetSubmissionStep extends Model
{
    use HasFactory;

    protected $fillable = [
        'submission_id',
        'step_id',
        'completed',
        'notes',
    ];

    protected $casts = [
        'completed' => 'boolean',
    ];

    public function submission(): BelongsTo
    {
        return $this->belongsTo(JobSheetSubmission::class, 'submission_id');
    }

    public function step(): BelongsTo
    {
        return $this->belongsTo(JobSheetStep::class, 'step_id');
    }
}

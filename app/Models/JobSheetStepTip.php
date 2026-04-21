<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobSheetStepTip extends Model
{
    use HasFactory;

    protected $fillable = [
        'step_id',
        'tip',
        'order',
    ];

    protected $casts = [
        'order' => 'integer',
    ];

    public function step(): BelongsTo
    {
        return $this->belongsTo(JobSheetStep::class, 'step_id');
    }
}

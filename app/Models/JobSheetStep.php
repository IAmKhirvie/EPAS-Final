<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobSheetStep extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_sheet_id',
        'step_number',
        'instruction',
        'expected_outcome',
        'image_path',
    ];

    protected $casts = [
        'step_number' => 'integer',
    ];

    public function jobSheet(): BelongsTo
    {
        return $this->belongsTo(JobSheet::class);
    }

    public function warnings(): HasMany
    {
        return $this->hasMany(JobSheetStepWarning::class)->orderBy('order');
    }

    public function tips(): HasMany
    {
        return $this->hasMany(JobSheetStepTip::class)->orderBy('order');
    }

    public function getWarningsListAttribute(): array
    {
        return $this->warnings->pluck('warning')->toArray();
    }

    public function getTipsListAttribute(): array
    {
        return $this->tips->pluck('tip')->toArray();
    }
}

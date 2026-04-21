<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskSheetItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_sheet_id',
        'part_name',
        'description',
        'expected_finding',
        'acceptable_range',
        'order',
        'image_path',
    ];

    protected $casts = [
        'order' => 'integer',
    ];

    public function taskSheet(): BelongsTo
    {
        return $this->belongsTo(TaskSheet::class);
    }
}
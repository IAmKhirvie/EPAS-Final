<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskSheetObjective extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_sheet_id',
        'objective',
        'order',
    ];

    protected $casts = [
        'order' => 'integer',
    ];

    public function taskSheet(): BelongsTo
    {
        return $this->belongsTo(TaskSheet::class);
    }
}

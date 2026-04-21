<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskSheetMaterial extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_sheet_id',
        'material_name',
        'quantity',
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

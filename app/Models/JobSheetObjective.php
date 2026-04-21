<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobSheetObjective extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_sheet_id',
        'objective',
        'order',
    ];

    protected $casts = [
        'order' => 'integer',
    ];

    public function jobSheet(): BelongsTo
    {
        return $this->belongsTo(JobSheet::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HomeworkGuideline extends Model
{
    use HasFactory;

    protected $fillable = [
        'homework_id',
        'guideline',
        'order',
    ];

    protected $casts = [
        'order' => 'integer',
    ];

    public function homework(): BelongsTo
    {
        return $this->belongsTo(Homework::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HomeworkRequirement extends Model
{
    use HasFactory;

    protected $fillable = [
        'homework_id',
        'requirement',
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

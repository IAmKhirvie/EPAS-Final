<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SelfCheckSubmission extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'self_check_id',
        'user_id',
        'score',
        'total_points',
        'percentage',
        'passed',
        'answers',
        'completed_at',
        'time_taken',
    ];

    protected $casts = [
        'score' => 'integer',
        'total_points' => 'integer',
        'percentage' => 'decimal:2',
        'passed' => 'boolean',
        'answers' => 'array',
        'completed_at' => 'datetime',
        'time_taken' => 'integer',
    ];

    public function selfCheck(): BelongsTo
    {
        return $this->belongsTo(SelfCheck::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getGradeAttribute(): string
    {
        if ($this->percentage >= 90) return 'A';
        if ($this->percentage >= 80) return 'B';
        if ($this->percentage >= 70) return 'C';
        if ($this->percentage >= 60) return 'D';
        return 'F';
    }

    public function getTimeTakenFormattedAttribute(): string
    {
        if (!$this->time_taken) return 'N/A';
        
        $minutes = floor($this->time_taken / 60);
        $seconds = $this->time_taken % 60;
        return "{$minutes}m {$seconds}s";
    }
}
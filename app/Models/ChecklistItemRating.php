<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChecklistItemRating extends Model
{
    use HasFactory;

    protected $fillable = [
        'checklist_item_id',
        'user_id',
        'rating',
        'remarks',
    ];

    protected $casts = [
        'rating' => 'integer',
    ];

    public function checklistItem(): BelongsTo
    {
        return $this->belongsTo(ChecklistItem::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

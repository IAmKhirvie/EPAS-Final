<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Pivot model for instructor-section assignments.
 *
 * Allows instructors to be assigned to multiple classes/sections.
 *
 * @property int $id
 * @property int $user_id
 * @property string $section
 * @property bool $is_primary
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class InstructorSection extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'section',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    /**
     * Get the instructor for this assignment.
     */
    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

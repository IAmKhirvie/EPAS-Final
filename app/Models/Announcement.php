<?php

namespace App\Models;

use App\Traits\HasCommonScopes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Announcement extends Model
{
    use HasFactory, SoftDeletes, HasCommonScopes;

    protected $fillable = [
        'title',
        'content',
        'user_id',
        'is_pinned',
        'is_urgent',
        'publish_at',
        'deadline',
        'target_roles',
        'target_sections',
    ];

    protected $casts = [
        'is_pinned' => 'boolean',
        'is_urgent' => 'boolean',
        'publish_at' => 'datetime',
        'deadline' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(AnnouncementComment::class)->orderBy('created_at', 'asc');
    }

    // Custom scope with pinned ordering (overrides trait)
    public function scopeRecent($query, $limit = 5)
    {
        return $query->published()
                    ->orderBy('is_pinned', 'desc')
                    ->orderBy('created_at', 'desc')
                    ->limit($limit);
    }

    public function scopeForUser($query, $user)
    {
        $role = $user->role;
        $section = $user->section;

        return $query->where(function ($q) use ($role) {
            $q->where('target_roles', 'all')
              ->orWhereNull('target_roles')
              ->orWhere('target_roles', $role)
              ->orWhere('target_roles', 'like', $role . ',%')
              ->orWhere('target_roles', 'like', '%,' . $role . ',%')
              ->orWhere('target_roles', 'like', '%,' . $role);
        })->where(function ($q) use ($section) {
            $q->whereNull('target_sections')
              ->orWhere('target_sections', '');
            if ($section) {
                $q->orWhere('target_sections', $section)
                  ->orWhere('target_sections', 'like', $section . ',%')
                  ->orWhere('target_sections', 'like', '%,' . $section . ',%')
                  ->orWhere('target_sections', 'like', '%,' . $section);
            }
        });
    }
}
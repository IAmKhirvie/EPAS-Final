<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait HasCommonScopes
{
    /**
     * Scope to filter active records
     * Assumes an 'is_active' or 'stat' column exists
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->where('is_active', true)
              ->orWhere('stat', 1);
        });
    }

    /**
     * Scope to filter inactive records
     */
    public function scopeInactive(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->where('is_active', false)
              ->orWhere('stat', 0);
        });
    }

    /**
     * Scope to filter pending records
     * Assumes a 'status' column with 'pending' value
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope to filter approved records
     */
    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope to filter rejected records
     */
    public function scopeRejected(Builder $query): Builder
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Scope to filter published records
     * Checks for null publish_at or publish_at <= now
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->whereNull('publish_at')
              ->orWhere('publish_at', '<=', now());
        });
    }

    /**
     * Scope to get recent records
     * Orders by created_at desc
     */
    public function scopeRecent(Builder $query, ?int $limit = null): Builder
    {
        $query = $query->orderBy('created_at', 'desc');

        if ($limit !== null) {
            return $query->limit($limit);
        }

        return $query;
    }

    /**
     * Scope to filter by user ID
     * Assumes a 'user_id' column exists
     */
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to filter by user model
     */
    public function scopeForUserModel(Builder $query, $user): Builder
    {
        return $query->where('user_id', $user->id);
    }
}

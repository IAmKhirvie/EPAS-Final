<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Registration extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'registrations';

    protected $fillable = [
        'first_name',
        'middle_name',
        'last_name',
        'ext_name',
        'email',
        'password',
        'verification_token',
        'verification_token_expires',
        'email_verified_at',
        'admin_approved_at',
        'approved_by',
        'rejection_reason',
        'status',
    ];

    protected $hidden = [
        'password',
        'verification_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'admin_approved_at' => 'datetime',
        'verification_token_expires' => 'datetime',
    ];

    /**
     * Status constants
     */
    const STATUS_PENDING = 'pending';
    const STATUS_EMAIL_VERIFIED = 'email_verified';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_TRANSFERRED = 'transferred';

    /**
     * Generate a new verification token
     */
    public function generateVerificationToken(): string
    {
        $token = Str::random(64);
        $this->update([
            'verification_token' => $token,
            'verification_token_expires' => now()->addHours(24),
        ]);
        return $token;
    }

    /**
     * Check if email is verified
     */
    public function isEmailVerified(): bool
    {
        return $this->email_verified_at !== null;
    }

    /**
     * Check if admin has approved
     */
    public function isAdminApproved(): bool
    {
        return $this->admin_approved_at !== null;
    }

    /**
     * Check if ready to transfer to users table
     */
    public function isReadyToTransfer(): bool
    {
        return $this->isEmailVerified() && $this->isAdminApproved();
    }

    /**
     * Mark email as verified
     */
    public function markEmailAsVerified(): bool
    {
        $this->update([
            'email_verified_at' => now(),
            'verification_token' => null,
            'verification_token_expires' => null,
            'status' => $this->isAdminApproved() ? self::STATUS_APPROVED : self::STATUS_EMAIL_VERIFIED,
        ]);

        return $this->isReadyToTransfer();
    }

    /**
     * Admin approves the registration
     */
    public function approve(int $adminId): bool
    {
        $this->update([
            'admin_approved_at' => now(),
            'approved_by' => $adminId,
            'rejection_reason' => null,
            'status' => $this->isEmailVerified() ? self::STATUS_APPROVED : self::STATUS_PENDING,
        ]);

        return $this->isReadyToTransfer();
    }

    /**
     * Admin rejects the registration
     */
    public function reject(int $adminId, ?string $reason = null): void
    {
        $this->update([
            'approved_by' => $adminId,
            'rejection_reason' => $reason,
            'status' => self::STATUS_REJECTED,
        ]);
    }

    /**
     * Get full name
     */
    public function getFullNameAttribute(): string
    {
        $name = $this->first_name;
        if ($this->middle_name) {
            $name .= ' ' . $this->middle_name;
        }
        $name .= ' ' . $this->last_name;
        if ($this->ext_name) {
            $name .= ' ' . $this->ext_name;
        }
        return $name;
    }

    /**
     * Scope for pending registrations
     */
    public function scopePending($query)
    {
        return $query->whereIn('status', [self::STATUS_PENDING, self::STATUS_EMAIL_VERIFIED]);
    }

    /**
     * Scope for email verified but not admin approved
     */
    public function scopeAwaitingApproval($query)
    {
        return $query->where('status', self::STATUS_EMAIL_VERIFIED);
    }

    /**
     * Get the admin who approved/rejected
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}

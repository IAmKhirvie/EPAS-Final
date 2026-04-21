<?php

namespace App\Models;

use App\Traits\HasCommonScopes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EnrollmentRequest extends Model
{
    use HasFactory, HasCommonScopes, SoftDeletes;

    protected $fillable = [
        'instructor_id',
        'student_id',
        'student_name',
        'student_email',
        'section',
        'status',
        'processed_by',
        'notes',
        'admin_notes',
        'processed_at',
    ];

    protected $casts = [
        'processed_at' => 'datetime',
    ];

    /**
     * Get the instructor who made the request
     */
    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    /**
     * Get the student being requested for enrollment
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * Get the admin who processed the request
     */
    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    /**
     * Scope for requests by a specific instructor
     */
    public function scopeByInstructor($query, $instructorId)
    {
        return $query->where('instructor_id', $instructorId);
    }

    /**
     * Get student display name (from student relation or stored name)
     */
    public function getStudentDisplayNameAttribute(): string
    {
        if ($this->student) {
            return $this->student->full_name;
        }
        return $this->student_name ?? 'Unknown Student';
    }

    /**
     * Get student display email
     */
    public function getStudentDisplayEmailAttribute(): ?string
    {
        if ($this->student) {
            return $this->student->email;
        }
        return $this->student_email;
    }

    /**
     * Check if request is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if request is approved
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check if request is rejected
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Approve the request
     */
    public function approve(User $admin, ?string $notes = null): bool
    {
        // If student exists, update their section
        if ($this->student) {
            $this->student->update(['section' => $this->section]);
        }

        return $this->update([
            'status' => 'approved',
            'processed_by' => $admin->id,
            'admin_notes' => $notes,
            'processed_at' => now(),
        ]);
    }

    /**
     * Reject the request
     */
    public function reject(User $admin, ?string $notes = null): bool
    {
        return $this->update([
            'status' => 'rejected',
            'processed_by' => $admin->id,
            'admin_notes' => $notes,
            'processed_at' => now(),
        ]);
    }
}

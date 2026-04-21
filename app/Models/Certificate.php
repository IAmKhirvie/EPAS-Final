<?php

namespace App\Models;

use App\Traits\HasCommonScopes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Certificate extends Model
{
    use HasFactory, HasCommonScopes;

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_PENDING_INSTRUCTOR = 'pending_instructor';
    const STATUS_PENDING_ADMIN = 'pending_admin';
    const STATUS_ISSUED = 'issued';
    const STATUS_REVOKED = 'revoked';
    const STATUS_EXPIRED = 'expired';
    const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'user_id',
        'course_id',
        'module_id',
        'certificate_number',
        'title',
        'description',
        'issue_date',
        'expiry_date',
        'status',
        'metadata',
        'pdf_path',
        'template_used',
        'requested_by',
        'requested_at',
        'approved_by',
        'approved_at',
        'instructor_approved_by',
        'instructor_approved_at',
        'admin_approved_by',
        'admin_approved_at',
        'rejection_reason',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'expiry_date' => 'date',
        'metadata' => 'array',
        'requested_at' => 'datetime',
        'approved_at' => 'datetime',
        'instructor_approved_at' => 'datetime',
        'admin_approved_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function instructorApprovedBy()
    {
        return $this->belongsTo(User::class, 'instructor_approved_by');
    }

    public function adminApprovedBy()
    {
        return $this->belongsTo(User::class, 'admin_approved_by');
    }

    public function scopePending($query)
    {
        return $query->whereIn('status', [self::STATUS_PENDING, self::STATUS_PENDING_INSTRUCTOR, self::STATUS_PENDING_ADMIN]);
    }

    public function scopePendingInstructor($query)
    {
        return $query->where('status', self::STATUS_PENDING_INSTRUCTOR);
    }

    public function scopePendingAdmin($query)
    {
        return $query->where('status', self::STATUS_PENDING_ADMIN);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeIssued($query)
    {
        return $query->where('status', self::STATUS_ISSUED);
    }

    public function scopeForModule($query, $moduleId)
    {
        return $query->where('module_id', $moduleId);
    }

    /**
     * Check if certificate needs instructor approval
     */
    public function needsInstructorApproval(): bool
    {
        return $this->status === self::STATUS_PENDING_INSTRUCTOR;
    }

    /**
     * Check if certificate needs admin approval
     */
    public function needsAdminApproval(): bool
    {
        return $this->status === self::STATUS_PENDING_ADMIN;
    }

    /**
     * Check if certificate is fully approved and issued
     */
    public function isIssued(): bool
    {
        return $this->status === self::STATUS_ISSUED;
    }

    public static function generateCertificateNumber()
    {
        $prefix = 'CERT';
        $year = date('Y');
        $random = strtoupper(substr(md5(uniqid()), 0, 8));
        return "{$prefix}-{$year}-{$random}";
    }
}

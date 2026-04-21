<?php

namespace App\Models;

use App\Constants\Roles;
use App\Notifications\CustomVerifyEmail;
use App\Traits\HasCommonScopes;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * User model for the JOMS LMS.
 *
 * Supports three roles: admin, instructor, student.
 * Includes gamification features (points, badges, streaks).
 *
 * @property int $id
 * @property string $first_name
 * @property string|null $middle_name
 * @property string $last_name
 * @property string|null $ext_name
 * @property string $email
 * @property string $password
 * @property string $role
 * @property int|null $department_id
 * @property bool $stat
 * @property string|null $student_id
 * @property string|null $profile_image
 * @property string|null $section
 * @property string|null $room_number
 * @property \Carbon\Carbon|null $email_verified_at
 * @property int $total_points
 * @property int $current_streak
 * @property \Carbon\Carbon|null $last_activity_date
 * @property bool $two_factor_required
 * @property array|null $notification_preferences
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 *
 * @property-read string $full_name
 * @property-read string $role_display
 * @property-read string $status_display
 * @property-read bool $is_verified
 * @property-read string $profile_image_url
 * @property-read string $initials
 */
class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, HasApiTokens, Notifiable, SoftDeletes, HasCommonScopes;

    protected static function booted(): void
    {
        static::deleting(function (User $user) {
            if ($user->role === \App\Constants\Roles::INSTRUCTOR) {
                \App\Models\Course::where('instructor_id', $user->id)->update(['instructor_id' => null]);
            }
        });
    }

    protected $fillable = [
        'first_name',
        'middle_name',
        'last_name',
        'ext_name',
        'email',
        'phone',
        'bio',
        'password',
        'department_id',
        'stat',
        'student_id',
        'profile_image',
        'section',
        'school_year',
        'room_number',
        'email_verified_at',
        'total_points',
        'current_streak',
        'last_activity_date',
        'two_factor_required',
        'notification_preferences',
        'email_changed_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'reset_token',
    ];

    protected $appends = [
        'full_name',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'email_changed_at' => 'datetime',
            'password' => 'hashed',
            'last_login' => 'datetime',
            'stat' => 'integer',
            'reset_token_expires' => 'datetime',
            'notification_preferences' => 'encrypted:array',
        ];
    }

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function points(): HasMany
    {
        return $this->hasMany(UserPoint::class);
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }

    public function progress(): HasMany
    {
        return $this->hasMany(UserProgress::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    public function completedModules(): HasMany
    {
        return $this->hasMany(UserProgress::class)->where('completed', true);
    }

    /**
     * Get the sections assigned to this instructor (many-to-many).
     */
    public function instructorSections(): HasMany
    {
        return $this->hasMany(InstructorSection::class);
    }

    /**
     * Get array of section names assigned to this instructor.
     *
     * @return array<string>
     */
    public function getAssignedSections(): array
    {
        return $this->instructorSections()->pluck('section')->toArray();
    }

    /**
     * Check if instructor is assigned to a specific section.
     *
     * @param string $section
     * @return bool
     */
    public function isAssignedToSection(string $section): bool
    {
        // Check new many-to-many relationship first
        if ($this->instructorSections()->where('section', $section)->exists()) {
            return true;
        }
        // Fallback to legacy advisory_section for backward compatibility
        return $this->advisory_section === $section;
    }

    /**
     * Get all sections this instructor can access (includes legacy advisory_section).
     *
     * @return \Illuminate\Support\Collection
     */
    public function getAllAccessibleSections(): \Illuminate\Support\Collection
    {
        $sections = collect($this->getAssignedSections());

        // Include legacy advisory_section if set
        if ($this->advisory_section && !$sections->contains($this->advisory_section)) {
            $sections->push($this->advisory_section);
        }

        return $sections->unique()->values();
    }

    // =========================================================================
    // ACCESSORS
    // =========================================================================

    /**
     * Get the middle initial (e.g. "Nayve" → "N.").
     */
    public function getMiddleInitialAttribute(): ?string
    {
        if (!$this->middle_name) {
            return null;
        }
        return strtoupper(substr($this->middle_name, 0, 1)) . '.';
    }

    /**
     * Get the user's full name (uses middle initial when present).
     */
    public function getFullNameAttribute(): string
    {
        $parts = array_filter([
            $this->first_name,
            $this->middle_initial,
            $this->last_name,
            $this->ext_name,
        ]);

        return implode(' ', $parts);
    }

    /**
     * Get the user's role with pretty formatting.
     */
    public function getRoleDisplayAttribute(): string
    {
        return ucfirst($this->role);
    }

    /**
     * Get the user's status with pretty formatting.
     */
    public function getStatusDisplayAttribute(): string
    {
        return $this->stat ? 'Active' : 'Pending';
    }

    /**
     * Get the user's verification status.
     */
    public function getIsVerifiedAttribute(): bool
    {
        return !is_null($this->email_verified_at);
    }

    /**
     * Get the user's profile image URL.
     * Returns uploaded image or generates avatar with initials.
     */
    public function getProfileImageUrlAttribute(): string
    {
        if ($this->profile_image) {
            if (str_starts_with($this->profile_image, 'http')) {
                return $this->profile_image;
            }
            // asset() helper automatically handles HTTP/HTTPS based on request
            // Add cache-busting with updated_at timestamp
            $cacheBuster = $this->updated_at ? $this->updated_at->timestamp : time();
            return asset('storage/profile-images/' . $this->profile_image) . '?v=' . $cacheBuster;
        }

        $initials = $this->initials;
        return "https://ui-avatars.com/api/?name=" . urlencode($initials) . "&background=6d9773&color=fff&size=32";
    }

    /**
     * Get user initials.
     */
    public function getInitialsAttribute(): string
    {
        $first = substr($this->first_name ?? '', 0, 1);
        $last = substr($this->last_name ?? '', 0, 1);
        return strtoupper($first . $last) ?: 'U';
    }

    // =========================================================================
    // ROLE CHECKS
    // =========================================================================

    /**
     * Check if user is active.
     */
    public function isActive(): bool
    {
        return (int) $this->stat === 1;
    }

    /**
     * Check if user is an admin.
     */
    public function isAdmin(): bool
    {
        return $this->role === Roles::ADMIN;
    }

    /**
     * Check if user is an instructor.
     */
    public function isInstructor(): bool
    {
        return $this->role === Roles::INSTRUCTOR;
    }

    /**
     * Check if user is a student.
     */
    public function isStudent(): bool
    {
        return $this->role === Roles::STUDENT;
    }

    // =========================================================================
    // SCOPES
    // =========================================================================

    /**
     * Scope for active users.
     * Overrides trait's scopeActive to use 'stat' column.
     */
    public function scopeActive($query)
    {
        return $query->where('stat', 1);
    }

    /**
     * Scope for students.
     */
    public function scopeStudents($query)
    {
        return $query->where('role', Roles::STUDENT);
    }

    /**
     * Scope for instructors.
     */
    public function scopeInstructors($query)
    {
        return $query->where('role', Roles::INSTRUCTOR);
    }

    /**
     * Scope by department.
     */
    public function scopeByDepartment($query, $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }

    /**
     * Scope for pending users (custom - uses stat column, not status)
     * Note: Overrides trait's scopePending which checks 'status' column
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('stat', 0);
    }

    /**
     * Scope for verified users.
     */
    public function scopeVerified(Builder $query): Builder
    {
        return $query->whereNotNull('email_verified_at');
    }

    /**
     * Scope for unverified users.
     */
    public function scopeUnverified(Builder $query): Builder
    {
        return $query->whereNull('email_verified_at');
    }

    /**
     * Scope by role.
     */
    public function scopeByRole(Builder $query, string $role): Builder
    {
        return $query->where('role', $role);
    }

    // =========================================================================
    // EMAIL VERIFICATION
    // =========================================================================

    /**
     * Get the email address for verification.
     */
    public function getEmailForVerification(): string
    {
        return $this->email;
    }

    /**
     * Send the email verification notification.
     */
    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new CustomVerifyEmail);
    }

    // =========================================================================
    // NOTIFICATION PREFERENCES
    // =========================================================================

    /**
     * Get a notification preference value.
     */
    public function getNotificationPreference(string $key, bool $default = true): bool
    {
        return $this->notification_preferences[$key] ?? $default;
    }

    /**
     * Set a notification preference value.
     */
    public function setNotificationPreference(string $key, bool $value): void
    {
        $preferences = $this->notification_preferences ?? [];
        $preferences[$key] = $value;
        $this->update(['notification_preferences' => $preferences]);
    }

    /**
     * Get default notification preferences.
     */
    public function getDefaultNotificationPreferences(): array
    {
        return [
            'email_homework_submitted' => true,
            'email_grade_posted' => true,
            'email_deadline_reminder' => true,
            'email_new_message' => true,
            'email_announcement' => true,
        ];
    }
}

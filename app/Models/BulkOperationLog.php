<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BulkOperationLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'operation_type',
        'total_records',
        'processed_records',
        'successful_records',
        'failed_records',
        'status',
        'input_data',
        'errors',
        'results',
        'notes',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'input_data' => 'array',
        'errors' => 'array',
        'results' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeProcessing($query)
    {
        return $query->where('status', 'processing');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeOfType($query, $type)
    {
        return $query->where('operation_type', $type);
    }

    // Helper Methods
    public function markAsProcessing()
    {
        $this->update([
            'status' => 'processing',
            'started_at' => now(),
        ]);
    }

    public function markAsCompleted()
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }

    public function markAsFailed($errorMessage = null)
    {
        $errors = $this->errors ?? [];
        if ($errorMessage) {
            $errors[] = ['message' => $errorMessage, 'time' => now()->toISOString()];
        }

        $this->update([
            'status' => 'failed',
            'errors' => $errors,
            'completed_at' => now(),
        ]);
    }

    public function incrementProgress($success = true)
    {
        $this->increment('processed_records');

        if ($success) {
            $this->increment('successful_records');
        } else {
            $this->increment('failed_records');
        }
    }

    public function addError($error)
    {
        $errors = $this->errors ?? [];
        $errors[] = is_array($error) ? $error : ['message' => $error, 'time' => now()->toISOString()];
        $this->update(['errors' => $errors]);
    }

    public function getProgressPercentageAttribute()
    {
        if ($this->total_records === 0) {
            return 0;
        }
        return round(($this->processed_records / $this->total_records) * 100, 1);
    }

    public function getSuccessRateAttribute()
    {
        if ($this->processed_records === 0) {
            return 0;
        }
        return round(($this->successful_records / $this->processed_records) * 100, 1);
    }

    public function getDurationAttribute()
    {
        if (!$this->started_at) {
            return null;
        }

        $end = $this->completed_at ?? now();
        return $this->started_at->diffForHumans($end, true);
    }

    public function getOperationLabelAttribute()
    {
        return match($this->operation_type) {
            'enroll' => 'Bulk Enrollment',
            'notify' => 'Bulk Notification',
            'grade' => 'Bulk Grading',
            'import' => 'Data Import',
            default => ucfirst($this->operation_type),
        };
    }

    public function getStatusBadgeAttribute()
    {
        return match($this->status) {
            'pending' => 'secondary',
            'processing' => 'info',
            'completed' => 'success',
            'failed' => 'danger',
            'cancelled' => 'warning',
            default => 'secondary',
        };
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DocumentAssessment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'information_sheet_id',
        'created_by',
        'assessment_number',
        'title',
        'description',
        'instructions',
        'document_content',
        'file_path',
        'original_filename',
        'file_type',
        'max_points',
        'time_limit',
        'due_date',
        'is_active',
    ];

    protected $casts = [
        'max_points' => 'integer',
        'time_limit' => 'integer',
        'due_date' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function informationSheet(): BelongsTo
    {
        return $this->belongsTo(InformationSheet::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(DocumentAssessmentSubmission::class);
    }

    public function getIsEditableDocumentAttribute(): bool
    {
        return in_array($this->file_type, ['docx', 'pptx']);
    }

    public function getIsPreviewableAttribute(): bool
    {
        return in_array($this->file_type, ['docx', 'doc', 'pptx', 'ppt', 'xlsx', 'xls', 'pdf']);
    }

    public function getIsPdfAttribute(): bool
    {
        return $this->file_type === 'pdf';
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($assessment) {
            $assessment->submissions()->delete();
        });
    }
}

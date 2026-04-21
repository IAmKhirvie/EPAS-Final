<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModulePrerequisite extends Model
{
    use HasFactory;

    protected $fillable = [
        'module_id',
        'prerequisite_module_id',
    ];

    /**
     * Get the module that has this prerequisite requirement.
     */
    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class, 'module_id');
    }

    /**
     * Get the prerequisite module that must be completed.
     */
    public function prerequisiteModule(): BelongsTo
    {
        return $this->belongsTo(Module::class, 'prerequisite_module_id');
    }
}

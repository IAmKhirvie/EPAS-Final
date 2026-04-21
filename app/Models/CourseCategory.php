<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class CourseCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'color',
        'icon',
        'description',
        'order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });
    }

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class, 'category_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    /**
     * Get darker shade of the category color for gradients
     */
    public function getDarkerColorAttribute(): string
    {
        $hex = ltrim($this->color, '#');
        $rgb = array_map('hexdec', str_split($hex, 2));

        // Darken by 20%
        $darker = array_map(function ($c) {
            return max(0, (int)($c * 0.8));
        }, $rgb);

        return sprintf('#%02x%02x%02x', $darker[0], $darker[1], $darker[2]);
    }

    /**
     * Get lighter shade of the category color
     */
    public function getLighterColorAttribute(): string
    {
        $hex = ltrim($this->color, '#');
        $rgb = array_map('hexdec', str_split($hex, 2));

        // Lighten by 20%
        $lighter = array_map(function ($c) {
            return min(255, (int)($c + (255 - $c) * 0.2));
        }, $rgb);

        return sprintf('#%02x%02x%02x', $lighter[0], $lighter[1], $lighter[2]);
    }
}

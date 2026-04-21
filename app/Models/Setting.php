<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Setting extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'key',
        'value',
    ];

    /**
     * Get the user that owns the setting.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get a setting value by key for a user
     */
    public static function getUserSetting($userId, $key, $default = null)
    {
        $setting = self::where('user_id', $userId)->where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * Get a system setting (no user_id)
     */
    public static function getSystemSetting($key, $default = null)
    {
        $setting = self::whereNull('user_id')->where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }
}

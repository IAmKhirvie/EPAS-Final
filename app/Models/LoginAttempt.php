<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoginAttempt extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'email',
        'ip_address',
        'successful',
        'user_agent',
        'attempted_at',
    ];

    protected $casts = [
        'successful' => 'boolean',
        'attempted_at' => 'datetime',
    ];

    public function scopeForIp($query, $ip)
    {
        return $query->where('ip_address', $ip);
    }

    public function scopeFailed($query)
    {
        return $query->where('successful', false);
    }

    public function scopeRecent($query, $minutes = 15)
    {
        return $query->where('attempted_at', '>=', now()->subMinutes($minutes));
    }

    public static function recordAttempt($email, $ip, $successful, $userAgent = null)
    {
        return static::create([
            'email' => $email,
            'ip_address' => $ip,
            'successful' => $successful,
            'user_agent' => $userAgent,
            'attempted_at' => now(),
        ]);
    }

    public static function getRecentFailedCount($ip, $minutes = 15)
    {
        return static::forIp($ip)->failed()->recent($minutes)->count();
    }
}

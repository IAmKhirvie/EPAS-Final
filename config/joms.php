<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Grading Configuration
    |--------------------------------------------------------------------------
    */

    'grading' => [
        'default_passing_score' => 70,
        'homework_pass_threshold' => 0.6,
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication & Security
    |--------------------------------------------------------------------------
    */

    'auth' => [
        'email_change_cooldown_hours' => 24,
    ],

    /*
    |--------------------------------------------------------------------------
    | Password Policy
    |--------------------------------------------------------------------------
    */

    'password' => [
        'min_length' => 8,
        'require_uppercase' => true,
        'require_lowercase' => true,
        'require_numeric' => true,
        'require_special' => true,
        // Regex pattern for validation (must match all requirements above)
        'regex' => '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&#^()_+=\-\[\]{}|:;<>,.?\/~`])[A-Za-z\d@$!%*?&#^()_+=\-\[\]{}|:;<>,.?\/~`]{8,}$/',
        'message' => 'Password must be at least 8 characters and contain at least one uppercase letter, one lowercase letter, one number, and one special character.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Gamification Points
    |--------------------------------------------------------------------------
    */

    'gamification' => [
        'points' => [
            'topic_complete' => 10,
            'self_check_pass' => 25,
            'homework_submit' => 15,
            'perfect_score' => 50,
            'daily_login' => 5,
            'module_complete' => 100,
            'course_complete' => 500,
            'milestone_25' => 25,
            'milestone_50' => 50,
            'milestone_75' => 75,
            'milestone_100' => 100,
        ],
        'milestones' => [25 => 25, 50 => 50, 75 => 75, 100 => 100],
    ],

    /*
    |--------------------------------------------------------------------------
    | SMTP / PHPMailer
    |--------------------------------------------------------------------------
    */

    'mail' => [
        'host' => env('MAIL_HOST', 'smtp.gmail.com'),
        'port' => (int) env('MAIL_PORT', 587),
        'username' => env('MAIL_USERNAME'),
        'password' => env('MAIL_PASSWORD'),
        'encryption' => env('MAIL_ENCRYPTION', 'tls'),
        'from_address' => env('MAIL_FROM_ADDRESS', env('MAIL_USERNAME')),
        'from_name' => env('MAIL_FROM_NAME', 'EPAS-E LMS'),
        'admin_email' => env('MAIL_ADMIN_EMAIL', env('MAIL_USERNAME')),
        'timeout' => 30,
    ],

    /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    */

    'pagination' => [
        'default' => 15,
        'users' => 20,
        'audit_logs' => 25,
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache TTL (seconds)
    |--------------------------------------------------------------------------
    */

    'cache' => [
        'dashboard_stats_ttl' => 600,  // 10 minutes
        'grades_ttl' => 300,           // 5 minutes
        'rankings_ttl' => 600,         // 10 minutes
    ],

    /*
    |--------------------------------------------------------------------------
    | File Uploads
    |--------------------------------------------------------------------------
    */

    'uploads' => [
        'max_document_size' => 10240,   // KB (10 MB)
        'max_image_size' => 5120,       // KB (5 MB)
        'max_audio_size' => 20480,      // KB (20 MB)
        'max_video_size' => 102400,     // KB (100 MB)
        'allowed_document_types' => ['pdf', 'doc', 'docx', 'xlsx', 'xls', 'txt', 'ppt', 'pptx'],
        'allowed_image_types' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
        'allowed_audio_types' => ['mp3', 'wav', 'ogg', 'm4a', 'webm'],
        'allowed_video_types' => ['mp4', 'webm', 'ogg', 'mov'],
    ],

];

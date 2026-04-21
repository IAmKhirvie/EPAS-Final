<?php

namespace App\Constants;

class Achievements
{
    public const DEFINITIONS = [
        // 1. Login Streak
        'STREAK_BRONZE' => ['name' => 'Streak Starter', 'description' => '3-day login streak', 'icon' => 'fas fa-fire', 'points' => 15, 'tier' => 'bronze'],
        'STREAK_SILVER' => ['name' => 'Week Warrior', 'description' => '7-day login streak', 'icon' => 'fas fa-fire', 'points' => 50, 'tier' => 'silver'],
        'STREAK_GOLD' => ['name' => 'Monthly Maven', 'description' => '30-day login streak', 'icon' => 'fas fa-fire', 'points' => 200, 'tier' => 'gold'],

        // 2. Assessment Ace — assessments passed
        'ACE_BRONZE' => ['name' => 'Quiz Taker', 'description' => 'Passed 5 assessments', 'icon' => 'fas fa-clipboard-check', 'points' => 25, 'tier' => 'bronze'],
        'ACE_SILVER' => ['name' => 'Assessment Ace', 'description' => 'Passed 15 assessments', 'icon' => 'fas fa-clipboard-check', 'points' => 75, 'tier' => 'silver'],
        'ACE_GOLD' => ['name' => 'Exam Master', 'description' => 'Passed 30 assessments', 'icon' => 'fas fa-clipboard-check', 'points' => 150, 'tier' => 'gold'],

        // 3. Knowledge Seeker — topics viewed
        'SEEKER_BRONZE' => ['name' => 'Curious Mind', 'description' => 'Viewed 10 topics', 'icon' => 'fas fa-book-reader', 'points' => 20, 'tier' => 'bronze'],
        'SEEKER_SILVER' => ['name' => 'Knowledge Seeker', 'description' => 'Viewed 30 topics', 'icon' => 'fas fa-book-reader', 'points' => 60, 'tier' => 'silver'],
        'SEEKER_GOLD' => ['name' => 'Scholar', 'description' => 'Viewed 50 topics', 'icon' => 'fas fa-book-reader', 'points' => 120, 'tier' => 'gold'],

        // 4. Perfect Score — perfect scores achieved
        'PERFECT_BRONZE' => ['name' => 'Sharp Shooter', 'description' => 'Scored 100% on 1 assessment', 'icon' => 'fas fa-bullseye', 'points' => 25, 'tier' => 'bronze'],
        'PERFECT_SILVER' => ['name' => 'Perfectionist', 'description' => 'Scored 100% on 5 assessments', 'icon' => 'fas fa-bullseye', 'points' => 75, 'tier' => 'silver'],
        'PERFECT_GOLD' => ['name' => 'Flawless', 'description' => 'Scored 100% on 10 assessments', 'icon' => 'fas fa-bullseye', 'points' => 150, 'tier' => 'gold'],

        // 5. Module Completion — modules completed
        'MODULE_BRONZE' => ['name' => 'Module Starter', 'description' => 'Completed 1 module', 'icon' => 'fas fa-medal', 'points' => 30, 'tier' => 'bronze'],
        'MODULE_SILVER' => ['name' => 'Module Master', 'description' => 'Completed 3 modules', 'icon' => 'fas fa-medal', 'points' => 100, 'tier' => 'silver'],
        'MODULE_GOLD' => ['name' => 'Module Legend', 'description' => 'Completed all modules', 'icon' => 'fas fa-medal', 'points' => 250, 'tier' => 'gold'],

        // 6. Course Graduate — courses completed
        'COURSE_BRONZE' => ['name' => 'Learner', 'description' => 'Completed 1 course', 'icon' => 'fas fa-graduation-cap', 'points' => 50, 'tier' => 'bronze'],
        'COURSE_SILVER' => ['name' => 'Graduate', 'description' => 'Completed 3 courses', 'icon' => 'fas fa-graduation-cap', 'points' => 150, 'tier' => 'silver'],
        'COURSE_GOLD' => ['name' => 'Valedictorian', 'description' => 'Completed 5 courses', 'icon' => 'fas fa-graduation-cap', 'points' => 300, 'tier' => 'gold'],

        // 7. Leaderboard Rank
        'RANK_BRONZE' => ['name' => 'Rising Star', 'description' => 'Reached top 20 on the leaderboard', 'icon' => 'fas fa-crown', 'points' => 30, 'tier' => 'bronze'],
        'RANK_SILVER' => ['name' => 'Top Performer', 'description' => 'Reached top 10 on the leaderboard', 'icon' => 'fas fa-crown', 'points' => 75, 'tier' => 'silver'],
        'RANK_GOLD' => ['name' => 'Champion', 'description' => 'Reached #1 on the leaderboard', 'icon' => 'fas fa-crown', 'points' => 200, 'tier' => 'gold'],

        // 8. Points Collector — total points earned
        'POINTS_BRONZE' => ['name' => 'Coin Collector', 'description' => 'Earned 100 points', 'icon' => 'fas fa-coins', 'points' => 10, 'tier' => 'bronze'],
        'POINTS_SILVER' => ['name' => 'Point Hoarder', 'description' => 'Earned 500 points', 'icon' => 'fas fa-coins', 'points' => 50, 'tier' => 'silver'],
        'POINTS_GOLD' => ['name' => 'Treasury', 'description' => 'Earned 1,000 points', 'icon' => 'fas fa-coins', 'points' => 100, 'tier' => 'gold'],

        // 9. First Steps — milestones
        'FIRST_LOGIN' => ['name' => 'First Steps', 'description' => 'Logged in for the first time', 'icon' => 'fas fa-door-open', 'points' => 10, 'tier' => 'bronze'],
        'FIRST_SUBMIT' => ['name' => 'First Submission', 'description' => 'Submitted your first activity', 'icon' => 'fas fa-paper-plane', 'points' => 25, 'tier' => 'silver'],
        'FIRST_PERFECT' => ['name' => 'First Perfect', 'description' => 'Achieved your first 100% score', 'icon' => 'fas fa-star', 'points' => 50, 'tier' => 'gold'],
    ];

    public static function get(string $key): ?array
    {
        return self::DEFINITIONS[$key] ?? null;
    }

    public static function all(): array
    {
        return self::DEFINITIONS;
    }

    /**
     * Get achievements grouped by category (strips tier suffix).
     */
    public static function grouped(): array
    {
        $groups = [];
        foreach (self::DEFINITIONS as $key => $def) {
            // Group by prefix before last underscore (STREAK, ACE, SEEKER, etc.)
            $lastUnderscore = strrpos($key, '_');
            $group = substr($key, 0, $lastUnderscore);
            $groups[$group][$key] = $def;
        }
        return $groups;
    }
}

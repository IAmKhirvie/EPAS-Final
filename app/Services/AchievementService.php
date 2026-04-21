<?php

namespace App\Services;

use App\Constants\Achievements;
use App\Models\User;
use App\Models\UserAchievement;
use App\Models\UserProgress;
use App\Models\Module;
use App\Models\Course;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class AchievementService
{
    protected GamificationService $gamificationService;

    public function __construct(GamificationService $gamificationService)
    {
        $this->gamificationService = $gamificationService;
    }

    /**
     * Check and award achievements based on a trigger event.
     */
    public function checkAndAward(User $user, string $trigger): ?UserAchievement
    {
        $awarded = null;

        try {
            $awarded = match ($trigger) {
                'first_login' => $this->award($user, 'FIRST_LOGIN'),
                'first_submit' => $this->award($user, 'FIRST_SUBMIT'),
                'streak' => $this->checkStreaks($user),
                'perfect_score' => $this->checkPerfectScores($user),
                'module_complete' => $this->checkModules($user),
                'course_complete' => $this->checkCourses($user),
                'leaderboard' => $this->checkRank($user),
                'assessment_pass' => $this->checkAce($user),
                'topic_view' => $this->checkSeeker($user),
                'points' => $this->checkPoints($user),
                default => null,
            };
        } catch (\Exception $e) {
            Log::error("Achievement check failed for user {$user->id}, trigger: {$trigger}", [
                'error' => $e->getMessage(),
            ]);
        }

        return $awarded;
    }

    /**
     * Get all achievements for a user (earned + locked).
     */
    public function getUserAchievements(User $user): Collection
    {
        $earned = UserAchievement::where('user_id', $user->id)
            ->pluck('earned_at', 'achievement_key')
            ->toArray();

        return collect(Achievements::all())->map(function ($definition, $key) use ($earned) {
            return array_merge($definition, [
                'key' => $key,
                'earned' => isset($earned[$key]),
                'earned_at' => $earned[$key] ?? null,
            ]);
        })->values();
    }

    protected function award(User $user, string $key): ?UserAchievement
    {
        if (UserAchievement::where('user_id', $user->id)->where('achievement_key', $key)->exists()) {
            return null;
        }

        $definition = Achievements::get($key);
        if (!$definition) {
            return null;
        }

        $achievement = UserAchievement::create([
            'user_id' => $user->id,
            'achievement_key' => $key,
            'earned_at' => now(),
        ]);

        $this->gamificationService->awardPoints(
            $user,
            $definition['points'],
            "Achievement unlocked: {$definition['name']}",
            $achievement
        );

        return $achievement;
    }

    // ===== STREAK (3 / 7 / 30 days) =====
    protected function checkStreaks(User $user): ?UserAchievement
    {
        $streak = $user->current_streak;
        $awarded = null;
        if ($streak >= 3) $awarded = $this->award($user, 'STREAK_BRONZE') ?? $awarded;
        if ($streak >= 7) $awarded = $this->award($user, 'STREAK_SILVER') ?? $awarded;
        if ($streak >= 30) $awarded = $this->award($user, 'STREAK_GOLD') ?? $awarded;
        return $awarded;
    }

    // ===== ASSESSMENT ACE (5 / 15 / 30 passed) =====
    protected function checkAce(User $user): ?UserAchievement
    {
        $passed = UserProgress::where('user_id', $user->id)
            ->where('status', 'passed')
            ->count();
        $awarded = null;
        if ($passed >= 5) $awarded = $this->award($user, 'ACE_BRONZE') ?? $awarded;
        if ($passed >= 15) $awarded = $this->award($user, 'ACE_SILVER') ?? $awarded;
        if ($passed >= 30) $awarded = $this->award($user, 'ACE_GOLD') ?? $awarded;
        return $awarded;
    }

    // ===== KNOWLEDGE SEEKER (10 / 30 / 50 topics) =====
    protected function checkSeeker(User $user): ?UserAchievement
    {
        $viewed = UserProgress::where('user_id', $user->id)
            ->where('progressable_type', 'App\\Models\\Topic')
            ->count();
        $awarded = null;
        if ($viewed >= 10) $awarded = $this->award($user, 'SEEKER_BRONZE') ?? $awarded;
        if ($viewed >= 30) $awarded = $this->award($user, 'SEEKER_SILVER') ?? $awarded;
        if ($viewed >= 50) $awarded = $this->award($user, 'SEEKER_GOLD') ?? $awarded;
        return $awarded;
    }

    // ===== PERFECT SCORE (1 / 5 / 10 perfect scores) =====
    protected function checkPerfectScores(User $user): ?UserAchievement
    {
        $perfects = UserProgress::where('user_id', $user->id)
            ->whereNotNull('score')
            ->whereNotNull('max_score')
            ->whereColumn('score', '=', 'max_score')
            ->where('max_score', '>', 0)
            ->count();
        $awarded = null;
        if ($perfects >= 1) $awarded = $this->award($user, 'PERFECT_BRONZE') ?? $awarded;
        if ($perfects >= 5) $awarded = $this->award($user, 'PERFECT_SILVER') ?? $awarded;
        if ($perfects >= 10) $awarded = $this->award($user, 'PERFECT_GOLD') ?? $awarded;

        // Also check first perfect milestone
        if ($perfects >= 1) $awarded = $this->award($user, 'FIRST_PERFECT') ?? $awarded;

        return $awarded;
    }

    // ===== MODULE COMPLETION (1 / 3 / all) =====
    protected function checkModules(User $user): ?UserAchievement
    {
        $completed = UserProgress::where('user_id', $user->id)
            ->where('progressable_type', Module::class)
            ->where('status', 'completed')
            ->count();
        $total = Module::where('is_active', true)->count();
        $awarded = null;
        if ($completed >= 1) $awarded = $this->award($user, 'MODULE_BRONZE') ?? $awarded;
        if ($completed >= 3) $awarded = $this->award($user, 'MODULE_SILVER') ?? $awarded;
        if ($total > 0 && $completed >= $total) $awarded = $this->award($user, 'MODULE_GOLD') ?? $awarded;
        return $awarded;
    }

    // ===== COURSE COMPLETION (1 / 3 / 5) =====
    protected function checkCourses(User $user): ?UserAchievement
    {
        $completed = \App\Models\Certificate::where('user_id', $user->id)
            ->where('status', 'issued')
            ->distinct('course_id')
            ->count('course_id');
        $awarded = null;
        if ($completed >= 1) $awarded = $this->award($user, 'COURSE_BRONZE') ?? $awarded;
        if ($completed >= 3) $awarded = $this->award($user, 'COURSE_SILVER') ?? $awarded;
        if ($completed >= 5) $awarded = $this->award($user, 'COURSE_GOLD') ?? $awarded;
        return $awarded;
    }

    // ===== LEADERBOARD RANK (top 20 / top 10 / #1) =====
    protected function checkRank(User $user): ?UserAchievement
    {
        $rank = $this->gamificationService->getUserRank($user);
        $awarded = null;
        if ($rank <= 20) $awarded = $this->award($user, 'RANK_BRONZE') ?? $awarded;
        if ($rank <= 10) $awarded = $this->award($user, 'RANK_SILVER') ?? $awarded;
        if ($rank === 1) $awarded = $this->award($user, 'RANK_GOLD') ?? $awarded;
        return $awarded;
    }

    // ===== POINTS COLLECTOR (100 / 500 / 1000) =====
    protected function checkPoints(User $user): ?UserAchievement
    {
        $points = $user->total_points ?? 0;
        $awarded = null;
        if ($points >= 100) $awarded = $this->award($user, 'POINTS_BRONZE') ?? $awarded;
        if ($points >= 500) $awarded = $this->award($user, 'POINTS_SILVER') ?? $awarded;
        if ($points >= 1000) $awarded = $this->award($user, 'POINTS_GOLD') ?? $awarded;
        return $awarded;
    }
}

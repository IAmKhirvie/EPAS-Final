<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Services\AchievementService;
use App\Services\CertificateService;
use App\Services\GamificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CredentialsController extends Controller
{
    protected CertificateService $certificateService;

    public function __construct(CertificateService $certificateService)
    {
        $this->certificateService = $certificateService;
    }

    public function index(Request $request)
    {
        $user = Auth::user();

        $certificates = $this->certificateService->getUserCertificates($user);

        $gamificationService = app(GamificationService::class);
        $stats = $gamificationService->getUserStats($user);

        // Leaderboard filtering
        $leaderboardFilter = $request->get('leaderboard', 'all');
        $section = null;
        $courseId = null;

        if ($leaderboardFilter === 'section' && $user->section) {
            $section = $user->section;
        } elseif ($leaderboardFilter === 'course' && $request->get('course_id')) {
            $courseId = (int) $request->get('course_id');
        }

        $leaderboard = $gamificationService->getLeaderboard(20, $section, $courseId);
        $sections = $gamificationService->getAvailableSections();
        $courses = Course::where('is_active', true)->orderBy('course_name')->get(['id', 'course_name']);

        // Achievement showcase — also retroactively check achievements
        $achievementService = app(AchievementService::class);
        $achievementService->checkAndAward($user, 'first_login');
        $achievementService->checkAndAward($user, 'streak');
        $achievementService->checkAndAward($user, 'assessment_pass');
        $achievementService->checkAndAward($user, 'topic_view');
        $achievementService->checkAndAward($user, 'perfect_score');
        $achievementService->checkAndAward($user, 'module_complete');
        $achievementService->checkAndAward($user, 'course_complete');
        $achievementService->checkAndAward($user, 'leaderboard');
        $achievementService->checkAndAward($user, 'points');
        $achievements = $achievementService->getUserAchievements($user);

        return view('credentials.index', compact(
            'certificates',
            'stats',
            'leaderboard',
            'leaderboardFilter',
            'sections',
            'courses',
            'achievements'
        ));
    }
}

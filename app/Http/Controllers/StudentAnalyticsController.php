<?php

namespace App\Http\Controllers;

use App\Constants\Roles;
use App\Models\Module;
use App\Services\GradingService;
use Illuminate\Http\Request;

class StudentAnalyticsController extends Controller
{
    public function __construct(private GradingService $gradingService)
    {
    }

    /**
     * Display the student analytics dashboard.
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // Verify user is a student
        if ($user->role !== Roles::STUDENT) {
            return redirect()->route('dashboard')
                ->with('error', 'Analytics dashboard is only available for students.');
        }

        $analytics = $this->gradingService->getStudentAnalytics($user);

        return view('analytics.student-dashboard', compact('analytics'));
    }

    /**
     * Display detailed analytics for a specific module.
     */
    public function moduleDetail(Module $module)
    {
        $user = auth()->user();

        // Verify user is a student
        if ($user->role !== Roles::STUDENT) {
            return redirect()->route('dashboard')
                ->with('error', 'Analytics dashboard is only available for students.');
        }

        $moduleGrade = $this->gradingService->calculateModuleGrade($user, $module);
        $ranking = $this->gradingService->getModuleRanking($user, $module);

        return view('analytics.module-detail', compact('module', 'moduleGrade', 'ranking'));
    }
}

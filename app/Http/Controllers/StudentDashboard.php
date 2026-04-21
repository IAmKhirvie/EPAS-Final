<?php

namespace App\Http\Controllers;

use App\Models\UserProgress;
use App\Models\Module;
use App\Models\InformationSheet;
use App\Models\Announcement;
use App\Models\PendingActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class StudentDashboard extends Controller
{
    public function index()
    {
        return view('dashboard');
    }
    
    public function getProgressData()
    {
        try {
            $user = Auth::user();

            // Get progress summary with circle progress data
            $progressData = $this->getCircleProgressData($user);

            // Get recent announcements
            $announcements = $this->getAnnouncements();

            // Get pending activities
            $pendingActivities = $this->getPendingActivities($user);

            // Get current module progress
            $currentModule = $this->getCurrentModuleProgress($user);

            return response()->json([
                'circleProgress' => $progressData,
                'announcements' => $announcements,
                'pendingActivities' => $pendingActivities,
                'currentModule' => $currentModule
            ]);
        } catch (\Exception $e) {
            Log::error('StudentDashboard::getProgressData failed', [
                'error' => $e->getMessage(),
                'user' => auth()->id(),
            ]);
            return response()->json(['error' => 'Failed to load progress data.'], 500);
        }
    }
    
    private function getCircleProgressData($user)
    {
        // Get total activities (information sheets + self checks) for student's section
        $section = $user->section;
        $totalActivities = InformationSheet::whereHas('module', function($query) use ($section) {
            $query->where('is_active', true)
                  ->whereHas('course', fn($q) => $q->forSection($section));
        })->count();

        $totalActivities += \App\Models\SelfCheck::whereHas('informationSheet.module', function($query) use ($section) {
            $query->where('is_active', true)
                  ->whereHas('course', fn($q) => $q->forSection($section));
        })->count();
        
        // Get completed activities
        $completedActivities = UserProgress::where('user_id', $user->id)
            ->whereIn('progressable_type', ['App\Models\InformationSheet', 'App\Models\SelfCheck'])
            ->where('status', 'completed')
            ->count();
            
        // Calculate average grade from self checks
        $averageGrade = UserProgress::where('user_id', $user->id)
            ->where('progressable_type', 'App\Models\SelfCheck')
            ->whereNotNull('score')
            ->avg('score') ?? 0;
            
        $progressPercentage = $totalActivities > 0 ? ($completedActivities / $totalActivities) * 100 : 0;
        
        return [
            'total_activities' => $totalActivities,
            'completed_activities' => $completedActivities,
            'progress_percentage' => round($progressPercentage, 1),
            'average_grade' => round($averageGrade, 1)
        ];
    }
    
    private function getAnnouncements()
    {
        $user = auth()->user();
        $query = Announcement::where('is_active', true)
            ->with('author'); // Eager load to prevent N+1

        // Filter by user role
        if ($user) {
            $query->forUser($user);
        }

        return $query->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function($announcement) {
                return [
                    'id' => $announcement->id,
                    'title' => $announcement->title,
                    'content' => $announcement->content,
                    'created_at' => $announcement->created_at->diffForHumans(),
                    'author' => $announcement->author->name ?? 'Admin'
                ];
            });
    }
    
    private function getPendingActivities($user)
    {
        return PendingActivity::where('user_id', $user->id)
            ->where('status', 'pending')
            ->with('module')
            ->orderBy('deadline')
            ->get()
            ->map(function($activity) {
                return [
                    'id' => $activity->id,
                    'title' => $activity->title,
                    'module_name' => $activity->module->module_name ?? 'General',
                    'deadline' => $activity->deadline ? Carbon::parse($activity->deadline)->format('M j, Y') : 'No deadline',
                    'is_urgent' => $activity->deadline && Carbon::parse($activity->deadline)->isToday()
                ];
            });
    }
    
    private function getCurrentModuleProgress($user)
    {
        // Get the most recently accessed module or the first incomplete module
        $latestProgress = UserProgress::where('user_id', $user->id)
            ->where('progressable_type', 'App\Models\InformationSheet')
            ->orderBy('updated_at', 'desc')
            ->first();
            
        if ($latestProgress) {
            $module = $latestProgress->progressable->module ?? Module::where('is_active', true)->whereHas('course', fn($q) => $q->forSection($user->section))->first();
        } else {
            $module = Module::where('is_active', true)->whereHas('course', fn($q) => $q->forSection($user->section))->first();
        }
        
        if (!$module) {
            return null;
        }
        
        $completedSheets = UserProgress::where('user_id', $user->id)
            ->where('progressable_type', 'App\Models\InformationSheet')
            ->whereIn('progressable_id', $module->informationSheets->pluck('id'))
            ->where('status', 'completed')
            ->count();
            
        $totalSheets = $module->informationSheets->count();
        $moduleProgress = $totalSheets > 0 ? ($completedSheets / $totalSheets) * 100 : 0;
        
        return [
            'module_name' => $module->module_name,
            'progress_percentage' => round($moduleProgress),
            'completed_sheets' => $completedSheets,
            'total_sheets' => $totalSheets,
            'next_sheet' => $completedSheets < $totalSheets ? $module->informationSheets[$completedSheets]->title ?? 'Next Sheet' : 'Module Complete'
        ];
    }
}
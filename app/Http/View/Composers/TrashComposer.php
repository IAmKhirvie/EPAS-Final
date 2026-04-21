<?php

namespace App\Http\View\Composers;

use App\Constants\Roles;
use App\Models\Module;
use App\Models\Topic;
use App\Models\InformationSheet;
use App\Models\Homework;
use App\Models\SelfCheck;
use App\Models\TaskSheet;
use App\Models\JobSheet;
use App\Models\Checklist;
use App\Models\Course;
use App\Models\Announcement;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class TrashComposer
{
    public function compose(View $view)
    {
        if (!Auth::check()) {
            $view->with('trashedCount', 0);
            return;
        }

        $user = Auth::user();

        // Only compute for instructors and admins
        if (!in_array($user->role, [Roles::INSTRUCTOR, Roles::ADMIN])) {
            $view->with('trashedCount', 0);
            return;
        }

        // Cache the count for 5 minutes to avoid repeated queries
        $cacheKey = "trash_count_{$user->id}";
        $trashedCount = Cache::remember($cacheKey, 300, function () use ($user) {
            return $this->computeTrashedCount($user);
        });

        $view->with('trashedCount', $trashedCount);
    }

    private function computeTrashedCount($user): int
    {
        $isAdmin = $user->role === Roles::ADMIN;
        $count = 0;

        if ($isAdmin) {
            // Admin sees all trashed items
            $count += Module::onlyTrashed()->count();
            $count += Topic::onlyTrashed()->count();
            $count += InformationSheet::onlyTrashed()->count();
            $count += Homework::onlyTrashed()->count();
            $count += SelfCheck::onlyTrashed()->count();
            $count += TaskSheet::onlyTrashed()->count();
            $count += JobSheet::onlyTrashed()->count();
            $count += Checklist::onlyTrashed()->count();
            $count += Course::onlyTrashed()->count();
            $count += Announcement::onlyTrashed()->count();
        } else {
            // Instructor sees only their course's trashed items
            $instructorCourseIds = Course::where('instructor_id', $user->id)->pluck('id')->toArray();

            if (!empty($instructorCourseIds)) {
                $count += Module::onlyTrashed()->whereIn('course_id', $instructorCourseIds)->count();

                // FIX: Topic belongs to InformationSheet, which belongs to Module.
                // Do NOT use direct module() relationship (it expects a missing module_id column).
                $count += Topic::onlyTrashed()
                    ->whereHas('informationSheet.module', fn($q) => $q->whereIn('course_id', $instructorCourseIds))
                    ->count();

                $count += InformationSheet::onlyTrashed()
                    ->whereHas('module', fn($q) => $q->whereIn('course_id', $instructorCourseIds))
                    ->count();

                $count += Homework::onlyTrashed()
                    ->whereHas('informationSheet.module', fn($q) => $q->whereIn('course_id', $instructorCourseIds))
                    ->count();

                $count += SelfCheck::onlyTrashed()
                    ->whereHas('informationSheet.module', fn($q) => $q->whereIn('course_id', $instructorCourseIds))
                    ->count();

                $count += TaskSheet::onlyTrashed()
                    ->whereHas('informationSheet.module', fn($q) => $q->whereIn('course_id', $instructorCourseIds))
                    ->count();

                $count += JobSheet::onlyTrashed()
                    ->whereHas('informationSheet.module', fn($q) => $q->whereIn('course_id', $instructorCourseIds))
                    ->count();

                $count += Checklist::onlyTrashed()
                    ->whereHas('informationSheet.module', fn($q) => $q->whereIn('course_id', $instructorCourseIds))
                    ->count();
            }

            // Instructor's own announcements
            $count += Announcement::onlyTrashed()->where('user_id', $user->id)->count();
        }

        return $count;
    }
}
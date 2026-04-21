<?php

namespace App\Http\Controllers;

use App\Constants\Roles;
use App\Models\Course;
use App\Models\Module;
use App\Models\Announcement;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SearchController extends Controller
{
    public function search(Request $request): JsonResponse
    {
        $q = trim($request->input('q', ''));
        if (strlen($q) < 2) {
            return response()->json([]);
        }

        $user = Auth::user();
        $results = [];

        // Search Courses
        $courses = Course::where('is_active', true)
            ->where(function ($query) use ($q) {
                $query->where('course_name', 'like', "%{$q}%")
                    ->orWhere('course_code', 'like', "%{$q}%");
            })
            ->limit(3)
            ->get(['id', 'course_name', 'course_code']);

        foreach ($courses as $course) {
            $results[] = [
                'type' => 'Course',
                'icon' => 'fas fa-book-open',
                'title' => $course->course_name,
                'sub' => $course->course_code,
                'url' => route('courses.show', $course->id),
            ];
        }

        // Search Modules
        $modules = Module::where('is_active', true)
            ->where(function ($query) use ($q) {
                $query->where('module_name', 'like', "%{$q}%")
                    ->orWhere('module_number', 'like', "%{$q}%")
                    ->orWhere('module_title', 'like', "%{$q}%");
            })
            ->with('course:id,course_name')
            ->limit(3)
            ->get(['id', 'course_id', 'module_name', 'module_number']);

        foreach ($modules as $module) {
            $results[] = [
                'type' => 'Module',
                'icon' => 'fas fa-cube',
                'title' => $module->module_name,
                'sub' => $module->course->course_name ?? $module->module_number,
                'url' => route('courses.modules.show', [$module->course_id, $module->id]),
            ];
        }

        // Search Announcements
        $announcements = Announcement::where(function ($query) use ($q) {
                $query->where('title', 'like', "%{$q}%")
                    ->orWhere('content', 'like', "%{$q}%");
            })
            ->limit(3)
            ->get(['id', 'title']);

        foreach ($announcements as $ann) {
            $results[] = [
                'type' => 'Announcement',
                'icon' => 'fas fa-bullhorn',
                'title' => $ann->title,
                'sub' => 'Announcement',
                'url' => route('private.announcements.show', $ann->id),
            ];
        }

        // Search Users (admin/instructor only)
        if (in_array($user->role, [Roles::ADMIN, Roles::INSTRUCTOR])) {
            $users = User::where('stat', 1)
                ->where(function ($query) use ($q) {
                    $query->where('first_name', 'like', "%{$q}%")
                        ->orWhere('last_name', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%")
                        ->orWhere('student_id', 'like', "%{$q}%");
                })
                ->limit(3)
                ->get(['id', 'first_name', 'last_name', 'email', 'role']);

            foreach ($users as $u) {
                $results[] = [
                    'type' => ucfirst($u->role),
                    'icon' => $u->role === 'student' ? 'fas fa-user-graduate' : ($u->role === 'instructor' ? 'fas fa-chalkboard-teacher' : 'fas fa-user-shield'),
                    'title' => $u->first_name . ' ' . $u->last_name,
                    'sub' => $u->email,
                    'url' => route('private.users.edit', $u->id),
                ];
            }
        }

        return response()->json($results);
    }
}

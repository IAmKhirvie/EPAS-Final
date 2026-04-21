<?php

namespace App\Http\View\Composers;

use App\Models\Announcement;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class AnnouncementComposer
{
    public function compose(View $view)
    {
        if (Auth::check()) {
            $user = Auth::user();

            $recentAnnouncements = Cache::remember(
                "announcements_navbar_{$user->id}",
                60, // 1 minute
                function () use ($user) {
                    return Announcement::with(['user'])
                        ->forUser($user)
                        ->where(function($query) {
                            $query->whereNull('publish_at')
                                ->orWhere('publish_at', '<=', now());
                        })
                        ->orderBy('is_pinned', 'desc')
                        ->orderBy('created_at', 'desc')
                        ->limit(5)
                        ->get();
                }
            );

            $view->with([
                'recentAnnouncements' => $recentAnnouncements,
                'recentAnnouncementsCount' => $recentAnnouncements->count(),
            ]);
        } else {
            $view->with([
                'recentAnnouncements' => collect(),
                'recentAnnouncementsCount' => 0,
            ]);
        }
    }
}

<?php

namespace App\Http\Controllers;


use App\Models\Announcement;
use App\Models\AnnouncementComment;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Http\Traits\SanitizesContent;
use App\Http\Requests\StoreAnnouncementRequest;
use App\Http\Requests\UpdateAnnouncementRequest;

class AnnouncementController extends Controller
{
    use SanitizesContent;
    public function index()
    {
        return view('private.announcements.index');
    }

    public function create()
    {
        return view('private.announcements.create');
    }

    public function store(StoreAnnouncementRequest $request)
    {
        $validated = $request->validated();

        try {
            $targetRoles = $request->target_roles;
            if (empty($targetRoles)) {
                $targetRoles = 'all';
            }

            $announcement = Announcement::create([
                'title' => $this->stripHtml($request->title),
                'content' => $this->sanitizeContent($request->content),
                'user_id' => Auth::id(),
                'is_pinned' => $request->is_pinned ?? false,
                'is_urgent' => $request->is_urgent ?? false,
                'publish_at' => $request->publish_at,
                'deadline' => $request->deadline,
                'target_roles' => $targetRoles,
                'target_sections' => $request->target_sections,
            ]);

            app(NotificationService::class)->notifyNewAnnouncement($announcement);

            return redirect()->route('private.announcements.index')
                ->with('success', 'Announcement created successfully.');
        } catch (\Exception $e) {
            Log::error('Announcement creation failed', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);
            return back()->withInput()->with('error', 'Failed to create announcement. Please try again.');
        }
    }

    public function edit(Announcement $announcement)
    {
        return view('private.announcements.edit', compact('announcement'));
    }

    public function update(UpdateAnnouncementRequest $request, Announcement $announcement)
    {
        try {
            $announcement->update([
                'title' => $this->stripHtml($request->title),
                'content' => $this->sanitizeContent($request->content),
                'is_pinned' => $request->is_pinned ?? false,
                'is_urgent' => $request->is_urgent ?? false,
                'publish_at' => $request->publish_at,
                'deadline' => $request->deadline,
                'target_roles' => $request->target_roles ?? 'all',
                'target_sections' => $request->target_sections ?? '',
            ]);

            return redirect()->route('private.announcements.show', $announcement)
                ->with('success', 'Announcement updated successfully.');
        } catch (\Exception $e) {
            Log::error('Announcement update failed', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'announcement_id' => $announcement->id,
            ]);
            return back()->withInput()->with('error', 'Failed to update announcement. Please try again.');
        }
    }

    public function show(Announcement $announcement)
    {
        $announcement->load(['user', 'comments.user']);
        return view('private.announcements.show', compact('announcement'));
    }

    public function destroy(Announcement $announcement)
    {
        $user = Auth::user();

        // Only admin or the author can delete
        if ($user->role !== \App\Constants\Roles::ADMIN && $announcement->user_id !== $user->id) {
            return back()->with('error', 'You do not have permission to delete this announcement.');
        }

        try {
            $announcement->delete();
            return redirect()->route('private.announcements.index')
                ->with('success', 'Announcement deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Announcement deletion failed', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'announcement_id' => $announcement->id,
            ]);
            return back()->with('error', 'Failed to delete announcement.');
        }
    }

    public function addComment(Request $request, Announcement $announcement)
    {
        $request->validate([
            'comment' => 'required|string|max:1000'
        ]);

        try {
            AnnouncementComment::create([
                'announcement_id' => $announcement->id,
                'user_id' => Auth::id(),
                'comment' => $this->sanitizeContent($request->comment)
            ]);

            return back()->with('success', 'Comment added successfully.');
        } catch (\Exception $e) {
            Log::error('Announcement comment creation failed', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);
            return back()->withInput()->with('error', 'Failed to add comment. Please try again.');
        }
    }

    public function unreadCount()
    {
        $user = auth()->user();
        $query = Announcement::query();

        if ($user) {
            $query->forUser($user);
        }

        // Count announcements from last 7 days as "unread"
        $count = $query->where('created_at', '>=', now()->subDays(7))
            ->where(function ($q) {
                $q->whereNull('publish_at')->orWhere('publish_at', '<=', now());
            })
            ->count();

        return response()->json(['count' => $count]);
    }

    public function getRecentAnnouncements()
    {
        $user = auth()->user();
        $query = Announcement::with('user');

        // Filter by user role if logged in
        if ($user) {
            $query->forUser($user);
        }

        $announcements = $query->orderBy('is_pinned', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($announcement) {
                return [
                    'id' => $announcement->id,
                    'title' => $announcement->title,
                    'content' => Str::limit($announcement->content, 100),
                    'is_urgent' => $announcement->is_urgent,
                    'is_pinned' => $announcement->is_pinned,
                    'author' => $announcement->user->full_name ?? $announcement->user->name,
                    'created_at' => $announcement->created_at->diffForHumans(),
                    'url' => route('private.announcements.show', $announcement->id)
                ];
            });

        return response()->json($announcements);
    }

    public static function createAutomaticAnnouncement($type, $content, $user, $targetRoles = 'all', $targetSections = null)
    {
        $titleMap = [
            'module' => 'New Module Created',
            'information_sheet' => 'New Information Sheet Added',
            'topic' => 'New Topic Published',
            'user_registered' => 'New User Registration',
            'user_approved' => 'User Account Approved',
            'comment' => 'New Comment Posted',
            'self_check' => 'New Self Check Available',
            'task_sheet' => 'New Task Sheet Added',
            'job_sheet' => 'New Job Sheet Available',
        ];

        $title = $titleMap[$type] ?? 'New Activity';

        $announcement = \App\Models\Announcement::create([
            'title' => $title,
            'content' => $content,
            'user_id' => $user->id,
            'is_pinned' => false,
            'is_urgent' => false,
            'target_roles' => $targetRoles,
            'target_sections' => $targetSections,
        ]);

        // Skip emails for automatic announcements to avoid blocking the request
        // (with sync queue driver, 100+ SMTP connections would hang the response)
        app(NotificationService::class)->notifyNewAnnouncement($announcement, sendEmail: false);

        return $announcement;
    }
}

<?php

namespace App\Listeners;

use App\Events\ContentCreated;
use App\Models\Announcement;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class CreateContentAnnouncement
{
    public function handle($event)
    {
        $user = Auth::user();
        $contentType = class_basename($event->model);
        $contentTitle = $event->model->title ?? $event->model->name ?? 'Unknown Content';
        
        $announcement = Announcement::create([
            'title' => "New {$contentType} Created",
            'content' => "{$user->first_name} {$user->last_name} has created a new {$contentType}: {$contentTitle}",
            'user_id' => $user->id,
            'is_pinned' => false,
            'is_urgent' => false,
            'target_roles' => $this->getTargetRoles($contentType),
        ]);

        // Broadcast the event
        broadcast(new ContentCreated($announcement, $announcement->target_roles));
    }

    private function getTargetRoles($contentType)
    {
        // Define which roles should see which announcements
        $roleMapping = [
            'Module' => 'all',
            'InformationSheet' => 'all', 
            'Topic' => 'all',
            'User' => 'admin,instructor',
            'SelfCheck' => 'all',
            'TaskSheet' => 'all',
            'JobSheet' => 'all',
            'Homework' => 'all',
        ];

        return $roleMapping[$contentType] ?? 'all';
    }
}
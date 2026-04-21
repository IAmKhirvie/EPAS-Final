<?php

namespace App\Livewire;

use App\Models\Announcement;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithPagination;

class AnnouncementList extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public string $search = '';
    public array $selectedAnnouncements = [];
    public bool $selectAll = false;
    public bool $readyToLoad = false;

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatedSelectAll(bool $value): void
    {
        $this->selectedAnnouncements = $value
            ? $this->getQuery()->pluck('id')->map(fn ($id) => (string) $id)->toArray()
            : [];
    }

    public function bulkDelete(): void
    {
        $user = Auth::user();
        if (!in_array($user->role, ['admin', 'instructor'])) {
            session()->flash('error', 'You do not have permission to delete announcements.');
            return;
        }

        $count = 0;
        $announcements = Announcement::whereIn('id', $this->selectedAnnouncements)->get();
        foreach ($announcements as $announcement) {
            $announcement->delete();
            $count++;
        }

        $this->selectedAnnouncements = [];
        $this->selectAll = false;
        session()->flash('success', "{$count} announcement(s) deleted.");
    }

    private function getQuery()
    {
        $user = Auth::user();

        $query = Announcement::with(['user', 'comments.user'])
            ->forUser($user);

        if ($this->search) {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('is_pinned', 'desc')
            ->orderBy('created_at', 'desc');
    }

    public function loadData(): void
    {
        $this->readyToLoad = true;
    }

    public function render()
    {
        $user = Auth::user();
        $canCreate = in_array($user->role, ['admin', 'instructor']);

        return view('livewire.announcement-list', [
            'announcements' => $this->readyToLoad ? $this->getQuery()->paginate(10) : new \Illuminate\Pagination\LengthAwarePaginator([], 0, 10),
            'canCreate' => $canCreate,
            'canManage' => $canCreate,
        ]);
    }
}

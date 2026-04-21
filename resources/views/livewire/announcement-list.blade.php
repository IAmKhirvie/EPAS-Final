<div wire:poll.30s wire:init="loadData">
    {{-- Flash Messages --}}
    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div class="d-flex align-items-center gap-2">
            <h4 class="mb-0">Announcements</h4>
            <span class="badge bg-success" title="Auto-refreshes every 30 seconds">
                <i class="fas fa-circle fa-xs me-1"></i> Live
            </span>
        </div>
        @if($canCreate)
            <a href="{{ route('private.announcements.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus me-1"></i> New Announcement
            </a>
        @endif
    </div>

    {{-- Search --}}
    <div class="mb-3">
        <input type="text" wire:model.live.debounce.300ms="search" class="form-control form-control-sm"
            placeholder="Search announcements...">
    </div>

    {{-- Bulk Actions --}}
    @if(count($selectedAnnouncements) > 0 && $canManage)
        <div class="d-flex flex-wrap gap-2 mb-3 p-2 bg-light rounded border">
            <span class="align-self-center text-muted small">{{ count($selectedAnnouncements) }} selected:</span>
            <button wire:click="bulkDelete" wire:confirm="Delete all selected announcements?" class="btn btn-danger btn-sm">
                <i class="fas fa-trash me-1"></i> Delete
            </button>
        </div>
    @endif

    {{-- Loading --}}
    <div wire:loading class="text-center py-2">
        <div class="spinner-border spinner-border-sm text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

    @if(!$readyToLoad)
    <div class="p-3">
        <x-skeleton type="table-row" :count="8" />
    </div>
    @else

    {{-- Select All --}}
    <div class="d-flex align-items-center gap-2 mb-2">
        <input type="checkbox" wire:model.live="selectAll" class="form-check-input" id="selectAllAnnouncements">
        <label class="form-check-label small text-muted" for="selectAllAnnouncements">Select All</label>
    </div>

    {{-- Announcement Cards --}}
    <div wire:loading.class="opacity-50">
        @forelse($announcements as $announcement)
            <div class="card mb-3 {{ $announcement->is_urgent ? 'border-danger' : ($announcement->is_pinned ? 'border-warning' : '') }}">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="d-flex align-items-start gap-2">
                            <input type="checkbox" wire:model.live="selectedAnnouncements" value="{{ $announcement->id }}" class="form-check-input mt-1">
                            <div>
                            <h5 class="card-title mb-1">
                                @if($announcement->is_pinned)
                                    <i class="fas fa-thumbtack text-warning me-1" title="Pinned"></i>
                                @endif
                                @if($announcement->is_urgent)
                                    <span class="badge bg-danger me-1">Urgent</span>
                                @endif
                                <a href="{{ route('private.announcements.show', $announcement) }}" class="text-decoration-none">
                                    {{ $announcement->title }}
                                </a>
                            </h5>
                            <small class="text-muted">
                                <i class="fas fa-user me-1"></i>{{ $announcement->user->full_name ?? 'Unknown' }}
                                <span class="mx-1">&bull;</span>
                                <i class="fas fa-clock me-1"></i>{{ $announcement->created_at->diffForHumans() }}
                                @if($announcement->deadline)
                                    <span class="mx-1">&bull;</span>
                                    <i class="fas fa-calendar-times me-1"></i>Due: {{ $announcement->deadline->format('M d, Y') }}
                                @endif
                            </small>
                            </div>
                        </div>
                        @if($announcement->comments_count ?? $announcement->comments->count())
                            <span class="badge bg-light text-dark">
                                <i class="fas fa-comment me-1"></i>{{ $announcement->comments->count() }}
                            </span>
                        @endif
                    </div>
                    <p class="card-text text-muted small mb-0">
                        {{ \Illuminate\Support\Str::limit(strip_tags($announcement->content), 200) }}
                    </p>
                </div>
            </div>
        @empty
            <div class="text-center text-muted py-5">
                <i class="fas fa-bullhorn fa-3x mb-3 opacity-50"></i>
                <p>No announcements found.</p>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($announcements->hasPages())
        <div class="d-flex justify-content-between align-items-center mt-3">
            <small class="text-muted">
                Showing {{ $announcements->firstItem() ?? 0 }}-{{ $announcements->lastItem() ?? 0 }} of {{ $announcements->total() }}
            </small>
            {{ $announcements->links() }}
        </div>
    @endif
    @endif
</div>

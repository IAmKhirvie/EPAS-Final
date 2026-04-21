<div wire:init="loadData">
    {{-- Flash Messages --}}
    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <h4 class="mb-0">Enrollment Requests</h4>
        @if($isInstructor)
            <a href="{{ route('enrollment-requests.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus me-1"></i> New Request
            </a>
        @endif
    </div>

    {{-- Status Tabs --}}
    <ul class="nav nav-tabs mb-3">
        <li class="nav-item">
            <button wire:click="$set('statusFilter', 'all')" class="nav-link {{ $statusFilter === 'all' ? 'active' : '' }}">
                All <span class="badge bg-secondary ms-1">{{ $counts['all'] ?? 0 }}</span>
            </button>
        </li>
        <li class="nav-item">
            <button wire:click="$set('statusFilter', 'pending')" class="nav-link {{ $statusFilter === 'pending' ? 'active' : '' }}">
                Pending <span class="badge bg-warning text-dark ms-1">{{ $counts['pending'] ?? 0 }}</span>
            </button>
        </li>
        <li class="nav-item">
            <button wire:click="$set('statusFilter', 'approved')" class="nav-link {{ $statusFilter === 'approved' ? 'active' : '' }}">
                Approved <span class="badge bg-success ms-1">{{ $counts['approved'] ?? 0 }}</span>
            </button>
        </li>
        <li class="nav-item">
            <button wire:click="$set('statusFilter', 'rejected')" class="nav-link {{ $statusFilter === 'rejected' ? 'active' : '' }}">
                Rejected <span class="badge bg-danger ms-1">{{ $counts['rejected'] ?? 0 }}</span>
            </button>
        </li>
    </ul>

    {{-- Search --}}
    <div class="d-flex flex-wrap gap-2 mb-3">
        <div class="flex-grow-1" style="min-width: 200px;">
            <input type="text" wire:model.live.debounce.300ms="search" class="form-control form-control-sm"
                placeholder="Search student name, email, section...">
        </div>
    </div>

    {{-- Bulk Actions --}}
    @if(count($selectedRequests) > 0 && $isAdmin)
        <div class="d-flex flex-wrap gap-2 mb-3 p-2 bg-light rounded border">
            <span class="align-self-center text-muted small">{{ count($selectedRequests) }} selected:</span>
            <button wire:click="bulkApprove" wire:confirm="Approve all selected pending requests?" class="btn btn-success btn-sm">
                <i class="fas fa-check me-1"></i> Approve
            </button>
            <button wire:click="bulkReject" wire:confirm="Reject all selected pending requests?" class="btn btn-danger btn-sm">
                <i class="fas fa-times me-1"></i> Reject
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

    {{-- Table --}}
    <div class="table-responsive" wire:loading.class="opacity-50">
        <table class="table table-hover table-sm align-middle">
            <thead class="table-light">
                <tr>
                    <th style="width: 40px;">
                        <input type="checkbox" wire:model.live="selectAll" class="form-check-input">
                    </th>
                    <th style="cursor:pointer;" wire:click="sortBy('id')">
                        ID
                        @if($sortField === 'id')
                            <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ms-1"></i>
                        @endif
                    </th>
                    <th style="cursor:pointer;" wire:click="sortBy('student_name')">
                        Student
                        @if($sortField === 'student_name')
                            <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ms-1"></i>
                        @endif
                    </th>
                    <th style="cursor:pointer;" wire:click="sortBy('section')">
                        Section
                        @if($sortField === 'section')
                            <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ms-1"></i>
                        @endif
                    </th>
                    <th>Instructor</th>
                    <th style="cursor:pointer;" wire:click="sortBy('status')">
                        Status
                        @if($sortField === 'status')
                            <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ms-1"></i>
                        @endif
                    </th>
                    <th>Notes</th>
                    <th style="cursor:pointer;" wire:click="sortBy('created_at')">
                        Date
                        @if($sortField === 'created_at')
                            <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ms-1"></i>
                        @endif
                    </th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($requests as $request)
                    <tr>
                        <td>
                            <input type="checkbox" wire:model.live="selectedRequests" value="{{ $request->id }}" class="form-check-input">
                        </td>
                        <td>{{ $request->id }}</td>
                        <td>
                            <div class="fw-medium">{{ $request->student_display_name }}</div>
                            <small class="text-muted">{{ $request->student_display_email }}</small>
                        </td>
                        <td><span class="badge bg-info">{{ $request->section }}</span></td>
                        <td>
                            <small>{{ $request->instructor?->full_name ?? '-' }}</small>
                        </td>
                        <td>
                            @switch($request->status)
                                @case('pending')
                                    <span class="badge bg-warning text-dark">Pending</span>
                                    @break
                                @case('approved')
                                    <span class="badge bg-success">Approved</span>
                                    @break
                                @case('rejected')
                                    <span class="badge bg-danger">Rejected</span>
                                    @break
                                @default
                                    <span class="badge bg-secondary">{{ ucfirst($request->status) }}</span>
                            @endswitch
                        </td>
                        <td>
                            @if($request->notes)
                                <small class="text-muted" title="{{ $request->notes }}">{{ \Illuminate\Support\Str::limit($request->notes, 30) }}</small>
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            <small>{{ $request->created_at->format('M d, Y') }}</small>
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                @if($request->isPending())
                                    @if($isAdmin)
                                        <button wire:click="approveRequest({{ $request->id }})" class="btn btn-outline-success btn-sm" title="Approve">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button wire:click="rejectRequest({{ $request->id }})" wire:confirm="Reject this enrollment request?" class="btn btn-outline-danger btn-sm" title="Reject">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    @endif
                                    @if($isInstructor && $request->instructor_id === auth()->id())
                                        <button wire:click="cancelRequest({{ $request->id }})" wire:confirm="Cancel this request?" class="btn btn-outline-warning btn-sm" title="Cancel">
                                            <i class="fas fa-undo"></i>
                                        </button>
                                    @endif
                                @endif
                                @if($request->processedBy)
                                    <small class="text-muted align-self-center ms-1">
                                        by {{ $request->processedBy->full_name }}
                                    </small>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">
                            <i class="fas fa-user-plus fa-2x mb-2 d-block opacity-50"></i>
                            No enrollment requests found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="d-flex justify-content-between align-items-center mt-3">
        <small class="text-muted">
            Showing {{ $requests->firstItem() ?? 0 }}-{{ $requests->lastItem() ?? 0 }} of {{ $requests->total() }}
        </small>
        {{ $requests->links() }}
    </div>
    @endif
</div>

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
        <h4 class="mb-0">Registration Management</h4>
    </div>

    {{-- Status Tabs --}}
    <ul class="nav nav-tabs mb-3">
        <li class="nav-item">
            <button wire:click="$set('statusFilter', 'pending')" class="nav-link {{ $statusFilter === 'pending' ? 'active' : '' }}">
                Pending <span class="badge bg-warning text-dark ms-1">{{ $counts['pending'] ?? 0 }}</span>
            </button>
        </li>
        <li class="nav-item">
            <button wire:click="$set('statusFilter', 'email_verified')" class="nav-link {{ $statusFilter === 'email_verified' ? 'active' : '' }}">
                Awaiting Approval <span class="badge bg-info ms-1">{{ $counts['email_verified'] ?? 0 }}</span>
            </button>
        </li>
        <li class="nav-item">
            <button wire:click="$set('statusFilter', 'rejected')" class="nav-link {{ $statusFilter === 'rejected' ? 'active' : '' }}">
                Rejected <span class="badge bg-danger ms-1">{{ $counts['rejected'] ?? 0 }}</span>
            </button>
        </li>
        <li class="nav-item">
            <button wire:click="$set('statusFilter', 'all')" class="nav-link {{ $statusFilter === 'all' ? 'active' : '' }}">
                All <span class="badge bg-secondary ms-1">{{ $counts['all'] ?? 0 }}</span>
            </button>
        </li>
    </ul>

    {{-- Search --}}
    <div class="d-flex flex-wrap gap-2 mb-3">
        <div class="flex-grow-1" style="min-width: 200px;">
            <input type="text" wire:model.live.debounce.300ms="search" class="form-control form-control-sm"
                placeholder="Search name, email...">
        </div>
    </div>

    {{-- Bulk Actions --}}
    @if(count($selectedRegistrations) > 0)
        <div class="d-flex flex-wrap gap-2 mb-3 p-2 bg-light rounded border">
            <span class="align-self-center text-muted small">{{ count($selectedRegistrations) }} selected:</span>
            <button wire:click="bulkApprove" wire:confirm="Approve selected registrations?" class="btn btn-success btn-sm">
                <i class="fas fa-check me-1"></i> Approve
            </button>
            <button wire:click="bulkReject" wire:confirm="Reject selected registrations?" class="btn btn-danger btn-sm">
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
                    <th style="cursor:pointer;" wire:click="sortBy('last_name')">
                        Name
                        @if($sortField === 'last_name' || $sortField === 'first_name')
                            <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ms-1"></i>
                        @endif
                    </th>
                    <th style="cursor:pointer;" wire:click="sortBy('email')">
                        Email
                        @if($sortField === 'email')
                            <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ms-1"></i>
                        @endif
                    </th>
                    <th style="cursor:pointer;" wire:click="sortBy('status')">
                        Status
                        @if($sortField === 'status')
                            <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ms-1"></i>
                        @endif
                    </th>
                    <th>Email Verified</th>
                    <th style="cursor:pointer;" wire:click="sortBy('created_at')">
                        Registered
                        @if($sortField === 'created_at')
                            <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ms-1"></i>
                        @endif
                    </th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($registrations as $reg)
                    <tr>
                        <td>
                            <input type="checkbox" wire:model.live="selectedRegistrations" value="{{ $reg->id }}" class="form-check-input">
                        </td>
                        <td>{{ $reg->id }}</td>
                        <td>
                            <div class="fw-medium">{{ $reg->full_name }}</div>
                        </td>
                        <td>{{ $reg->email }}</td>
                        <td>
                            @switch($reg->status)
                                @case('pending')
                                    <span class="badge bg-warning text-dark">Pending</span>
                                    @break
                                @case('email_verified')
                                    <span class="badge bg-info">Email Verified</span>
                                    @break
                                @case('approved')
                                    <span class="badge bg-success">Approved</span>
                                    @break
                                @case('rejected')
                                    <span class="badge bg-danger">Rejected</span>
                                    @break
                                @default
                                    <span class="badge bg-secondary">{{ ucfirst($reg->status) }}</span>
                            @endswitch
                        </td>
                        <td>
                            @if($reg->isEmailVerified())
                                <i class="fas fa-check-circle text-success"></i>
                            @else
                                <i class="fas fa-times-circle text-muted"></i>
                            @endif
                        </td>
                        <td>
                            <small>{{ $reg->created_at->format('M d, Y') }}</small>
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                @if($reg->status !== 'transferred' && $reg->status !== 'approved')
                                    <button wire:click="approveRegistration({{ $reg->id }})" class="btn btn-outline-success btn-sm" title="Approve">
                                        <i class="fas fa-check"></i>
                                    </button>
                                @endif
                                @if(!in_array($reg->status, ['transferred', 'rejected', 'approved']))
                                    <button wire:click="rejectRegistration({{ $reg->id }})" wire:confirm="Reject this registration?" class="btn btn-outline-danger btn-sm" title="Reject">
                                        <i class="fas fa-times"></i>
                                    </button>
                                @endif
                                @if(!$reg->isEmailVerified())
                                    <button wire:click="resendVerification({{ $reg->id }})" class="btn btn-outline-info btn-sm" title="Resend Verification">
                                        <i class="fas fa-envelope"></i>
                                    </button>
                                @endif
                                @if($reg->status === 'rejected')
                                    <button wire:click="deleteRegistration({{ $reg->id }})" wire:confirm="Delete this registration?" class="btn btn-outline-danger btn-sm" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="100%" class="text-center text-muted py-4">
                            <i class="fas fa-clipboard-list fa-2x mb-2 d-block opacity-50"></i>
                            No registrations found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="d-flex justify-content-between align-items-center mt-3">
        <small class="text-muted">
            Showing {{ $registrations->firstItem() ?? 0 }}-{{ $registrations->lastItem() ?? 0 }} of {{ $registrations->total() }}
        </small>
        {{ $registrations->links() }}
    </div>
    @endif
</div>

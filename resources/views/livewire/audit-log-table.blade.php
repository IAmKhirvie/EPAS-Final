<div wire:init="loadData">
    {{-- Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <h4 class="mb-0">Audit Logs</h4>
    </div>

    {{-- Filters --}}
    <div class="d-flex flex-wrap gap-2 mb-3">
        <div class="flex-grow-1" style="min-width: 200px;">
            <input type="text" wire:model.live.debounce.300ms="search" class="form-control form-control-sm"
                placeholder="Search description, action, IP, user...">
        </div>
        <select wire:model.live="actionFilter" class="form-select form-select-sm" style="max-width: 180px;">
            <option value="">All Actions</option>
            @foreach($actions as $action)
                <option value="{{ $action }}">{{ ucfirst(str_replace('_', ' ', $action)) }}</option>
            @endforeach
        </select>
        <input type="date" wire:model.live="dateFrom" class="form-control form-control-sm" style="max-width: 160px;" placeholder="From">
        <input type="date" wire:model.live="dateTo" class="form-control form-control-sm" style="max-width: 160px;" placeholder="To">
    </div>

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
                    <th>User</th>
                    <th style="cursor:pointer;" wire:click="sortBy('action')">
                        Action
                        @if($sortField === 'action')
                            <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ms-1"></i>
                        @endif
                    </th>
                    <th>Description</th>
                    <th style="cursor:pointer;" wire:click="sortBy('ip_address')">
                        IP Address
                        @if($sortField === 'ip_address')
                            <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ms-1"></i>
                        @endif
                    </th>
                    <th style="cursor:pointer;" wire:click="sortBy('created_at')">
                        Date
                        @if($sortField === 'created_at')
                            <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ms-1"></i>
                        @endif
                    </th>
                    <th style="width: 40px;"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                    <tr>
                        <td>
                            <input type="checkbox" wire:model.live="selectedLogs" value="{{ $log->id }}" class="form-check-input">
                        </td>
                        <td>{{ $log->id }}</td>
                        <td>
                            <small class="fw-medium">{{ $log->user?->full_name ?? 'System' }}</small>
                        </td>
                        <td>
                            <span class="badge bg-secondary">{{ ucfirst(str_replace('_', ' ', $log->action)) }}</span>
                        </td>
                        <td>
                            <small>{{ \Illuminate\Support\Str::limit($log->description, 60) }}</small>
                        </td>
                        <td><small class="text-muted">{{ $log->ip_address }}</small></td>
                        <td>
                            <small>{{ $log->created_at->format('M d, Y H:i') }}</small>
                        </td>
                        <td>
                            @if($log->old_values || $log->new_values)
                                <button wire:click="toggleExpand({{ $log->id }})" class="btn btn-outline-secondary btn-sm" title="Details">
                                    <i class="fas fa-{{ $expandedLogId === $log->id ? 'chevron-up' : 'chevron-down' }}"></i>
                                </button>
                            @endif
                        </td>
                    </tr>
                    @if($expandedLogId === $log->id)
                        <tr>
                            <td colspan="8" class="bg-light">
                                <div class="row p-2">
                                    @if($log->old_values)
                                        <div class="col-md-6">
                                            <strong class="text-danger small">Old Values:</strong>
                                            <pre class="small mb-0 mt-1" style="max-height: 200px; overflow-y: auto;">{{ json_encode($log->old_values, JSON_PRETTY_PRINT) }}</pre>
                                        </div>
                                    @endif
                                    @if($log->new_values)
                                        <div class="col-md-6">
                                            <strong class="text-success small">New Values:</strong>
                                            <pre class="small mb-0 mt-1" style="max-height: 200px; overflow-y: auto;">{{ json_encode($log->new_values, JSON_PRETTY_PRINT) }}</pre>
                                        </div>
                                    @endif
                                </div>
                                @if($log->url || $log->method)
                                    <div class="px-2 pb-2">
                                        <small class="text-muted">
                                            {{ $log->method }} {{ $log->url }}
                                        </small>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @endif
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            <i class="fas fa-clipboard-list fa-2x mb-2 d-block opacity-50"></i>
                            No audit logs found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="d-flex justify-content-between align-items-center mt-3">
        <small class="text-muted">
            Showing {{ $logs->firstItem() ?? 0 }}-{{ $logs->lastItem() ?? 0 }} of {{ $logs->total() }}
        </small>
        {{ $logs->links() }}
    </div>
    @endif
</div>

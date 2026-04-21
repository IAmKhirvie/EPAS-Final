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
        <h4 class="mb-0">{{ $this->pageTitle }}</h4>
        <div class="d-flex gap-2">
            @if($canCreate && Auth::user()->role === \App\Constants\Roles::ADMIN)
                <a href="{{ route('admin.certificates.index') }}" class="btn btn-outline-success btn-sm">
                    <i class="fas fa-certificate me-1"></i> Certificates
                </a>
                <form action="{{ route('admin.certificates.distribute') }}" method="POST"
                      onsubmit="return confirm('This will issue certificates to all students who completed all modules but don\'t have one yet. Continue?')">
                    @csrf
                    <button type="submit" class="btn btn-success btn-sm">
                        <i class="fas fa-paper-plane me-1"></i> Distribute Certificates
                    </button>
                </form>
            @endif
            @if($canCreate)
                <a href="{{ route('private.users.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus me-1"></i> Add User
                </a>
            @endif
        </div>
    </div>

    {{-- Filter Counts --}}
    <div class="d-flex flex-wrap gap-2 mb-3">
        <span class="badge bg-secondary">Total: {{ (int) ($filterCounts['total'] ?? 0) }}</span>
        @if(!$this->routeRoleFilter)
            <span class="badge bg-info">Students: {{ (int) ($filterCounts['students'] ?? 0) }}</span>
            <span class="badge bg-primary">Instructors: {{ (int) ($filterCounts['instructors'] ?? 0) }}</span>
            <span class="badge bg-dark">Admins: {{ (int) ($filterCounts['admins'] ?? 0) }}</span>
        @endif
        <span class="badge bg-success">Active: {{ (int) ($filterCounts['active'] ?? 0) }}</span>
        <span class="badge bg-warning text-dark">Pending: {{ (int) ($filterCounts['pending'] ?? 0) }}</span>
    </div>

    {{-- Search & Filters --}}
    <div class="d-flex flex-wrap gap-2 mb-3">
        <div class="flex-grow-1" style="min-width: 200px;">
            <input type="text" wire:model.live.debounce.300ms="search" class="form-control form-control-sm"
                placeholder="Search name, email, ID, section...">
        </div>
        @if(!$this->routeRoleFilter)
            <select wire:model.live="roleFilter" class="form-select form-select-sm" style="max-width: 160px;">
                <option value="">All Roles</option>
                <option value="student">Students</option>
                <option value="instructor">Instructors</option>
                <option value="admin">Admins</option>
            </select>
        @endif
        <select wire:model.live="statusFilter" class="form-select form-select-sm" style="max-width: 160px;">
            <option value="">All Status</option>
            <option value="active">Active</option>
            <option value="pending">Pending</option>
            <option value="unverified">Unverified</option>
        </select>
        @if(isset($isInstructorViewingStudents) && $isInstructorViewingStudents && count($instructorSections ?? []) > 1)
            <select wire:model.live="sectionFilter" class="form-select form-select-sm" style="max-width: 160px;">
                <option value="">All My Sections</option>
                @foreach($instructorSections as $section)
                    <option value="{{ $section }}">{{ $section }}</option>
                @endforeach
            </select>
        @endif
    </div>

    {{-- Bulk Actions --}}
    @if(count($selectedUsers) > 0 && $canDelete)
        <div class="d-flex flex-wrap gap-2 mb-3 p-2 bg-light rounded border">
            <span class="align-self-center text-muted small">{{ count($selectedUsers) }} selected:</span>
            <button wire:click="bulkActivate" wire:confirm="Activate selected users?" class="btn btn-success btn-sm">
                <i class="fas fa-check me-1"></i> Activate
            </button>
            <button wire:click="bulkDeactivate" wire:confirm="Deactivate selected users?" class="btn btn-warning btn-sm">
                <i class="fas fa-ban me-1"></i> Deactivate
            </button>
            <button wire:click="$toggle('showBulkAssign')" class="btn btn-info btn-sm">
                <i class="fas fa-users-cog me-1"></i> {{ $this->routeRoleFilter === 'instructor' ? 'Assign Advisory Class' : 'Assign Section' }}
            </button>
            <button wire:click="bulkDelete" wire:confirm="Delete selected users? This cannot be undone." class="btn btn-danger btn-sm">
                <i class="fas fa-trash me-1"></i> Delete
            </button>
        </div>

        @if($showBulkAssign)
            <div class="mb-3 p-3 bg-light rounded border">
                <div class="d-flex flex-wrap align-items-end gap-2">
                    @if($this->routeRoleFilter === 'instructor')
                        {{-- Instructor: Advisory Class dropdown + Department dropdown --}}
                        <div style="min-width: 180px;">
                            <label class="form-label small mb-1">Advisory Class</label>
                            <select wire:model="bulkSection" class="form-select form-select-sm">
                                <option value="">-- Select Section --</option>
                                @foreach($availableSections as $section)
                                    <option value="{{ $section }}">{{ $section }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div style="min-width: 180px;">
                            <label class="form-label small mb-1">Department</label>
                            <select wire:model="bulkDepartment" class="form-select form-select-sm">
                                <option value="">-- Select Department --</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @else
                        {{-- Student: Section text + School Year --}}
                        <div style="min-width: 160px;">
                            <label class="form-label small mb-1">Section / Batch</label>
                            <input type="text" wire:model="bulkSection" class="form-control form-control-sm"
                                placeholder="e.g., A1, Batch 1">
                        </div>
                        <div style="min-width: 160px;">
                            <label class="form-label small mb-1">School Year</label>
                            <input type="text" wire:model="bulkSchoolYear" class="form-control form-control-sm"
                                placeholder="e.g., 2025-2026">
                        </div>
                    @endif
                    <button wire:click="bulkAssignSection" wire:confirm="Assign to {{ count($selectedUsers) }} selected user(s)?"
                        class="btn btn-primary btn-sm">
                        <i class="fas fa-check me-1"></i> Apply
                    </button>
                    <button wire:click="$set('showBulkAssign', false)" class="btn btn-outline-secondary btn-sm">
                        Cancel
                    </button>
                </div>
            </div>
        @endif
    @endif

    {{-- Loading Indicator --}}
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
                    @if($canDelete)
                        <th style="width: 40px;">
                            <input type="checkbox" wire:model.live="selectAll" class="form-check-input">
                        </th>
                    @endif
                    <th style="cursor:pointer;" wire:click="sortBy('id')">
                        ID
                        @if($sortField === 'id')
                            <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ms-1"></i>
                        @endif
                    </th>
                    <th style="cursor:pointer;" wire:click="sortBy('last_name')">
                        Name
                        @if($sortField === 'last_name' || $sortField === 'name')
                            <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ms-1"></i>
                        @endif
                    </th>
                    <th style="cursor:pointer;" wire:click="sortBy('email')">
                        Email
                        @if($sortField === 'email')
                            <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ms-1"></i>
                        @endif
                    </th>
                    @if(!$this->routeRoleFilter)
                        <th style="cursor:pointer;" wire:click="sortBy('role')">
                            Role
                            @if($sortField === 'role')
                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ms-1"></i>
                            @endif
                        </th>
                    @endif
                    <th style="cursor:pointer;" wire:click="sortBy('department')">
                        Department
                        @if($sortField === 'department')
                            <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ms-1"></i>
                        @endif
                    </th>
                    <th style="cursor:pointer;" wire:click="sortBy('section')">
                        Section
                        @if($sortField === 'section')
                            <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ms-1"></i>
                        @endif
                    </th>
                    <th style="cursor:pointer;" wire:click="sortBy('stat')">
                        Status
                        @if($sortField === 'stat')
                            <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ms-1"></i>
                        @endif
                    </th>
                    <th>Pending</th>
                    @if(isset($isInstructorViewingStudents) && $isInstructorViewingStudents)
                        <th>Avg Grade</th>
                    @endif
                    <th style="cursor:pointer;" wire:click="sortBy('created_at')">
                        Joined
                        @if($sortField === 'created_at')
                            <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ms-1"></i>
                        @endif
                    </th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    <tr>
                        @if($canDelete)
                            <td>
                                <input type="checkbox" wire:model.live="selectedUsers" value="{{ $user->id }}" class="form-check-input">
                            </td>
                        @endif
                        <td>{{ $user->id }}</td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <img src="{{ $user->profile_image_url }}" alt="" class="rounded-circle" width="32" height="32">
                                <div>
                                    <div class="fw-medium">{{ $user->full_name }}</div>
                                    @if($user->student_id)
                                        <small class="text-muted">{{ $user->student_id }}</small>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>{{ $user->email }}</td>
                        @if(!$this->routeRoleFilter)
                            <td>
                                <span class="badge bg-{{ $user->role === 'admin' ? 'dark' : ($user->role === 'instructor' ? 'primary' : 'info') }}">
                                    {{ ucfirst($user->role) }}
                                </span>
                            </td>
                        @endif
                        <td>{{ $user->department->name ?? '-' }}</td>
                        <td>
                            {{ $user->section ?? '-' }}
                            @if($user->school_year)
                                <br><small class="text-muted">{{ $user->school_year }}</small>
                            @endif
                        </td>
                        <td>
                            @if((int) $user->stat === 1)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-warning text-dark">Pending</span>
                            @endif
                        </td>
                        <td>
                            @php
                                $pc = $pendingCounts[$user->id] ?? ['registrations' => 0, 'enrollments' => 0, 'unread_notifications' => 0];
                                $totalPending = ($pc['registrations'] ?? 0) + ($pc['enrollments'] ?? 0) + ($pc['unread_notifications'] ?? 0);
                                $tooltipParts = [];
                                if ($pc['registrations'] > 0) $tooltipParts[] = $pc['registrations'] . ' pending registration(s)';
                                if ($pc['enrollments'] > 0) $tooltipParts[] = $pc['enrollments'] . ' pending enrollment(s)';
                                if ($pc['unread_notifications'] > 0) $tooltipParts[] = $pc['unread_notifications'] . ' unread notification(s)';
                            @endphp
                            @if($totalPending > 0)
                                <span class="badge bg-warning text-dark"
                                      data-bs-toggle="tooltip"
                                      data-bs-placement="top"
                                      title="{{ implode(', ', $tooltipParts) }}">
                                    <i class="fas fa-exclamation-triangle me-1"></i>{{ $totalPending }}
                                </span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        @if(isset($isInstructorViewingStudents) && $isInstructorViewingStudents)
                            <td>
                                @php
                                    $progress = $studentProgress[$user->id] ?? ['average_grade' => 0, 'courses_count' => 0];
                                    $gradeColor = $progress['average_grade'] >= 90 ? 'success' :
                                                  ($progress['average_grade'] >= 85 ? 'info' :
                                                  ($progress['average_grade'] >= 80 ? 'primary' :
                                                  ($progress['average_grade'] >= 75 ? 'warning' : 'danger')));
                                @endphp
                                @if($progress['courses_count'] > 0)
                                    <span class="badge bg-{{ $gradeColor }}">{{ $progress['average_grade'] }}%</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        @endif
                        <td>
                            <small>{{ $user->created_at->format('M d, Y') }}</small>
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="{{ route('private.users.edit', $user) }}" class="btn btn-outline-primary btn-sm" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @if(isset($isInstructorViewingStudents) && $isInstructorViewingStudents)
                                    <a href="{{ route('grades.show', $user) }}" class="btn btn-outline-info btn-sm" title="View Grades">
                                        <i class="fas fa-chart-bar"></i>
                                    </a>
                                @endif
                                @if((int) $user->stat === 0)
                                    <button wire:click="approveUser({{ $user->id }})" class="btn btn-outline-success btn-sm" title="Approve">
                                        <i class="fas fa-check"></i>
                                    </button>
                                @endif
                                @if($canDelete && $user->id !== auth()->id())
                                    <button wire:click="deleteUser({{ $user->id }})" wire:confirm="Delete {{ $user->full_name }}?" class="btn btn-outline-danger btn-sm" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="100%" class="text-center text-muted py-4">
                            <i class="fas fa-users fa-2x mb-2 d-block opacity-50"></i>
                            No users found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="d-flex justify-content-between align-items-center mt-3">
        <small class="text-muted">
            Showing {{ $users->firstItem() ?? 0 }}-{{ $users->lastItem() ?? 0 }} of {{ $users->total() }}
        </small>
        {{ $users->links() }}
    </div>
    @endif

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            function initTooltips() {
                document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) {
                    if (!bootstrap.Tooltip.getInstance(el)) {
                        new bootstrap.Tooltip(el);
                    }
                });
            }
            initTooltips();
            Livewire.hook('morph.updated', function () {
                initTooltips();
            });
        });
    </script>
</div>

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
        <h4 class="mb-0">
            Class Management
            @if($sectionFilter)
                <span class="text-muted"> / {{ $sectionFilter }}</span>
            @endif
        </h4>
    </div>

    {{-- No Advisory Section Warning --}}
    @if(isset($noAdvisorySection) && $noAdvisorySection)
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle me-2"></i>
            You are not assigned to any section. Please contact an administrator.
        </div>
        @return
    @endif

    {{-- Search and Actions --}}
    <div class="d-flex flex-wrap gap-2 mb-3">
        <div class="flex-grow-1" style="min-width: 200px;">
            <input type="text" wire:model.live.debounce.300ms="search" class="form-control form-control-sm"
                placeholder="Search student name, ID...">
        </div>
        @if($sectionFilter)
            <button wire:click="openAddStudentModal" class="btn btn-success btn-sm">
                <i class="fas fa-user-plus me-1"></i> Add Student
            </button>
            <button wire:click="clearSection" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i> All Sections
            </button>
        @endif
    </div>

    {{-- Bulk Actions Bar --}}
    @if(count($selectedStudents) > 0 && $sectionFilter)
        <div class="alert alert-info d-flex flex-wrap align-items-center gap-2 mb-3">
            <span class="me-2">
                <strong>{{ count($selectedStudents) }}</strong> student(s) selected
            </span>
            <button wire:click="bulkRemoveFromSection" wire:confirm="Are you sure you want to remove these students from this section?"
                class="btn btn-warning btn-sm">
                <i class="fas fa-user-minus me-1"></i> Remove from Section
            </button>
            @if(!$isInstructor)
                <button wire:click="bulkActivate" class="btn btn-success btn-sm">
                    <i class="fas fa-check me-1"></i> Activate
                </button>
                <button wire:click="bulkDeactivate" wire:confirm="Are you sure you want to deactivate these students?"
                    class="btn btn-secondary btn-sm">
                    <i class="fas fa-ban me-1"></i> Deactivate
                </button>
            @endif
            <button wire:click="$set('selectedStudents', [])" class="btn btn-outline-secondary btn-sm ms-auto">
                Clear Selection
            </button>
        </div>
    @endif

    @if(!$readyToLoad)
    {{-- Skeleton loader --}}
    <div class="p-3">
        <x-skeleton type="table-row" :count="8" />
    </div>
    @else
    {{-- Loading spinner for subsequent loads --}}
    <div wire:loading class="text-center py-2">
        <div class="spinner-border spinner-border-sm text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

    <div wire:loading.class="opacity-50">
        @if($sectionFilter && $students)
            {{-- Section Detail: Student Table --}}
            <div class="mb-3">
                @php
                    $advisers = $advisersBySection->get($sectionFilter, collect());
                @endphp
                @if($advisers->isNotEmpty())
                    <small class="text-muted">
                        <i class="fas fa-chalkboard-teacher me-1"></i>
                        Adviser(s): {{ $advisers->map(fn($a) => $a->full_name)->join(', ') }}
                    </small>
                @endif
            </div>

            <div class="table-responsive">
                <table class="table table-hover table-sm align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 40px;">
                                <input type="checkbox" wire:model.live="selectAll" class="form-check-input">
                            </th>
                            <th style="cursor:pointer;" wire:click="sortBy('student_id')">
                                Student ID
                                @if($sortField === 'student_id')
                                    <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                @endif
                            </th>
                            <th style="cursor:pointer;" wire:click="sortBy('last_name')">
                                Name
                                @if($sortField === 'last_name')
                                    <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                @endif
                            </th>
                            <th style="cursor:pointer;" wire:click="sortBy('email')">
                                Email
                                @if($sortField === 'email')
                                    <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                @endif
                            </th>
                            <th>Department</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($students as $student)
                            <tr>
                                <td>
                                    <input type="checkbox" wire:model.live="selectedStudents" value="{{ $student->id }}" class="form-check-input">
                                </td>
                                <td>{{ $student->student_id ?? '-' }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <img src="{{ $student->profile_image_url }}" alt="" class="rounded-circle" width="28" height="28">
                                        <span class="fw-medium">{{ $student->full_name }}</span>
                                    </div>
                                </td>
                                <td>{{ $student->email }}</td>
                                <td>{{ $student->department?->name ?? '-' }}</td>
                                <td>
                                    @if((int) $student->stat === 1)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-warning text-dark">Pending</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    No students found in this section.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($students->hasPages())
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <small class="text-muted">
                        Showing {{ $students->firstItem() ?? 0 }}-{{ $students->lastItem() ?? 0 }} of {{ $students->total() }}
                    </small>
                    {{ $students->links() }}
                </div>
            @endif
        @else
            {{-- Section Cards --}}
            <div class="row g-3">
                @forelse($allSections as $section)
                    @php
                        $sectionStudents = $studentsBySection->get($section, collect());
                        $advisers = $advisersBySection->get($section, collect());
                    @endphp
                    <div class="col-md-6 col-lg-4">
                        <div class="card h-100" style="cursor: pointer;" wire:click="selectSection('{{ $section }}')">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h5 class="card-title mb-0">{{ $section }}</h5>
                                    <span class="badge bg-primary">{{ $sectionStudents->count() }} students</span>
                                </div>
                                @if($advisers->isNotEmpty())
                                    <small class="text-muted d-block mb-2">
                                        <i class="fas fa-chalkboard-teacher me-1"></i>
                                        {{ $advisers->map(fn($a) => $a->full_name)->join(', ') }}
                                    </small>
                                @else
                                    <small class="text-muted d-block mb-2">
                                        <i class="fas fa-exclamation-circle me-1"></i> No adviser assigned
                                    </small>
                                @endif
                                <div class="d-flex gap-2">
                                    <small class="text-success">
                                        <i class="fas fa-check-circle me-1"></i>
                                        {{ $sectionStudents->where('stat', 1)->count() }} active
                                    </small>
                                    <small class="text-warning">
                                        <i class="fas fa-clock me-1"></i>
                                        {{ $sectionStudents->where('stat', 0)->count() }} pending
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-center text-muted py-5">
                        <i class="fas fa-school fa-3x mb-3 opacity-50"></i>
                        <p>No sections found.</p>
                    </div>
                @endforelse
            </div>
        @endif
    </div>
    @endif

    {{-- Add Student Modal --}}
    @if($showAddStudentModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-user-plus me-2"></i>Add Students to {{ $sectionFilter }}
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeAddStudentModal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <input type="text" wire:model.live.debounce.300ms="addStudentSearch" class="form-control"
                                placeholder="Search unassigned students by name, ID, or email...">
                        </div>

                        @if(count($studentsToAdd) > 0)
                            <div class="alert alert-info py-2 mb-3">
                                <strong>{{ count($studentsToAdd) }}</strong> student(s) selected to add
                            </div>
                        @endif

                        <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                            <table class="table table-hover table-sm">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th style="width: 40px;"></th>
                                        <th>Student ID</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($this->unassignedStudents as $student)
                                        <tr wire:click="toggleStudentToAdd({{ $student->id }})" style="cursor: pointer;"
                                            class="{{ in_array($student->id, $studentsToAdd) ? 'table-primary' : '' }}">
                                            <td>
                                                <input type="checkbox" class="form-check-input"
                                                    {{ in_array($student->id, $studentsToAdd) ? 'checked' : '' }}
                                                    wire:click.stop="toggleStudentToAdd({{ $student->id }})">
                                            </td>
                                            <td>{{ $student->student_id ?? '-' }}</td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <img src="{{ $student->profile_image_url }}" alt="" class="rounded-circle" width="24" height="24">
                                                    <span>{{ $student->full_name }}</span>
                                                </div>
                                            </td>
                                            <td>{{ $student->email }}</td>
                                            <td>
                                                @if((int) $student->stat === 1)
                                                    <span class="badge bg-success">Active</span>
                                                @else
                                                    <span class="badge bg-warning text-dark">Pending</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-4">
                                                @if($addStudentSearch)
                                                    No unassigned students found matching "{{ $addStudentSearch }}".
                                                @else
                                                    No unassigned students available. All students are already in a section.
                                                @endif
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeAddStudentModal">Cancel</button>
                        <button type="button" class="btn btn-success" wire:click="addSelectedStudentsToSection"
                            {{ count($studentsToAdd) === 0 ? 'disabled' : '' }}>
                            <i class="fas fa-plus me-1"></i>Add {{ count($studentsToAdd) }} Student(s)
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

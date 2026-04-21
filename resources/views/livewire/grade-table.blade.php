<div wire:init="loadData">
    {{-- Flash Messages --}}
    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <h4 class="mb-0">Grades Overview</h4>
        @if(isset($viewer) && $viewer->role !== \App\Constants\Roles::STUDENT)
            <a href="{{ route('grades.export', ['section' => $sectionFilter]) }}" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-download me-1"></i> Export CSV
            </a>
        @endif
    </div>

    {{-- Search & Filters --}}
    <div class="d-flex flex-wrap gap-2 mb-3">
        <div class="flex-grow-1" style="min-width: 200px;">
            <input type="text" wire:model.live.debounce.300ms="search" class="form-control form-control-sm"
                placeholder="Search name, student ID, email...">
        </div>
        <select wire:model.live="sectionFilter" class="form-select form-select-sm" style="max-width: 180px;">
            <option value="">All Sections</option>
            @foreach($sections as $section)
                <option value="{{ $section }}">{{ $section }}</option>
            @endforeach
        </select>
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
                    <th>Section</th>
                    <th class="text-center">Self-Check Avg</th>
                    <th class="text-center">Homework Avg</th>
                    <th class="text-center">Overall</th>
                    <th class="text-center">Grade</th>
                    <th class="text-center">Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($students as $student)
                    @php $gs = $student->grade_summary; @endphp
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
                        <td>{{ $student->section ?? '-' }}</td>
                        <td class="text-center">{{ $gs['self_check_average'] }}%</td>
                        <td class="text-center">{{ $gs['homework_average'] }}%</td>
                        <td class="text-center">
                            <span class="fw-bold {{ $gs['overall_average'] >= 75 ? 'text-success' : 'text-danger' }}">
                                {{ $gs['overall_average'] }}%
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-{{ $gs['is_competent'] ? 'success' : 'danger' }}">
                                {{ $gs['grade_code'] }}
                            </span>
                        </td>
                        <td class="text-center">
                            @if($gs['is_competent'])
                                <span class="badge bg-success">Competent</span>
                            @else
                                <span class="badge bg-warning text-dark">Not Yet Competent</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('grades.show', $student) }}" class="btn btn-outline-primary btn-sm" title="View Details">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center text-muted py-4">
                            <i class="fas fa-graduation-cap fa-2x mb-2 d-block opacity-50"></i>
                            No students found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="d-flex justify-content-between align-items-center mt-3">
        <small class="text-muted">
            Showing {{ $students->firstItem() ?? 0 }}-{{ $students->lastItem() ?? 0 }} of {{ $students->total() }}
        </small>
        {{ $students->links() }}
    </div>
    @endif
</div>

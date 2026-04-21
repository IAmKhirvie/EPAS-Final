@extends('layouts.app')

@section('title', "Section {$section}")

@section('content')
    <div class="content-area">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4>{{ isset($isInstructor) && $isInstructor ? 'My Class' : 'Section' }}: {{ $section }}</h4>
                <x-breadcrumb :items="[
                    ['label' => 'Class Management', 'url' => route('class-management.index')],
                    ['label' => $section],
                ]" />
            </div>
            <div>
                <span class="badge bg-primary">Total Students: {{ $students->total() }}</span>
                @if(!isset($isInstructor) || !$isInstructor)
                <button type="button" class="btn btn-outline-primary ms-2" data-bs-toggle="modal" data-bs-target="#assignAdviserModal">
                    <i class="fas fa-user-plus me-1"></i> Assign Adviser
                </button>
                <a href="{{ route('class-management.index') }}" class="btn btn-outline-secondary ms-2">
                    <i class="fas fa-arrow-left me-1"></i> Back to Sections
                </a>
                @endif
            </div>
        </div>

        <!-- Current Adviser Info -->
        @if($currentAdviser)
        <div class="card mb-4">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h6 class="card-title">Section Adviser</h6>
                        <div class="d-flex align-items-center">
                            <img src="{{ $currentAdviser->profile_image_url }}" alt="Adviser Avatar" class="rounded-circle me-3" width="48" height="48" onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($currentAdviser->initials) }}&background=28a745&color=fff&size=48'">
                            <div>
                                <h6 class="mb-1">{{ $currentAdviser->full_name }}</h6>
                                <p class="text-muted mb-1">
                                    {{ $currentAdviser->email }}
                                    @if($currentAdviser->department)
                                        • {{ $currentAdviser->department->name }}
                                    @endif
                                </p>
                                <span class="badge bg-success">Assigned Adviser</span>
                            </div>
                        </div>
                    </div>
                    @if(!isset($isInstructor) || !$isInstructor)
                    <div class="col-md-4 text-end">
                        <form method="POST" action="{{ route('class-management.remove-adviser', $section) }}" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger btn-sm"
                                    onclick="return confirm('Are you sure you want to remove this adviser?')">
                                <i class="fas fa-user-minus me-1"></i> Remove Adviser
                            </button>
                        </form>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @else
        <div class="alert alert-warning mb-4">
            <i class="fas fa-exclamation-triangle me-2"></i>
            No adviser assigned to this section.
            @if(!isset($isInstructor) || !$isInstructor)
            <a href="#" class="alert-link" data-bs-toggle="modal" data-bs-target="#assignAdviserModal">
                Assign an adviser now.
            </a>
            @endif
        </div>
        @endif

        <!-- Section Navigation (Admin Only) -->
        @if((!isset($isInstructor) || !$isInstructor) && $allSections->count() > 1)
            <div class="card mb-4">
                <div class="card-body">
                    <h6 class="card-title">Quick Section Navigation</h6>
                    <div class="section-navigation">
                        @foreach($allSections as $sec)
                            <a href="{{ route('class-management.show', $sec) }}"
                               class="btn btn-sm {{ $sec == $section ? 'btn-primary' : 'btn-outline-primary' }} me-2 mb-2">
                                {{ $sec }}
                                @if($advisersBySection[$sec] ?? false)
                                    <span class="badge bg-success ms-1">✓</span>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        <!-- Students Table -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Students in {{ $section }}</h5>
            </div>
            <div class="card-body">
                <div class="table-container">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Profile</th>
                                <th>Name</th>
                                <th>Student ID</th>
                                <th>Email</th>
                                <th>Department</th>
                                <th>Room</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($students as $index => $student)
                                <tr>
                                    <td>{{ ($students->currentPage() - 1) * $students->perPage() + $index + 1 }}</td>
                                    <td>
                                        <img src="{{ $student->profile_image_url }}" alt="User Avatar" class="rounded-circle" width="32" height="32" onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($student->initials) }}&background=6d9773&color=fff&size=32'">
                                    </td>
                                    <td>{{ $student->full_name }}</td>
                                    <td>{{ $student->student_id ?? 'N/A' }}</td>
                                    <td>{{ $student->email }}</td>
                                    <td>{{ $student->department->name ?? 'N/A' }}</td>
                                    <td>{{ $student->room_number ?? 'N/A' }}</td>
                                    <td>
                                        @if($student->stat)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-warning text-dark">Pending</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('private.users.edit', $student->id)}}" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center">No students found in this section.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($students->hasPages())
                    <div class="pagination-container mt-3">
                        {{ $students->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Assign Adviser Modal (Admin Only) -->
    @if(!isset($isInstructor) || !$isInstructor)
    <div class="modal fade" id="assignAdviserModal" tabindex="-1" aria-labelledby="assignAdviserModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="assignAdviserModalLabel">Assign Adviser to {{ $section }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('class-management.assign-adviser', $section) }}">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="adviser_id" class="form-label">Select Adviser</label>
                            <select class="form-select" id="adviser_id" name="adviser_id" required>
                                <option value="">-- Choose an instructor --</option>
                                @foreach($instructors as $instructor)
                                    <option value="{{ $instructor->id }}" {{ $currentAdviser && $currentAdviser->id == $instructor->id ? 'selected' : '' }}>
                                        {{ $instructor->full_name }}
                                        @if($instructor->email)
                                            ({{ $instructor->email }})
                                        @endif
                                        @if($instructor->department)
                                            - {{ $instructor->department->name }}
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @if($currentAdviser)
                            <div class="alert alert-info">
                                <small>
                                    <strong>Current Adviser:</strong> {{ $currentAdviser->full_name }}
                                    @if($currentAdviser->email)
                                        ({{ $currentAdviser->email }})
                                    @endif
                                </small>
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Assign Adviser</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
@endsection

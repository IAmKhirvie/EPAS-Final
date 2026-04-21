@extends('layouts.app')

@section('title', 'Search Students')

@section('content')
    <div class="content-area">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4>Search Students</h4>
            <a href="{{ route('class-management.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Sections
            </a>
        </div>

        <!-- Search Form -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('class-management.search') }}">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="search" class="form-label">Search Students</label>
                                <input type="text" class="form-control" id="search" name="search" 
                                       value="{{ $search }}" placeholder="Search by name, student, or email...">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="section" class="form-label">Filter by Section</label>
                                <select class="form-select" id="section" name="section">
                                    <option value="">All Sections</option>
                                    @foreach($allSections as $sec)
                                        <option value="{{ $sec }}" {{ $section == $sec ? 'selected' : '' }}>
                                            {{ $sec }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="mb-3">
                                <label class="form-label">&nbsp;</label>
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-search me-1"></i> Search
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Results -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Search Results</h5>
            </div>
            <div class="card-body">
                @if($search || $section)
                    <div class="alert alert-info">
                        Showing results 
                        @if($search) for "<strong>{{ $search }}</strong>"@endif
                        @if($section) in section <strong>{{ $section }}</strong>@endif
                    </div>
                @endif

                <div class="table-container">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Profile</th>
                                <th>Name</th>
                                <th>student</th>
                                <th>Email</th>
                                <th>Section</th>
                                <th>Department</th>
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
                                    <td>
                                        <span class="badge bg-info">{{ $student->section ?? 'N/A' }}</span>
                                    </td>
                                    <td>{{ $student->department->name ?? 'N/A' }}</td>
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
                                        <a href="{{ route('class-management.show', $student->section) }}" class="btn btn-sm btn-outline-info">
                                            <i class="fas fa-users"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center">
                                        @if($search || $section)
                                            No students found matching your search criteria.
                                        @else
                                            No students found. Start by adding some students.
                                        @endif
                                    </td>
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
@endsection
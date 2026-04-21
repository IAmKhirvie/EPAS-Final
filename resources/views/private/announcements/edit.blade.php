@extends('layouts.app')

@section('content')
<div class="container-fluid py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Edit Announcement</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('private.announcements.update', $announcement) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <!-- Title -->
                        <div class="mb-3">
                            <label for="title" class="form-label">Announcement Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror"
                                id="title" name="title" value="{{ old('title', $announcement->title) }}"
                                placeholder="Enter announcement title" required>
                            @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Content -->
                        <div class="mb-3">
                            <label for="content" class="form-label">Announcement Content <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('content') is-invalid @enderror"
                                id="content" name="content" rows="6"
                                placeholder="Enter announcement content" required>{{ old('content', $announcement->content) }}</textarea>
                            @error('content')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <!-- Publish Date & Time -->
                            <div class="col-md-6 mb-3">
                                <label for="publish_at" class="form-label">Publish Date & Time</label>
                                <input type="datetime-local" class="form-control @error('publish_at') is-invalid @enderror"
                                    id="publish_at" name="publish_at"
                                    value="{{ old('publish_at', $announcement->publish_at?->format('Y-m-d\TH:i')) }}">
                                <div class="form-text">
                                    Schedule when to publish this announcement. Leave empty to publish immediately.
                                </div>
                                @error('publish_at')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Deadline -->
                            <div class="col-md-6 mb-3">
                                <label for="deadline" class="form-label">Deadline (Optional)</label>
                                <input type="datetime-local" class="form-control @error('deadline') is-invalid @enderror"
                                    id="deadline" name="deadline"
                                    value="{{ old('deadline', $announcement->deadline?->format('Y-m-d\TH:i')) }}">
                                <div class="form-text">
                                    Set a deadline for this announcement (e.g., for assignments, events).
                                </div>
                                @error('deadline')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <!-- Urgent Checkbox -->
                            <div class="col-md-6 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_urgent" name="is_urgent" value="1"
                                        {{ old('is_urgent', $announcement->is_urgent) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_urgent">
                                        <strong>Mark as Urgent</strong>
                                    </label>
                                </div>
                                <div class="form-text">Urgent announcements will be highlighted in red.</div>
                            </div>

                            <!-- Pin Checkbox -->
                            <div class="col-md-6 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_pinned" name="is_pinned" value="1"
                                        {{ old('is_pinned', $announcement->is_pinned) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_pinned">
                                        <strong>Pin to Top</strong>
                                    </label>
                                </div>
                                <div class="form-text">Pinned announcements will appear at the top of the list.</div>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Target Roles -->
                            <div class="col-md-6 mb-3">
                                <label for="target_roles" class="form-label">Target Roles</label>
                                <select class="form-select @error('target_roles') is-invalid @enderror"
                                    id="target_roles" name="target_roles">
                                    @php $tr = old('target_roles', $announcement->target_roles); @endphp
                                    <option value="" {{ !$tr ? 'selected' : '' }}>All Roles</option>
                                    <option value="student" {{ $tr === 'student' ? 'selected' : '' }}>Students Only</option>
                                    <option value="instructor" {{ $tr === 'instructor' ? 'selected' : '' }}>Instructors Only</option>
                                    <option value="admin" {{ $tr === 'admin' ? 'selected' : '' }}>Admins Only</option>
                                    <option value="student,instructor" {{ $tr === 'student,instructor' ? 'selected' : '' }}>Students & Instructors</option>
                                    <option value="admin,instructor" {{ $tr === 'admin,instructor' ? 'selected' : '' }}>Admins & Instructors</option>
                                </select>
                                <div class="form-text">Leave as "All Roles" to reach everyone.</div>
                                @error('target_roles')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Target Sections -->
                            <div class="col-md-6 mb-3">
                                <label for="target_sections" class="form-label">Target Sections</label>
                                <input type="text" class="form-control @error('target_sections') is-invalid @enderror"
                                    id="target_sections" name="target_sections"
                                    value="{{ old('target_sections', $announcement->target_sections) }}"
                                    placeholder="e.g., A1,B1,C1">
                                <div class="form-text">
                                    Comma-separated list of sections. Leave empty to reach all sections.
                                </div>
                                @error('target_sections')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="d-flex justify-content-between align-items-center">
                            <a href="{{ route('private.announcements.show', $announcement) }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Back
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Update Announcement
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .form-text {
        font-size: 0.875rem;
        color: #6c757d;
    }
</style>
@endsection
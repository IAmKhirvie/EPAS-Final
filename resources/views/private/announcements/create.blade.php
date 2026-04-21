@extends('layouts.app')

@section('content')
<div class="container-fluid py-3">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Create New Announcement</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('private.announcements.store') }}" method="POST">
                        @csrf

                        <!-- Title -->
                        <div class="mb-3">
                            <label for="title" class="form-label">Announcement Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror"
                                id="title" name="title" value="{{ old('title') }}"
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
                                placeholder="Enter announcement content" required>{{ old('content') }}</textarea>
                            @error('content')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <!-- Publish Date & Time -->
                            <div class="col-md-6 mb-3">
                                <label for="publish_at" class="form-label">Publish Date & Time</label>
                                <input type="datetime-local" class="form-control @error('publish_at') is-invalid @enderror"
                                    id="publish_at" name="publish_at" value="{{ old('publish_at') }}"
                                    placeholder="Leave empty to publish immediately">
                                <div class="form-text">
                                    Schedule when to publish this announcement. Leave empty to publish immediately.
                                </div>
                                @error('publish_at')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Deadline (Optional) -->
                            <div class="col-md-6 mb-3">
                                <label for="deadline" class="form-label">Deadline (Optional)</label>
                                <input type="datetime-local" class="form-control @error('deadline') is-invalid @enderror"
                                    id="deadline" name="deadline" value="{{ old('deadline') }}"
                                    placeholder="Set a deadline if applicable">
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
                                        {{ old('is_urgent') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_urgent">
                                        <strong>Mark as Urgent</strong>
                                    </label>
                                </div>
                                <div class="form-text">
                                    Urgent announcements will be highlighted in red.
                                </div>
                            </div>

                            <!-- Pin Checkbox -->
                            <div class="col-md-6 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_pinned" name="is_pinned" value="1"
                                        {{ old('is_pinned') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_pinned">
                                        <strong>Pin to Top</strong>
                                    </label>
                                </div>
                                <div class="form-text">
                                    Pinned announcements will appear at the top of the list.
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Target Roles -->
                            <div class="col-md-6 mb-3">
                                <label for="target_roles" class="form-label">Target Roles</label>
                                <select class="form-select @error('target_roles') is-invalid @enderror"
                                    id="target_roles" name="target_roles">
                                    <option value="" {{ old('target_roles') === null ? 'selected' : '' }}>All Roles</option>
                                    <option value="student" {{ old('target_roles') === 'student' ? 'selected' : '' }}>Students Only</option>
                                    <option value="instructor" {{ old('target_roles') === 'instructor' ? 'selected' : '' }}>Instructors Only</option>
                                    <option value="admin" {{ old('target_roles') === 'admin' ? 'selected' : '' }}>Admins Only</option>
                                    <option value="student,instructor" {{ old('target_roles') === 'student,instructor' ? 'selected' : '' }}>Students & Instructors</option>
                                    <option value="admin,instructor" {{ old('target_roles') === 'admin,instructor' ? 'selected' : '' }}>Admins & Instructors</option>
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
                                    id="target_sections" name="target_sections" value="{{ old('target_sections') }}"
                                    placeholder="e.g., A1,B1,C1">
                                <div class="form-text">
                                    Comma-separated list of sections. Leave empty to reach all sections.
                                </div>
                                @error('target_sections')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Current Time Display -->
                        <div class="alert alert-info">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-info-circle me-2"></i>
                                <div>
                                    <strong>Current Time:</strong>
                                    <span id="current-time">{{ now()->format('F j, Y g:i A') }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="d-flex justify-content-between align-items-center">
                            <a href="{{ route('private.announcements.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Back to Announcements
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane me-1"></i> Create Announcement
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Update current time every minute
    function updateCurrentTime() {
        const now = new Date();
        const options = {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: 'numeric',
            minute: 'numeric',
            hour12: true
        };
        document.getElementById('current-time').textContent = now.toLocaleDateString('en-US', options);
    }

    // Update time immediately and then every minute
    updateCurrentTime();
    setInterval(updateCurrentTime, 60000);

    // Set minimum datetime for publish_at and deadline to current time
    const now = new Date();
    const nowFormatted = now.toISOString().slice(0, 16);

    document.getElementById('publish_at').min = nowFormatted;
    document.getElementById('deadline').min = nowFormatted;
</script>

<style>
    .form-text {
        font-size: 0.875rem;
        color: #6c757d;
    }

    .alert-info {
        background-color: #d1ecf1;
        border-color: #bee5eb;
        color: #0c5460;
    }
</style>
@endsection
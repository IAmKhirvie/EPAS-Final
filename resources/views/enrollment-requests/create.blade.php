@extends('layouts.app')

@section('title', 'New Enrollment Request')

@section('content')
<div class="content-area">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4>New Enrollment Request</h4>
                    <p class="text-muted mb-0">Request a student to be enrolled in your class: <strong>{{ $user->advisory_section }}</strong></p>
                </div>
                <a href="{{ route('enrollment-requests.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back
                </a>
            </div>

            <div class="card">
                <div class="card-body">
                    <form action="{{ route('enrollment-requests.store') }}" method="POST">
                        @csrf

                        <div class="mb-4">
                            <label for="student_id" class="form-label">Select Student <span class="text-danger">*</span></label>
                            <select name="student_id" id="student_id" class="form-select @error('student_id') is-invalid @enderror" required>
                                <option value="">-- Select a student --</option>
                                @if($unassignedStudents->where('section', null)->count() > 0)
                                    <optgroup label="Unassigned Students (No Section)">
                                        @foreach($unassignedStudents->where('section', null) as $student)
                                            <option value="{{ $student->id }}" {{ old('student_id') == $student->id ? 'selected' : '' }}>
                                                {{ $student->full_name }}
                                                @if($student->student_id)
                                                    ({{ $student->student_id }})
                                                @endif
                                                - {{ $student->email }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endif
                                @if($unassignedStudents->whereNotNull('section')->count() > 0)
                                    <optgroup label="Students in Other Sections">
                                        @foreach($unassignedStudents->whereNotNull('section') as $student)
                                            <option value="{{ $student->id }}" {{ old('student_id') == $student->id ? 'selected' : '' }}>
                                                {{ $student->full_name }}
                                                @if($student->student_id)
                                                    ({{ $student->student_id }})
                                                @endif
                                                - Currently in: {{ $student->section }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endif
                            </select>
                            @error('student_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">
                                Select a student to request enrollment into your section.
                                @if($unassignedStudents->whereNotNull('section')->count() > 0)
                                    Students in other sections will need admin approval to transfer.
                                @endif
                            </small>
                        </div>

                        <div class="mb-4">
                            <label for="notes" class="form-label">Notes (Optional)</label>
                            <textarea name="notes" id="notes" class="form-control @error('notes') is-invalid @enderror"
                                      rows="3" placeholder="Add any notes or reason for this request..."
                                      maxlength="500">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Maximum 500 characters</small>
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Note:</strong> This request will be sent to administrators for approval.
                            You will be notified once the request is processed.
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('enrollment-requests.index') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane me-1"></i> Submit Request
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            @if($unassignedStudents->count() === 0)
            <div class="alert alert-warning mt-4">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>No Available Students</strong>
                <p class="mb-0 mt-2">All active students are already enrolled in your section or there are no unassigned students available.</p>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Add native search functionality to the student select
document.addEventListener('DOMContentLoaded', function() {
    const select = document.getElementById('student_id');
    if (!select) return;

    // Create a search input for the dropdown
    const searchContainer = document.createElement('div');
    searchContainer.className = 'mb-3';
    searchContainer.innerHTML = '<input type="text" id="studentSearch" class="form-control" placeholder="Search for a student...">';

    select.parentNode.insertBefore(searchContainer, select);

    const searchInput = document.getElementById('studentSearch');
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const options = select.querySelectorAll('option');

        options.forEach(function(option) {
            const text = option.textContent.toLowerCase();
            if (text.indexOf(searchTerm) > -1) {
                option.style.display = '';
            } else {
                option.style.display = 'none';
            }
        });
    });
});
</script>
@endpush

@extends('layouts.app')

@section('title', 'Create User')

@section('content')
    <div class="content-area">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4>Create New User</h4>
            <a href="{{ route('private.users.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Back to Users
            </a>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>Please fix the following errors:</strong>
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="card">
            <div class="card-body">
                <form action="{{ route('private.users.store') }}" method="POST">
                    @csrf

                    <div class="row g-3">
                        <!-- Personal Information -->
                        <div class="col-md-6">
                            <h6 class="border-bottom pb-2 mb-3 text-primary">
                                <i class="fas fa-user me-2"></i>Personal Information
                            </h6>

                            <div class="row g-2">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">First Name *</label>
                                        <input type="text" name="first_name" class="form-control @error('first_name') is-invalid @enderror"
                                               value="{{ old('first_name') }}" required placeholder="Enter first name">
                                        @error('first_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Last Name *</label>
                                        <input type="text" name="last_name" class="form-control @error('last_name') is-invalid @enderror"
                                               value="{{ old('last_name') }}" required placeholder="Enter last name">
                                        @error('last_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row g-2">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Middle Name</label>
                                        <input type="text" name="middle_name" class="form-control @error('middle_name') is-invalid @enderror"
                                               value="{{ old('middle_name') }}" placeholder="Enter middle name">
                                        @error('middle_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Extension Name</label>
                                        <input type="text" name="ext_name" class="form-control @error('ext_name') is-invalid @enderror"
                                               value="{{ old('ext_name') }}" placeholder="Jr., Sr., III">
                                        @error('ext_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Account Information -->
                        <div class="col-md-6">
                            <h6 class="border-bottom pb-2 mb-3 text-primary">
                                <i class="fas fa-id-card me-2"></i>Account Information
                            </h6>

                            <div class="mb-3">
                                <label class="form-label">Email *</label>
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                       value="{{ old('email') }}" required placeholder="Enter email address">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row g-2">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Role *</label>
                                        <select name="role" class="form-select @error('role') is-invalid @enderror" required id="roleSelect">
                                            <option value="">-- Select Role --</option>
                                            <option value="student" {{ old('role') === 'student' ? 'selected' : '' }}>Student</option>
                                            <option value="instructor" {{ old('role') === 'instructor' ? 'selected' : '' }}>Instructor</option>
                                            <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                                        </select>
                                        @error('role')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Department *</label>
                                        <select name="department_id" class="form-select @error('department_id') is-invalid @enderror" required>
                                            <option value="">-- Select Department --</option>
                                            @foreach($departments as $dept)
                                                <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>
                                                    {{ $dept->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('department_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Dynamic Fields based on Role -->
                            <div id="studentFields" class="role-dependent" style="display: none;">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">School Year</label>
                                        <input type="text" name="school_year" class="form-control @error('school_year') is-invalid @enderror"
                                               value="{{ old('school_year') }}" placeholder="e.g., 2025-2026">
                                        @error('school_year')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Section / Batch</label>
                                        <select name="section" class="form-select @error('section') is-invalid @enderror" id="sectionSelect">
                                            <option value="">-- Select Section --</option>
                                            <option value="A1" {{ old('section') == 'A1' ? 'selected' : '' }}>A1</option>
                                            <option value="B1" {{ old('section') == 'B1' ? 'selected' : '' }}>B1</option>
                                            <option value="C1" {{ old('section') == 'C1' ? 'selected' : '' }}>C1</option>
                                            <option value="D1" {{ old('section') == 'D1' ? 'selected' : '' }}>D1</option>
                                            <option value="custom">-- Custom Section --</option>
                                        </select>
                                        <input type="text" name="custom_section" class="form-control mt-2 d-none"
                                            id="customSectionInput" placeholder="e.g., Batch 1, EPAS-B1">
                                        @error('section')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div id="instructorFields" class="role-dependent" style="display: none;">
                                <div class="mb-3">
                                    <label class="form-label">Room Number</label>
                                    <input type="text" name="room_number" class="form-control @error('room_number') is-invalid @enderror"
                                           value="{{ old('room_number') }}" placeholder="e.g., Room 101, Lab 2">
                                    @error('room_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Password Section -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card border-primary">
                                <div class="card-header bg-primary bg-opacity-10 border-primary">
                                    <h6 class="mb-0">
                                        <i class="fas fa-key me-2"></i>Password
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Password *</label>
                                                <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                                                       required placeholder="Min 8 chars, uppercase, lowercase, number, special char">
                                                @error('password')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                                <small class="text-muted">Must contain: uppercase, lowercase, number, and special character</small>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Confirm Password *</label>
                                                <input type="password" name="password_confirmation" class="form-control"
                                                       required placeholder="Confirm password">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Approval Section -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card border-success">
                                <div class="card-header bg-success bg-opacity-10 border-success">
                                    <h6 class="mb-0">
                                        <i class="fas fa-user-check me-2"></i>Account Activation
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" name="stat" value="1"
                                               id="autoApproveSwitch" {{ old('stat', '1') == '1' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="autoApproveSwitch">
                                            <strong>Auto-approve this user</strong>
                                        </label>
                                    </div>
                                    <small class="text-muted d-block mt-2">
                                        <i class="fas fa-info-circle me-1"></i>
                                        When enabled, the user will be immediately active and can log in.
                                        When disabled, the user will need manual approval before they can access the system.
                                    </small>

                                    <div class="mt-3 p-3 rounded" id="approvalStatus" style="background: rgba(25, 135, 84, 0.1);">
                                        <i class="fas fa-check-circle text-success me-2"></i>
                                        <span id="approvalStatusText">User will be <strong>immediately active</strong> after creation.</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="d-flex gap-2 justify-content-end">
                                <a href="{{ route('private.users.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times me-2"></i>Cancel
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-user-plus me-2"></i>Create User
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Role-based field visibility
        const roleSelect = document.getElementById('roleSelect');
        const studentFields = document.getElementById('studentFields');
        const instructorFields = document.getElementById('instructorFields');

        function toggleRoleFields() {
            const role = roleSelect.value;

            // Hide all role-dependent fields first
            document.querySelectorAll('.role-dependent').forEach(field => {
                field.style.display = 'none';
            });

            // Show relevant fields based on role
            if (role === 'student') {
                studentFields.style.display = 'block';
            } else if (role === 'instructor') {
                instructorFields.style.display = 'block';
            }
        }

        // Initial toggle
        toggleRoleFields();

        // Toggle on role change
        roleSelect.addEventListener('change', toggleRoleFields);

        // Custom section handling
        const sectionSelect = document.getElementById('sectionSelect');
        const customSectionInput = document.getElementById('customSectionInput');

        function toggleCustomSection() {
            if (sectionSelect.value === 'custom') {
                customSectionInput.classList.remove('d-none');
                customSectionInput.required = true;
                customSectionInput.focus();
            } else {
                customSectionInput.classList.add('d-none');
                customSectionInput.required = false;
                customSectionInput.value = '';
            }
        }

        // Initial toggle
        toggleCustomSection();

        // Toggle on section change
        sectionSelect.addEventListener('change', toggleCustomSection);

        // Auto-approve toggle visual feedback
        const autoApproveSwitch = document.getElementById('autoApproveSwitch');
        const approvalStatus = document.getElementById('approvalStatus');
        const approvalStatusText = document.getElementById('approvalStatusText');

        function updateApprovalStatus() {
            if (autoApproveSwitch.checked) {
                approvalStatus.style.background = 'rgba(25, 135, 84, 0.1)';
                approvalStatusText.innerHTML = '<i class="fas fa-check-circle text-success me-2"></i>User will be <strong>immediately active</strong> after creation.';
            } else {
                approvalStatus.style.background = 'rgba(255, 193, 7, 0.1)';
                approvalStatusText.innerHTML = '<i class="fas fa-clock text-warning me-2"></i>User will be <strong>pending approval</strong> and cannot log in until approved.';
            }
        }

        // Initial status
        updateApprovalStatus();

        // Update on toggle
        autoApproveSwitch.addEventListener('change', updateApprovalStatus);
    });
</script>

<style>
.content-area {
    max-height: calc(100vh - 0px);
    overflow-y: auto;
}

.card {
    border: none;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
}

.card-header {
    background: rgba(0,0,0,0.02);
    border-bottom: 1px solid rgba(0,0,0,0.1);
}

.form-label {
    font-weight: 500;
    font-size: 0.875rem;
    margin-bottom: 0.5rem;
    color: #495057;
}

.form-control, .form-select {
    border-radius: 8px;
    border: 1px solid #dee2e6;
    padding: 0.5rem 0.75rem;
    font-size: 0.875rem;
}

.form-control:focus, .form-select:focus {
    border-color: #ffb902;
    box-shadow: 0 0 0 0.2rem rgba(255, 185, 2, 0.25);
}

.form-check-input:checked {
    background-color: #198754;
    border-color: #198754;
}

.form-switch .form-check-input {
    width: 3em;
    height: 1.5em;
}

.btn {
    border-radius: 8px;
    padding: 0.5rem 1rem;
    font-weight: 500;
}

.alert {
    border-radius: 8px;
    border: none;
}

/* Mobile responsiveness */
@media (max-width: 1032px) {
    .content-area {
        max-height: none;
        overflow-y: visible;
    }

    .row.g-3 {
        margin-bottom: -0.75rem;
    }

    .row.g-3 > [class*="col-"] {
        margin-bottom: 0.75rem;
    }
}
</style>
@endsection

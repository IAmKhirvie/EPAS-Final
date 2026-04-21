@extends('layouts.app')

@section('title', 'Edit User')

@section('content')
    <div class="content-area">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4>Edit User</h4>
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

        {{-- Pending Items Panel --}}
        @if(isset($pendingItems))
            @php
                $hasPending = ($pendingItems['pending_approval'] ?? false)
                    || $pendingItems['pending_registrations']->count() > 0
                    || $pendingItems['pending_enrollments']->count() > 0
                    || $pendingItems['unread_notifications']->count() > 0;
            @endphp
            @if($hasPending)
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <h6 class="alert-heading mb-2">
                        <i class="fas fa-exclamation-triangle me-2"></i>Pending Items for {{ $user->full_name }}
                    </h6>
                    <ul class="mb-0">
                        @if($pendingItems['pending_approval'])
                            <li><strong>Account pending approval</strong> — this user's account is inactive (stat=0)</li>
                        @endif
                        @if($pendingItems['pending_registrations']->count() > 0)
                            <li>
                                <a href="{{ route('private.users.index', ['status' => 'pending']) }}">
                                    {{ $pendingItems['pending_registrations']->count() }} pending registration(s)
                                </a>
                            </li>
                        @endif
                        @if($pendingItems['pending_enrollments']->count() > 0)
                            <li>
                                <a href="{{ route('enrollment-requests.index') }}">
                                    {{ $pendingItems['pending_enrollments']->count() }} pending enrollment request(s)
                                </a>
                            </li>
                        @endif
                        @if($pendingItems['unread_notifications']->count() > 0)
                            <li>{{ $pendingItems['unread_notifications']->count() }} unread notification(s)</li>
                        @endif
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
        @endif

        <div class="card">
            <div class="card-body">
                <form action="{{ route('private.users.update', $user->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row g-3">
                        <!-- Personal Information -->
                        <div class="col-md-6">
                            <h6 class="border-bottom pb-2 mb-3 text-primary">
                                <i class="fas fa-user me-2"></i>Personal Information
                            </h6>
                            
                            <div class="mb-3">
                                <label class="form-label">student ID</label>
                                <input type="text" name="student_id" class="form-control @error('student_id') is-invalid @enderror" 
                                       value="{{ old('student_id', $user->student_id) }}" placeholder="Enter student ID">
                                @error('student_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row g-2">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">First Name *</label>
                                        <input type="text" name="first_name" class="form-control @error('first_name') is-invalid @enderror" 
                                               value="{{ old('first_name', $user->first_name) }}" required>
                                        @error('first_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Last Name *</label>
                                        <input type="text" name="last_name" class="form-control @error('last_name') is-invalid @enderror" 
                                               value="{{ old('last_name', $user->last_name) }}" required>
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
                                               value="{{ old('middle_name', $user->middle_name) }}">
                                        @error('middle_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Extension Name</label>
                                        <input type="text" name="ext_name" class="form-control @error('ext_name') is-invalid @enderror" 
                                               value="{{ old('ext_name', $user->ext_name) }}" placeholder="Jr., Sr., III">
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
                                       value="{{ old('email', $user->email) }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row g-2">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Role *</label>
                                        <select name="role" class="form-select @error('role') is-invalid @enderror" required id="roleSelect">
                                            <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Admin</option>
                                            <option value="student" {{ old('role', $user->role) === 'student' ? 'selected' : '' }}>Student</option>
                                            <option value="instructor" {{ old('role', $user->role) === 'instructor' ? 'selected' : '' }}>Instructor</option>
                                        </select>
                                        @error('role')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Status *</label>
                                        <select name="stat" class="form-select @error('stat') is-invalid @enderror" required>
                                            <option value="1" {{ old('stat', $user->stat) ? 'selected' : '' }}>Active</option>
                                            <option value="0" {{ !old('stat', $user->stat) ? 'selected' : '' }}>Inactive</option>
                                        </select>
                                        @error('stat')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Department</label>
                                <select name="department_id" class="form-select @error('department_id') is-invalid @enderror" id="departmentSelect">
                                    <option value="">-- Select Department --</option>
                                    @foreach($departments as $dept)
                                        <option value="{{ $dept->id }}" {{ old('department_id', $user->department_id) == $dept->id ? 'selected' : '' }}>
                                            {{ $dept->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('department_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Dynamic Fields based on Role -->
                            <div id="studentFields" class="role-dependent" style="display: none;">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">School Year</label>
                                        <input type="text" name="school_year" class="form-control @error('school_year') is-invalid @enderror"
                                               value="{{ old('school_year', $user->school_year) }}" placeholder="e.g., 2025-2026">
                                        @error('school_year')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Section / Batch</label>
                                        <select name="section" class="form-select @error('section') is-invalid @enderror" id="sectionSelect">
                                            <option value="">-- Select Section --</option>
                                            <option value="A1" {{ old('section', $user->section) == 'A1' ? 'selected' : '' }}>A1</option>
                                            <option value="B1" {{ old('section', $user->section) == 'B1' ? 'selected' : '' }}>B1</option>
                                            <option value="C1" {{ old('section', $user->section) == 'C1' ? 'selected' : '' }}>C1</option>
                                            <option value="D1" {{ old('section', $user->section) == 'D1' ? 'selected' : '' }}>D1</option>
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
                                           value="{{ old('room_number', $user->room_number) }}" placeholder="e.g., Room 101, Lab 2">
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
                            <div class="card border-warning">
                                <div class="card-header bg-warning bg-opacity-10 border-warning">
                                    <h6 class="mb-0">
                                        <i class="fas fa-key me-2"></i>Change Password (Optional)
                                    </h6>
                                    <small class="text-muted">Leave blank if you don't want to change the password</small>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">New Password</label>
                                                <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" 
                                                       placeholder="Enter new password">
                                                @error('password')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Confirm New Password</label>
                                                <input type="password" name="password_confirmation" class="form-control" 
                                                       placeholder="Confirm new password">
                                            </div>
                                        </div>
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
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-save me-2"></i>Update User
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Credentials Section (for students only) --}}
        @if($user->role === 'student')
        <div class="card mt-4">
            <div class="card-header bg-primary bg-opacity-10 border-primary">
                <h6 class="mb-0">
                    <i class="fas fa-award me-2"></i>Credentials
                    <small class="text-muted ms-2">Achievements & Certificates for {{ $user->full_name }}</small>
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <h6 class="border-bottom pb-2 mb-3 text-primary">
                            <i class="fas fa-certificate me-2"></i>Issue Certificate
                        </h6>
                        @php $modules = \App\Models\Module::with('course')->where('is_active', true)->orderBy('module_title')->get(); @endphp
                        @if($modules->isEmpty())
                            <p class="text-muted small">No active modules available.</p>
                        @else
                            <form action="{{ route('private.users.issue-certificate', $user) }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <select name="module_id" class="form-select" required>
                                        <option value="">-- Select Module --</option>
                                        @foreach($modules as $module)
                                            <option value="{{ $module->id }}">
                                                {{ $module->module_title }} ({{ $module->course->course_name ?? 'N/A' }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-sm btn-primary">
                                    <i class="fas fa-certificate me-1"></i> Issue Certificate
                                </button>
                            </form>
                        @endif
                    </div>
                </div>

                {{-- Certificates --}}
                @php $certificates = \App\Models\Certificate::where('user_id', $user->id)->with('course', 'module')->latest()->get(); @endphp
                @if($certificates->isNotEmpty())
                    <h6 class="border-bottom pb-2 mb-3">
                        <i class="fas fa-certificate text-warning me-1"></i> Certificates ({{ $certificates->count() }})
                    </h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>Certificate</th>
                                    <th>Module / Course</th>
                                    <th>Status</th>
                                    <th>Issued</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($certificates as $cert)
                                    <tr>
                                        <td>
                                            <a href="{{ route('admin.certificates.show', $cert) }}">
                                                {{ $cert->certificate_number }}
                                            </a>
                                        </td>
                                        <td>{{ $cert->module->module_title ?? '' }} / {{ $cert->course->course_name ?? '' }}</td>
                                        <td>
                                            <span class="badge bg-{{ $cert->status === 'issued' ? 'success' : ($cert->status === 'revoked' ? 'danger' : 'warning') }}">
                                                {{ ucfirst(str_replace('_', ' ', $cert->status)) }}
                                            </span>
                                        </td>
                                        <td>{{ $cert->issue_date ? $cert->issue_date->format('M d, Y') : '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
        @endif
    </div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // student ID change confirmation
        const studentInput = document.querySelector('input[name="student_id"]');
        const originalValue = studentInput.value;
        
        studentInput.addEventListener('change', function() {
            if (this.value !== originalValue) {
                if (!confirm('Are you sure you want to change the student ID?')) {
                    this.value = originalValue;
                }
            }
        });

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

        // Check if current section is not in the predefined list (it's custom)
        const currentSection = '{{ old('section', $user->section) }}';
        const predefinedSections = ['', 'A1', 'B1', 'C1', 'D1', 'custom'];
        if (currentSection && !predefinedSections.includes(currentSection)) {
            // It's a custom section - select "custom" and fill the input
            sectionSelect.value = 'custom';
            customSectionInput.value = currentSection;
            customSectionInput.classList.remove('d-none');
        }

        // Initial toggle
        toggleCustomSection();

        // Toggle on section change
        sectionSelect.addEventListener('change', toggleCustomSection);
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
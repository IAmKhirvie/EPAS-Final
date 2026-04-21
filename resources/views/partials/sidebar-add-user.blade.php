        <!-- Add User Sidebar -->
        @php $departments = $departments ?? \App\Models\Department::all(); @endphp
        <div class="slide-sidebar" id="addUserSidebar">
            <div class="slide-sidebar-header">
                <h5>Add New User</h5>
                <button class="close-sidebar" id="closeSidebar">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="slide-sidebar-content">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <strong>Please fix the following errors:</strong>
                        <ul class="mb-0 mt-2 ps-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('private.users.store') }}" id="addUserForm">
                    @csrf
                    <div class="row">
                        <div class="col-12">
                            <div class="mb-3">
                                <label for="first_name" class="form-label required-field">First Name</label>
                                <input type="text" name="first_name" id="first_name" class="form-control" value="{{ old('first_name') }}" required>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="mb-3">
                                <label for="middle_name" class="form-label">Middle Name</label>
                                <input type="text" name="middle_name" id="middle_name" class="form-control" value="{{ old('middle_name') }}">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="mb-3">
                                <label for="last_name" class="form-label required-field">Last Name</label>
                                <input type="text" name="last_name" id="last_name" class="form-control" value="{{ old('last_name') }}" required>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="mb-3">
                                <label for="ext_name" class="form-label">Extension Name</label>
                                <input type="text" name="ext_name" id="ext_name" class="form-control" value="{{ old('ext_name') }}">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="mb-3">
                                <label for="student_id" class="form-label required-field">Student ID</label>
                                <input type="text" name="student_id" id="student_id" class="form-control" value="{{ old('student_id') }}" required>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="mb-3">
                                <label for="email" class="form-label required-field">Email</label>
                                <input type="email" name="email" id="email" class="form-control" value="{{ old('email') }}" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="mb-3">
                                <label for="password" class="form-label required-field">Password</label>
                                <input type="password" name="password" id="password" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="mb-3">
                                <label for="password_confirmation" class="form-label required-field">Confirm Password</label>
                                <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="mb-3">
                                <label for="department_id" class="form-label required-field">Department</label>
                                <select name="department_id" id="department_id" class="form-select" required>
                                    <option value="" disabled {{ old('department_id') ? '' : 'selected' }}>Select Department</option>
                                    @foreach($departments as $department)
                                        <option value="{{ $department->id }}" {{ old('department_id') == $department->id ? 'selected' : '' }}>
                                            {{ $department->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="mb-3">
                                <label for="role" class="form-label required-field">Role</label>
                                <select name="role" id="role" class="form-select" required>
                                    <option value="" disabled {{ old('role') ? '' : 'selected' }}>Select Role</option>
                                    <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                                    <option value="student" {{ old('role') == 'student' ? 'selected' : '' }}>Student</option>
                                    <option value="instructor" {{ old('role') == 'instructor' ? 'selected' : '' }}>Instructor</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row" id="student-fields">
                        <div class="col-6">
                            <div class="mb-3">
                                <label for="school_year" class="form-label">School Year</label>
                                <input type="text" name="school_year" id="school_year" class="form-control" value="{{ old('school_year') }}" placeholder="e.g., 2025-2026">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="mb-3">
                                <label for="section" class="form-label">Section / Batch</label>
                                <input type="text" name="section" id="section" class="form-control" value="{{ old('section') }}" placeholder="e.g., Batch 1, EPAS-B1">
                            </div>
                        </div>
                    </div>
                    <div class="row" id="instructor-fields">
                        <div class="col-12">
                            <div class="mb-3">
                                <label for="room_number" class="form-label">Room Number</label>
                                <input type="text" name="room_number" id="room_number" class="form-control" value="{{ old('room_number') }}" placeholder="e.g., Room 101, Lab 2">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="stat" class="form-label required-field">Status</label>
                        <select name="stat" id="stat" class="form-select" required>
                            <option value="1" {{ old('stat') == '1' ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ old('stat') == '0' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Add User</button>
                    </div>
                </form>
            </div>
        </div>

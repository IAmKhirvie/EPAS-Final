        <!-- Create Course Sidebar -->
        <div class="slide-sidebar" id="createCourseSidebar">
            <div class="slide-sidebar-header">
                <h5>Create New Course</h5>
                <button class="close-sidebar" id="closeCourseSidebar">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="slide-sidebar-content">
                <form method="POST" action="{{ route('courses.store') }}" id="createCourseForm">
                    @csrf

                    <div class="mb-3">
                        <label for="course_name" class="form-label required-field">Course Name</label>
                        <input type="text" name="course_name" id="course_name"
                            class="form-control" value="{{ old('course_name') }}"
                            placeholder="e.g., Electronic Products Assembly and Servicing" required>
                    </div>

                    <div class="mb-3">
                        <label for="course_code" class="form-label required-field">Course Code</label>
                        <input type="text" name="course_code" id="course_code"
                            class="form-control" value="{{ old('course_code') }}"
                            placeholder="e.g., EPAS-NCII" required>
                    </div>

                    <div class="mb-3">
                        <label for="sector" class="form-label">Sector</label>
                        <input type="text" name="sector" id="sector"
                            class="form-control" value="{{ old('sector') }}"
                            placeholder="e.g., Electronics Sector">
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea name="description" id="description"
                                class="form-control" rows="3"
                                placeholder="Enter course description...">{{ old('description') }}</textarea>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Create Course</button>
                    </div>
                </form>
            </div>
        </div>

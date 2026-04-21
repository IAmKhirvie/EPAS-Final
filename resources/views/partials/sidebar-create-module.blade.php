        <!-- Create Module Sidebar -->
        @php $courses = $courses ?? \App\Models\Course::withCount('modules')->orderBy('course_name')->get(); @endphp
        <div class="slide-sidebar" id="createModuleSidebar">
            <div class="slide-sidebar-header">
                <h5>Create New Module</h5>
                <button class="close-sidebar" id="closeModuleSidebar">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="slide-sidebar-content">
                <form method="POST" action="{{ route('modules.store') }}" id="createModuleForm">
                    @csrf

                    <div class="mb-3">
                        <label for="fab_course_id" class="form-label required-field">Course</label>
                        <select name="course_id" id="fab_course_id" class="form-select" required>
                            <option value="" disabled selected>Select a Course</option>
                            @foreach($courses as $course)
                                <option value="{{ $course->id }}" data-module-count="{{ $course->modules_count ?? $course->modules->count() }}">
                                    {{ $course->course_code }} - {{ $course->course_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-6">
                            <div class="mb-3">
                                <label for="fab_module_order" class="form-label required-field">Order</label>
                                <input type="number" name="order" id="fab_module_order"
                                       class="form-control" value="{{ old('order', 1) }}" min="1" required>
                                <small class="text-muted">Position in course</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="mb-3">
                                <label for="module_number" class="form-label required-field">Module Number</label>
                                <input type="text" name="module_number" id="module_number"
                                       class="form-control" value="{{ old('module_number') }}" placeholder="e.g., Module 1" required>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="qualification_title" class="form-label required-field">Qualification Title</label>
                        <input type="text" name="qualification_title" id="qualification_title"
                               class="form-control" value="{{ old('qualification_title', 'Electronic Products Assembly And Servicing NCII') }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="unit_of_competency" class="form-label required-field">Unit of Competency</label>
                        <input type="text" name="unit_of_competency" id="unit_of_competency"
                               class="form-control" value="{{ old('unit_of_competency', 'Assemble Electronic Products') }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="module_title" class="form-label required-field">Module Title</label>
                        <input type="text" name="module_title" id="module_title"
                               class="form-control" value="{{ old('module_title', 'Assembling Electronic Products') }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="module_name" class="form-label required-field">Module Name</label>
                        <input type="text" name="module_name" id="module_name"
                               class="form-control" value="{{ old('module_name', 'Competency based learning material') }}" required>
                    </div>

                    <div class="mb-3">
                        <x-rich-editor
                            name="table_of_contents"
                            label="Table of Contents"
                            placeholder="Enter the table of contents..."
                            :value="old('table_of_contents')"
                            toolbar="standard"
                            :height="100"
                        />
                    </div>

                    <div class="mb-3">
                        <x-rich-editor
                            name="how_to_use_cblm"
                            label="How to Use CBLM"
                            :value="old('how_to_use_cblm')"
                            toolbar="standard"
                            :height="80"
                        />
                    </div>

                    <div class="mb-3">
                        <x-rich-editor
                            name="introduction"
                            label="Introduction"
                            :value="old('introduction')"
                            toolbar="standard"
                            :height="80"
                        />
                    </div>

                    <div class="mb-3">
                        <x-rich-editor
                            name="learning_outcomes"
                            label="Learning Outcomes"
                            :value="old('learning_outcomes')"
                            toolbar="standard"
                            :height="80"
                        />
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Create Module</button>
                    </div>
                </form>
            </div>
        </div>

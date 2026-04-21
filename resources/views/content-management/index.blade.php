@extends('layouts.app')

@section('title', 'Content Management - EPAS-E')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="page-header">
                <div class="page-header-left">
                    <h1><i class="fas fa-cubes me-2"></i>Content Management</h1>
                    <p>Manage courses, modules, and learning materials</p>
                </div>
                <div class="page-header-actions">
                    <a href="{{ route('courses.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Create Course
                    </a>
                </div>
            </div>

            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            <!-- Delete Confirmation Modal -->
            <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Confirm Deletion</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <p id="deleteConfirmMessage">Are you sure you want to delete this item?</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Add New Content Popup Container -->
            <div id="addContentPopupContainer"></div>

            <!-- Courses Accordion -->
            <div class="accordion" id="coursesAccordion">
                @foreach($courses as $course)
                <div class="accordion-item">
                    <h2 class="accordion-header" id="courseHeading{{ $course->id }}">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#courseCollapse{{ $course->id }}" aria-expanded="false"
                            aria-controls="courseCollapse{{ $course->id }}">
                            <div class="d-flex justify-content-between align-items-center w-100 me-3">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-graduation-cap me-3 text-primary"></i>
                                    <div>
                                        <strong class="h5 mb-0">{{ $course->course_name }}</strong>
                                        <div class="text-muted small">{{ $course->course_code }}</div>
                                    </div>
                                </div>
                                <div class="d-flex gap-3">
                                    <span class="badge bg-primary">{{ $course->modules->count() }} Modules</span>
                                    <span class="badge bg-{{ $course->is_active ? 'success' : 'secondary' }}">
                                        {{ $course->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </div>
                            </div>
                        </button>
                    </h2>
                    <div id="courseCollapse{{ $course->id }}" class="accordion-collapse collapse"
                        aria-labelledby="courseHeading{{ $course->id }}" data-bs-parent="#coursesAccordion">
                        <div class="accordion-body bg-light">
                            <!-- Course Actions -->
                            <div class="d-flex gap-2 mb-4 p-3 bg-white rounded">
                                <a href="{{ route('courses.edit', $course->id) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-edit me-1"></i>Edit Course
                                </a>
                                <a href="{{ route('courses.modules.create', $course) }}" class="btn btn-sm btn-outline-success">
                                    <i class="fas fa-plus me-1"></i>Add Module
                                </a>

                                <!-- Delete Course Button -->
                                <button type="button" class="btn btn-sm btn-outline-danger delete-course-btn"
                                    data-course-id="{{ $course->id }}"
                                    data-course-name="{{ $course->course_name }}"
                                    data-modules-count="{{ $course->modules->count() }}">
                                    <i class="fas fa-trash me-1"></i>Delete Course
                                </button>
                            </div>

                            <!-- Modules Accordion -->
                            @if($course->modules->count() > 0)
                            <div class="accordion" id="modulesAccordion{{ $course->id }}">
                                @foreach($course->modules as $module)
                                <div class="accordion-item border-0 mb-3">
                                    <h2 class="accordion-header" id="moduleHeading{{ $module->id }}">
                                        <button class="accordion-button collapsed bg-white border rounded" type="button" data-bs-toggle="collapse"
                                            data-bs-target="#moduleCollapse{{ $module->id }}" aria-expanded="false"
                                            aria-controls="moduleCollapse{{ $module->id }}">
                                            <div class="d-flex justify-content-between align-items-center w-100 me-3">
                                                <div class="d-flex align-items-center">
                                                    <i class="fas fa-folder me-3 text-info"></i>
                                                    <div>
                                                        <strong class="h6 mb-0">Module {{ $module->module_number }}: {{ $module->module_name }}</strong>
                                                        @if($module->description)
                                                        <div class="text-muted small">{{ Str::limit($module->description, 80) }}</div>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="d-flex gap-2">
                                                    <span class="badge bg-info">{{ $module->informationSheets->count() }} Info Sheets</span>
                                                    <span class="badge bg-{{ $module->is_active ? 'success' : 'secondary' }}">
                                                        {{ $module->is_active ? 'Active' : 'Inactive' }}
                                                    </span>
                                                </div>
                                            </div>
                                        </button>
                                    </h2>
                                    <div id="moduleCollapse{{ $module->id }}" class="accordion-collapse collapse"
                                        aria-labelledby="moduleHeading{{ $module->id }}" data-bs-parent="#modulesAccordion{{ $course->id }}">
                                        <div class="accordion-body bg-white border rounded">
                                            <!-- Module Actions -->
                                            <div class="d-flex gap-2 mb-3 p-3 bg-light rounded">
                                                <a href="{{ route('courses.modules.edit', [$course, $module]) }}" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-edit me-1"></i>Edit Module
                                                </a>
                                                <a href="{{ route('courses.modules.sheets.create', [$course, $module]) }}" class="btn btn-sm btn-outline-success">
                                                    <i class="fas fa-plus me-1"></i>Add Information Sheet
                                                </a>

                                                <!-- Delete Module Button -->
                                                <button type="button" class="btn btn-sm btn-outline-danger delete-module-btn"
                                                    data-course-id="{{ $course->id }}"
                                                    data-module-id="{{ $module->id }}"
                                                    data-module-slug="{{ $module->slug }}"
                                                    data-module-name="Module {{ $module->module_number }}: {{ $module->module_name }}"
                                                    data-info-sheets-count="{{ $module->informationSheets->count() }}">
                                                    <i class="fas fa-trash me-1"></i>Delete Module
                                                </button>
                                            </div>

                                            <!-- Information Sheets -->
                                            @if($module->informationSheets->count() > 0)
                                            <div class="accordion" id="infoSheetsAccordion{{ $module->id }}">
                                                @foreach($module->informationSheets as $infoSheet)
                                                <div class="accordion-item border-0 mb-2">
                                                    <h2 class="accordion-header" id="infoSheetHeading{{ $infoSheet->id }}">
                                                        <button class="accordion-button collapsed bg-light border rounded" type="button" data-bs-toggle="collapse"
                                                            data-bs-target="#infoSheetCollapse{{ $infoSheet->id }}" aria-expanded="false"
                                                            aria-controls="infoSheetCollapse{{ $infoSheet->id }}">
                                                            <div class="d-flex justify-content-between align-items-center w-100 me-3">
                                                                <div class="d-flex align-items-center">
                                                                    <i class="fas fa-file-alt me-3 text-warning"></i>
                                                                    <div>
                                                                        <strong class="h6 mb-0">Information Sheet {{ $infoSheet->sheet_number }}: {{ $infoSheet->title }}</strong>
                                                                    </div>
                                                                </div>
                                                                <div class="d-flex gap-1">
                                                                    @if($infoSheet->topics->count() > 0)
                                                                    <span class="badge bg-primary">{{ $infoSheet->topics->count() }} Topics</span>
                                                                    @endif
                                                                    @if($infoSheet->selfChecks && $infoSheet->selfChecks->count() > 0)
                                                                    <span class="badge bg-success">{{ $infoSheet->selfChecks->count() }} Self-Checks</span>
                                                                    @endif
                                                                    @if($infoSheet->taskSheets && $infoSheet->taskSheets->count() > 0)
                                                                    <span class="badge bg-info">{{ $infoSheet->taskSheets->count() }} Task Sheets</span>
                                                                    @endif
                                                                    @if($infoSheet->jobSheets && $infoSheet->jobSheets->count() > 0)
                                                                    <span class="badge bg-secondary">{{ $infoSheet->jobSheets->count() }} Job Sheets</span>
                                                                    @endif
                                                                    @if($infoSheet->homeworks && $infoSheet->homeworks->count() > 0)
                                                                    <span class="badge bg-dark">{{ $infoSheet->homeworks->count() }} Homework</span>
                                                                    @endif
                                                                    @if($infoSheet->documentAssessments && $infoSheet->documentAssessments->count() > 0)
                                                                    <span class="badge" style="background: #7c3aed; color: #fff;">{{ $infoSheet->documentAssessments->count() }} Doc Assessment</span>
                                                                    @endif
                                                                    @if($infoSheet->checklists && $infoSheet->checklists->count() > 0)
                                                                    <span class="badge bg-danger">{{ $infoSheet->checklists->count() }} Checklists</span>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </button>
                                                    </h2>
                                                    <div id="infoSheetCollapse{{ $infoSheet->id }}" class="accordion-collapse collapse"
                                                        aria-labelledby="infoSheetHeading{{ $infoSheet->id }}" data-bs-parent="#infoSheetsAccordion{{ $module->id }}">
                                                        <div class="accordion-body bg-white border rounded">
                                                            <!-- Information Sheet Actions -->
                                                            <div class="d-flex gap-2 mb-3 p-3 bg-light rounded">
                                                                <a href="{{ route('information-sheets.edit', [$module->id, $infoSheet->id]) }}"
                                                                    class="btn btn-sm btn-outline-primary">
                                                                    <i class="fas fa-edit me-1"></i>Edit Information Sheet
                                                                </a>
                                                                <a href="{{ route('topics.create', $infoSheet->id) }}"
                                                                    class="btn btn-sm btn-outline-success">
                                                                    <i class="fas fa-plus me-1"></i>Add Topic
                                                                </a>
                                                                <button type="button" class="btn btn-sm btn-outline-danger delete-info-sheet-btn"
                                                                    data-course-id="{{ $course->id }}"
                                                                    data-module-id="{{ $module->id }}"
                                                                    data-info-sheet-id="{{ $infoSheet->id }}"
                                                                    data-info-sheet-name="Information Sheet {{ $infoSheet->sheet_number }}: {{ $infoSheet->title }}">
                                                                    <i class="fas fa-trash me-1"></i>Delete Information Sheet
                                                                </button>
                                                            </div>

                                                            <!-- Content Items -->
                                                            <div class="content-items">
                                                                <h5 class="text-muted mb-3 border-bottom pb-2">Content Items</h5>

                                                                <!-- Topics -->
                                                                @if($infoSheet->topics->count() > 0)
                                                                <div class="mb-4">
                                                                    <h6 class="text-primary mb-3">
                                                                        <i class="fas fa-file-alt me-2"></i>Topics
                                                                    </h6>
                                                                    <div class="list-group">
                                                                        @foreach($infoSheet->topics as $topic)
                                                                        <div class="list-group-item d-flex justify-content-between align-items-center">
                                                                            <div>
                                                                                <strong>{{ $topic->topic_number }}.</strong> {{ $topic->title }}
                                                                                @if($topic->description)
                                                                                <p class="mb-0 text-muted small mt-1">{{ Str::limit($topic->description, 100) }}</p>
                                                                                @endif
                                                                            </div>
                                                                            <div class="btn-group btn-group-sm">
                                                                                <a href="{{ route('topics.edit', [$infoSheet->id, $topic->id]) }}" class="btn btn-outline-primary">Edit</a>
                                                                                <button class="btn btn-outline-danger delete-topic-btn"
                                                                                    data-course-id="{{ $course->id }}"
                                                                                    data-module-id="{{ $module->id }}"
                                                                                    data-info-sheet-id="{{ $infoSheet->id }}"
                                                                                    data-topic-id="{{ $topic->id }}"
                                                                                    data-topic-name="{{ $topic->title }}">
                                                                                    Delete
                                                                                </button>
                                                                            </div>
                                                                        </div>
                                                                        @endforeach
                                                                    </div>
                                                                </div>
                                                                @endif

                                                                <!-- Self-Checks -->
                                                                @if($infoSheet->selfChecks && $infoSheet->selfChecks->count() > 0)
                                                                <div class="mb-4">
                                                                    <h6 class="text-warning mb-3">
                                                                        <i class="fas fa-question-circle me-2"></i>Self-Checks
                                                                    </h6>
                                                                    <div class="list-group">
                                                                        @foreach($infoSheet->selfChecks as $selfCheck)
                                                                        <div class="list-group-item d-flex justify-content-between align-items-center">
                                                                            <div>
                                                                                <strong>{{ $selfCheck->check_number }}.</strong> {{ $selfCheck->title }}
                                                                                <p class="mb-0 text-muted small mt-1">
                                                                                    {{ $selfCheck->questions ? $selfCheck->questions->count() : 0 }} Questions
                                                                                </p>
                                                                            </div>
                                                                            <div class="btn-group btn-group-sm">
                                                                                <a href="{{ route('self-checks.edit', [$infoSheet->id, $selfCheck->id]) }}" class="btn btn-outline-primary">Edit</a>
                                                                                <button class="btn btn-outline-danger delete-self-check-btn"
                                                                                    data-course-id="{{ $course->id }}"
                                                                                    data-module-id="{{ $module->id }}"
                                                                                    data-info-sheet-id="{{ $infoSheet->id }}"
                                                                                    data-self-check-id="{{ $selfCheck->id }}"
                                                                                    data-self-check-name="{{ $selfCheck->title }}">
                                                                                    Delete
                                                                                </button>
                                                                            </div>
                                                                        </div>
                                                                        @endforeach
                                                                    </div>
                                                                </div>
                                                                @endif

                                                                <!-- Task Sheets -->
                                                                @if($infoSheet->taskSheets && $infoSheet->taskSheets->count() > 0)
                                                                <div class="mb-4">
                                                                    <h6 class="text-info mb-3">
                                                                        <i class="fas fa-tasks me-2"></i>Task Sheets
                                                                    </h6>
                                                                    <div class="list-group">
                                                                        @foreach($infoSheet->taskSheets as $taskSheet)
                                                                        <div class="list-group-item d-flex justify-content-between align-items-center">
                                                                            <div>
                                                                                <strong>{{ $taskSheet->task_number ?? '' }}.</strong> {{ $taskSheet->title }}
                                                                            </div>
                                                                            <div class="btn-group btn-group-sm">
                                                                                <a href="{{ route('task-sheets.edit', [$infoSheet->id, $taskSheet->id]) }}" class="btn btn-outline-primary">Edit</a>
                                                                                <button class="btn btn-outline-danger delete-task-sheet-btn"
                                                                                    data-course-id="{{ $course->id }}"
                                                                                    data-module-id="{{ $module->id }}"
                                                                                    data-info-sheet-id="{{ $infoSheet->id }}"
                                                                                    data-task-sheet-id="{{ $taskSheet->id }}"
                                                                                    data-task-sheet-name="{{ $taskSheet->title }}">
                                                                                    Delete
                                                                                </button>
                                                                            </div>
                                                                        </div>
                                                                        @endforeach
                                                                    </div>
                                                                </div>
                                                                @endif

                                                                <!-- Job Sheets -->
                                                                @if($infoSheet->jobSheets && $infoSheet->jobSheets->count() > 0)
                                                                <div class="mb-4">
                                                                    <h6 class="text-success mb-3">
                                                                        <i class="fas fa-briefcase me-2"></i>Job Sheets
                                                                    </h6>
                                                                    <div class="list-group">
                                                                        @foreach($infoSheet->jobSheets as $jobSheet)
                                                                        <div class="list-group-item d-flex justify-content-between align-items-center">
                                                                            <div>
                                                                                <strong>{{ $jobSheet->job_number ?? '' }}.</strong> {{ $jobSheet->title }}
                                                                            </div>
                                                                            <div class="btn-group btn-group-sm">
                                                                                <a href="{{ route('job-sheets.edit', [$infoSheet->id, $jobSheet->id]) }}" class="btn btn-outline-primary">Edit</a>
                                                                                <button class="btn btn-outline-danger delete-job-sheet-btn"
                                                                                    data-course-id="{{ $course->id }}"
                                                                                    data-module-id="{{ $module->id }}"
                                                                                    data-info-sheet-id="{{ $infoSheet->id }}"
                                                                                    data-job-sheet-id="{{ $jobSheet->id }}"
                                                                                    data-job-sheet-name="{{ $jobSheet->title }}">
                                                                                    Delete
                                                                                </button>
                                                                            </div>
                                                                        </div>
                                                                        @endforeach
                                                                    </div>
                                                                </div>
                                                                @endif

                                                                <!-- Homework -->
                                                                @if($infoSheet->homeworks && $infoSheet->homeworks->count() > 0)
                                                                <div class="mb-4">
                                                                    <h6 class="text-secondary mb-3">
                                                                        <i class="fas fa-book me-2"></i>Homework
                                                                    </h6>
                                                                    <div class="list-group">
                                                                        @foreach($infoSheet->homeworks as $homework)
                                                                        <div class="list-group-item d-flex justify-content-between align-items-center">
                                                                            <div>
                                                                                <strong>{{ $homework->title }}</strong>
                                                                                @if($homework->due_date)
                                                                                <p class="mb-0 text-muted small mt-1">
                                                                                    Due: {{ $homework->due_date->format('M d, Y') }}
                                                                                </p>
                                                                                @endif
                                                                            </div>
                                                                            <div class="btn-group btn-group-sm">
                                                                                <a href="{{ route('homeworks.edit', [$infoSheet->id, $homework->id]) }}" class="btn btn-outline-primary">Edit</a>
                                                                                <button class="btn btn-outline-danger delete-homework-btn"
                                                                                    data-course-id="{{ $course->id }}"
                                                                                    data-module-id="{{ $module->id }}"
                                                                                    data-info-sheet-id="{{ $infoSheet->id }}"
                                                                                    data-homework-id="{{ $homework->id }}"
                                                                                    data-homework-name="{{ $homework->title }}">
                                                                                    Delete
                                                                                </button>
                                                                            </div>
                                                                        </div>
                                                                        @endforeach
                                                                    </div>
                                                                </div>
                                                                @endif

                                                                <!-- Checklists -->
                                                                @if($infoSheet->checklists && $infoSheet->checklists->count() > 0)
                                                                <div class="mb-4">
                                                                    <h6 class="text-danger mb-3">
                                                                        <i class="fas fa-clipboard-check me-2"></i>Checklists
                                                                    </h6>
                                                                    <div class="list-group">
                                                                        @foreach($infoSheet->checklists as $checklist)
                                                                        <div class="list-group-item d-flex justify-content-between align-items-center">
                                                                            <div>
                                                                                <strong>{{ $checklist->title }}</strong>
                                                                            </div>
                                                                            <div class="btn-group btn-group-sm">
                                                                                <a href="{{ route('checklists.edit', [$infoSheet->id, $checklist->id]) }}" class="btn btn-outline-primary">Edit</a>
                                                                                <button class="btn btn-outline-danger delete-checklist-btn"
                                                                                    data-course-id="{{ $course->id }}"
                                                                                    data-module-id="{{ $module->id }}"
                                                                                    data-info-sheet-id="{{ $infoSheet->id }}"
                                                                                    data-checklist-id="{{ $checklist->id }}"
                                                                                    data-checklist-name="{{ $checklist->title }}">
                                                                                    Delete
                                                                                </button>
                                                                            </div>
                                                                        </div>
                                                                        @endforeach
                                                                    </div>
                                                                </div>
                                                                @endif

                                                                <!-- Document Assessments -->
                                                                @if($infoSheet->documentAssessments && $infoSheet->documentAssessments->count() > 0)
                                                                <div class="mb-4">
                                                                    <h6 class="mb-3" style="color: #7c3aed;">
                                                                        <i class="fas fa-file-word me-2"></i>Document Assessments
                                                                    </h6>
                                                                    <div class="list-group">
                                                                        @foreach($infoSheet->documentAssessments as $docAssessment)
                                                                        <div class="list-group-item d-flex justify-content-between align-items-center">
                                                                            <div>
                                                                                <strong>{{ $docAssessment->title }}</strong>
                                                                                <span class="badge bg-light text-dark ms-1">{{ $docAssessment->max_points }} pts</span>
                                                                                @if($docAssessment->file_type)
                                                                                <span class="badge bg-light text-muted ms-1">{{ strtoupper($docAssessment->file_type) }}</span>
                                                                                @endif
                                                                            </div>
                                                                            <div class="btn-group btn-group-sm">
                                                                                <a href="{{ route('document-assessments.show', $docAssessment) }}" class="btn btn-outline-secondary">View</a>
                                                                                <a href="{{ route('document-assessments.edit', [$infoSheet->id, $docAssessment->id]) }}" class="btn btn-outline-primary">Edit</a>
                                                                                <button class="btn btn-outline-danger delete-doc-assessment-btn"
                                                                                    data-course-id="{{ $course->id }}"
                                                                                    data-module-id="{{ $module->id }}"
                                                                                    data-info-sheet-id="{{ $infoSheet->id }}"
                                                                                    data-doc-assessment-id="{{ $docAssessment->id }}"
                                                                                    data-doc-assessment-name="{{ $docAssessment->title }}">
                                                                                    Delete
                                                                                </button>
                                                                            </div>
                                                                        </div>
                                                                        @endforeach
                                                                    </div>
                                                                </div>
                                                                @endif

                                                                <!-- Add Content Section -->
                                                                <div class="add-content-section text-center p-4 bg-light rounded border position-relative">
                                                                    <div class="popup d-inline-block">
                                                                        <button type="button" class="btn btn-success btn-lg popup-btn" data-info-sheet-id="{{ $infoSheet->id }}">
                                                                            <i class="fas fa-plus-circle me-2"></i>Add New Content
                                                                        </button>
                                                                        <div class="popuptext" id="popup{{ $infoSheet->id }}">
                                                                            <div class="popup-content">
                                                                                <div class="popup-item" data-action="topic" data-info-sheet="{{ $infoSheet->id }}">
                                                                                    <i class="fas fa-file-alt"></i> Topic
                                                                                </div>
                                                                                <div class="popup-item" data-action="self-check" data-info-sheet="{{ $infoSheet->id }}">
                                                                                    <i class="fas fa-question-circle"></i> Self-Check
                                                                                </div>
                                                                                <div class="popup-item" data-action="task-sheet" data-info-sheet="{{ $infoSheet->id }}">
                                                                                    <i class="fas fa-tasks"></i> Task Sheet
                                                                                </div>
                                                                                <div class="popup-item" data-action="job-sheet" data-info-sheet="{{ $infoSheet->id }}">
                                                                                    <i class="fas fa-briefcase"></i> Job Sheet
                                                                                </div>
                                                                                <div class="popup-item" data-action="homework" data-info-sheet="{{ $infoSheet->id }}">
                                                                                    <i class="fas fa-book"></i> Homework
                                                                                </div>
                                                                                <div class="popup-item" data-action="performance-criteria" data-info-sheet="{{ $infoSheet->id }}">
                                                                                    <i class="fas fa-clipboard-check"></i> Performance
                                                                                </div>
                                                                                <div class="popup-item" data-action="checklist" data-info-sheet="{{ $infoSheet->id }}">
                                                                                    <i class="fas fa-list-check"></i> Check List
                                                                                </div>
                                                                                <div class="popup-item" data-action="document-assessment" data-info-sheet="{{ $infoSheet->id }}">
                                                                                    <i class="fas fa-file-word"></i> Doc Assessment
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <small class="d-block text-muted mt-2">Click to add topics, self-checks, task sheets, and more</small>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                @endforeach
                                            </div>
                                            @else
                                            <div class="text-center py-5 bg-light rounded">
                                                <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                                                <h5>No Information Sheets</h5>
                                                <p class="text-muted mb-3">This module doesn't have any information sheets yet.</p>
                                                <a href="{{ route('information-sheets.create', $module->id) }}" class="btn btn-primary">
                                                    <i class="fas fa-plus me-1"></i>Create First Information Sheet
                                                </a>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            @else
                            <div class="text-center py-5 bg-light rounded">
                                <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                                <h5>No Modules Created</h5>
                                <p class="text-muted mb-3">This course doesn't have any modules yet.</p>
                                <a href="{{ route('courses.modules.create', $course) }}" class="btn btn-primary">
                                    <i class="fas fa-plus me-1"></i>Create First Module
                                </a>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            @if($courses->count() == 0)
            <div class="text-center py-5">
                <i class="fas fa-graduation-cap fa-3x text-muted mb-3"></i>
                <h4>No Courses Available</h4>
                <p class="text-muted">No learning courses have been created yet.</p>
                <a href="{{ route('courses.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Create First Course
                </a>
            </div>
            @endif
        </div>
    </div>
</div>

<style>
    /* Accordion polish */
    .accordion-item {
        border-radius: var(--cb-radius-sm) !important;
        box-shadow: var(--cb-shadow-card);
        border: 1px solid var(--cb-border);
        overflow: hidden;
        margin-bottom: 0.75rem;
    }

    .accordion-button {
        transition: all 0.3s ease;
        border-radius: var(--cb-radius-sm) !important;
    }

    .accordion-button:not(.collapsed) {
        background-color: #fff8e1;
        color: #bb8954;
        box-shadow: inset 0 -1px 0 rgba(0, 0, 0, .125);
    }

    .accordion-button:hover {
        background-color: var(--cb-surface-alt);
    }

    .badge {
        font-size: 0.75em;
    }

    /* List item polish */
    .list-group-item {
        border-left: 3px solid transparent;
        transition: all 0.2s ease;
        border-radius: var(--cb-radius-sm);
    }

    .list-group-item:hover {
        border-left-color: #ffb902;
        background-color: var(--cb-surface-alt);
        box-shadow: var(--cb-shadow-card);
    }

    /* Action bar polish */
    .accordion-body .d-flex.gap-2.p-3 {
        border-radius: var(--cb-radius-sm);
        box-shadow: var(--cb-shadow-card);
    }

    /* Add content section */
    .add-content-section {
        background: linear-gradient(135deg, var(--cb-surface-alt) 0%, var(--cb-border) 100%);
        border-radius: var(--cb-radius-sm);
    }

    /* Empty state polish */
    .text-center.py-5.bg-light {
        border-radius: var(--cb-radius-md);
    }

    /* Dark mode overrides */
    .dark-mode .accordion-item {
        border-color: var(--border);
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.2);
    }

    .dark-mode .accordion-button:not(.collapsed) {
        background-color: var(--card-header-bg);
        color: var(--primary);
        box-shadow: inset 0 -1px 0 var(--border);
    }

    .dark-mode .accordion-button:hover {
        background-color: var(--light-gray);
    }

    .dark-mode .list-group-item:hover {
        border-left-color: var(--primary);
        background-color: var(--light-gray);
    }

    .dark-mode .add-content-section {
        background: linear-gradient(135deg, var(--light-gray) 0%, var(--card-header-bg) 100%);
    }

    /* Add Content Popup Styles */
    .popup {
        position: relative;
        display: inline-block;
        cursor: pointer;
    }

    .popup .popuptext {
        visibility: hidden;
        width: 220px;
        background-color: #555;
        color: #fff;
        text-align: left;
        border-radius: 8px;
        padding: 10px 0;
        position: absolute;
        z-index: 1000;
        bottom: 125%;
        left: 50%;
        transform: translateX(-50%);
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
    }

    /* Popup arrow */
    .popup .popuptext::after {
        content: "";
        position: absolute;
        top: 100%;
        left: 50%;
        margin-left: -8px;
        border-width: 8px;
        border-style: solid;
        border-color: #555 transparent transparent transparent;
    }

    .popup .popuptext.show {
        visibility: visible;
        animation: fadeIn 0.2s ease-in-out;
    }

    .popup-content {
        display: flex;
        flex-direction: column;
    }

    .popup-item {
        padding: 10px 20px;
        cursor: pointer;
        transition: background 0.2s;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .popup-item:hover {
        background-color: #666;
    }

    .popup-item i {
        width: 20px;
        text-align: center;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateX(-50%) translateY(-10px);
        }

        to {
            opacity: 1;
            transform: translateX(-50%) translateY(0);
        }
    }

    /* Dark mode popup */
    .dark-mode .popup .popuptext {
        background-color: #3a3a3a;
    }

    .dark-mode .popup .popuptext::after {
        border-color: #3a3a3a transparent transparent transparent;
    }

    .dark-mode .popup-item:hover {
        background-color: #4a4a4a;
    }

    /* Close popup when clicking outside */
    .popup-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 999;
        display: none;
    }

    .popup-overlay.active {
        display: block;
    }
</style>

@push('scripts')
<!-- Cache-bust: 2026-03-22-v2 -->
<script nonce="{{ app('request')->secure() ? 'nonce-'.bin2hex(random_bytes(16)) : '' }}">
    (function() {
        'use strict';

        let currentDeleteUrl = '';
        let currentDeleteCallback = null;

        const deleteModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
        const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
        const deleteConfirmMessage = document.getElementById('deleteConfirmMessage');
        const deleteConfirmModal = document.getElementById('deleteConfirmModal');

        // Route mapping for content types - using base URLs
        const routes = {
            'topic': function(infoSheetId) {
                return '/information-sheets/' + infoSheetId + '/topics/create';
            },
            'self-check': function(infoSheetId) {
                return '/information-sheets/' + infoSheetId + '/self-checks/create';
            },
            'task-sheet': function(infoSheetId) {
                return '/information-sheets/' + infoSheetId + '/task-sheets/create';
            },
            'job-sheet': function(infoSheetId) {
                return '/information-sheets/' + infoSheetId + '/job-sheets/create';
            },
            'homework': function(infoSheetId) {
                return '/information-sheets/' + infoSheetId + '/homeworks/create';
            },
            'performance-criteria': function(infoSheetId) {
                return '/information-sheets/' + infoSheetId + '/performance-criteria/create';
            },
            'checklist': function(infoSheetId) {
                return '/information-sheets/' + infoSheetId + '/checklists/create';
            },
            'document-assessment': function(infoSheetId) {
                return '/information-sheets/' + infoSheetId + '/document-assessments/create';
            }
        };

        // Helper functions
        function getClosest(el, selector) {
            if (!el || el === document) return null;
            if (el.matches && el.matches(selector)) return el;
            return getClosest(el.parentNode, selector);
        }

        function fadeOutAndRemove(el, duration, callback) {
            if (!el) return;
            el.style.transition = 'opacity ' + duration + 'ms, height ' + duration + 'ms, margin ' + duration + 'ms';
            el.style.opacity = '0';
            el.style.height = '0';
            el.style.marginTop = '0';
            el.style.marginBottom = '0';
            el.style.overflow = 'hidden';
            setTimeout(function() {
                el.remove();
                if (callback) callback();
            }, duration);
        }

        function getDataAttr(el, attr) {
            if (!el) return null;
            return el.getAttribute('data-' + attr);
        }

        function setModalMessage(message) {
            if (deleteConfirmMessage) {
                deleteConfirmMessage.textContent = message;
            }
        }

        function setButtonLoading(button, loading, defaultHtml) {
            if (!button) return;
            if (loading) {
                button.disabled = true;
                button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Deleting...';
            } else {
                button.disabled = false;
                button.innerHTML = defaultHtml || 'Delete';
            }
        }

        // Popup toggle functionality
        document.addEventListener('click', function(e) {
            const popupBtn = e.target.closest('.popup-btn');
            if (popupBtn) {
                e.stopPropagation();
                const popups = document.querySelectorAll('.popuptext');
                popups.forEach(function(popup) {
                    if (popup !== popupBtn.nextElementSibling) {
                        popup.classList.remove('show');
                    }
                });
                const popup = popupBtn.nextElementSibling;
                if (popup && popup.classList.contains('popuptext')) {
                    popup.classList.toggle('show');
                }
                return;
            }

            const popupItem = e.target.closest('.popup-item');
            if (popupItem) {
                e.stopPropagation();
                const action = getDataAttr(popupItem, 'action');
                const infoSheetId = getDataAttr(popupItem, 'info-sheet');

                if (routes[action] && infoSheetId) {
                    const popups = document.querySelectorAll('.popuptext');
                    popups.forEach(function(popup) {
                        popup.classList.remove('show');
                    });
                    window.location.href = routes[action](infoSheetId);
                }
                return;
            }

            const popup = e.target.closest('.popup');
            if (!popup) {
                const popups = document.querySelectorAll('.popuptext');
                popups.forEach(function(p) {
                    p.classList.remove('show');
                });
            }
        });

        // Delete handlers
        function setupDeleteHandler(selector, getIdFn, getMessageFn, getUrlFn, getCallbackFn) {
            document.addEventListener('click', function(e) {
                const btn = e.target.closest(selector);
                if (!btn) return;
                e.preventDefault();

                const id = getIdFn(btn);
                const message = getMessageFn(btn, id);
                setModalMessage(message);

                // Get the URL immediately from the button that was clicked
                const url = getUrlFn(btn, id);
                if (!url) {
                    console.error('[Content Management] Delete URL is null/undefined!');
                    showAlert('Error: Unable to determine delete URL.', 'error');
                    return;
                }
                currentDeleteUrl = url;
                currentDeleteCallback = getCallbackFn(btn, id);

                deleteModal.show();
            });
        }

        // Delete Course
        setupDeleteHandler('.delete-course-btn',
            function(btn) {
                return getDataAttr(btn, 'course-id');
            },
            function(btn, id) {
                const courseName = getDataAttr(btn, 'course-name');
                const modulesCount = getDataAttr(btn, 'modules-count');
                let message = 'Are you sure you want to delete the course "' + courseName + '"?';
                if (modulesCount && parseInt(modulesCount) > 0) {
                    message += ' This course contains ' + modulesCount + ' module(s) and ALL associated content will be permanently deleted.';
                }
                return message;
            },
            function(btn, id) {
                return '/courses/' + id;
            },
            function(btn, id) {
                return function(response) {
                    const el = document.getElementById('courseHeading' + id);
                    if (el) {
                        const accordionItem = getClosest(el, '.accordion-item');
                        fadeOutAndRemove(accordionItem, 300, function() {
                            showAlert('Course deleted successfully!', 'success');
                            checkIfNoCourses();
                        });
                    }
                };
            }
        );

        // Delete Module
        setupDeleteHandler('.delete-module-btn',
            function(btn) {
                return getDataAttr(btn, 'module-id');
            },
            function(btn, id) {
                const moduleName = getDataAttr(btn, 'module-name');
                const infoSheetsCount = getDataAttr(btn, 'info-sheets-count');
                const courseId = getDataAttr(btn, 'course-id');
                let message = 'Are you sure you want to delete the module "' + moduleName + '"?';
                if (infoSheetsCount && parseInt(infoSheetsCount) > 0) {
                    message += ' This module contains ' + infoSheetsCount + ' information sheet(s) and all associated content will be permanently deleted.';
                }
                return message;
            },
            function(btn, id) {
                const courseId = getDataAttr(btn, 'course-id');
                const slug = getDataAttr(btn, 'module-slug');
                if (!courseId || !slug) {
                    console.error('Missing course-id or module-slug for module delete');
                    return null;
                }
                return '/courses/' + courseId + '/module-' + slug;
            },
            function(btn, id) {
                return function(response) {
                    const numericId = getDataAttr(btn, 'module-numeric-id') || id;
                    const el = document.getElementById('moduleHeading' + numericId);
                    if (el) {
                        const accordionItem = getClosest(el, '.accordion-item');
                        fadeOutAndRemove(accordionItem, 300, function() {
                            showAlert('Module deleted successfully!', 'success');
                        });
                    }
                };
            }
        );

        // Delete Information Sheet
        setupDeleteHandler('.delete-info-sheet-btn',
            function(btn) {
                return getDataAttr(btn, 'info-sheet-id');
            },
            function(btn, id) {
                const infoSheetName = getDataAttr(btn, 'info-sheet-name');
                return 'Are you sure you want to delete the information sheet "' + infoSheetName + '"?';
            },
            function(btn, id) {
                const courseId = getDataAttr(btn, 'course-id');
                const moduleId = getDataAttr(btn, 'module-id');
                if (!courseId || !moduleId) {
                    console.error('Missing course-id or module-id for information sheet delete');
                    return null;
                }
                return '/courses/' + courseId + '/module-' + moduleId + '/information-sheets/' + id;
            },
            function(btn, id) {
                return function(response) {
                    const el = document.getElementById('infoSheetHeading' + id);
                    if (el) {
                        const accordionItem = getClosest(el, '.accordion-item');
                        fadeOutAndRemove(accordionItem, 300, function() {
                            showAlert('Information sheet deleted successfully!', 'success');
                        });
                    }
                };
            }
        );

        // Delete Self-Check
        setupDeleteHandler('.delete-self-check-btn',
            function(btn) {
                return getDataAttr(btn, 'self-check-id');
            },
            function(btn, id) {
                const selfCheckName = getDataAttr(btn, 'self-check-name');
                return 'Are you sure you want to delete the self-check "' + selfCheckName + '"? All questions will be permanently deleted.';
            },
            function(btn, id) {
                const infoSheetId = getDataAttr(btn, 'info-sheet-id');
                if (!infoSheetId) {
                    console.error('Missing info-sheet-id for self-check delete');
                    return null;
                }
                return '/information-sheets/' + infoSheetId + '/self-checks/' + id;
            },
            function(btn, id) {
                return function(response) {
                    const el = getClosest(btn, '.list-group-item');
                    fadeOutAndRemove(el, 300, function() {
                        showAlert('Self-check deleted successfully!', 'success');
                    });
                };
            }
        );

        // Delete Task Sheet
        setupDeleteHandler('.delete-task-sheet-btn',
            function(btn) {
                return getDataAttr(btn, 'task-sheet-id');
            },
            function(btn, id) {
                const taskSheetName = getDataAttr(btn, 'task-sheet-name');
                return 'Are you sure you want to delete the task sheet "' + taskSheetName + '"?';
            },
            function(btn, id) {
                const infoSheetId = getDataAttr(btn, 'info-sheet-id');
                if (!infoSheetId) {
                    console.error('Missing info-sheet-id for task-sheet delete');
                    return null;
                }
                return '/information-sheets/' + infoSheetId + '/task-sheets/' + id;
            },
            function(btn, id) {
                return function(response) {
                    const el = getClosest(btn, '.list-group-item');
                    fadeOutAndRemove(el, 300, function() {
                        showAlert('Task sheet deleted successfully!', 'success');
                    });
                };
            }
        );

        // Delete Job Sheet
        setupDeleteHandler('.delete-job-sheet-btn',
            function(btn) {
                return getDataAttr(btn, 'job-sheet-id');
            },
            function(btn, id) {
                const jobSheetName = getDataAttr(btn, 'job-sheet-name');
                return 'Are you sure you want to delete the job sheet "' + jobSheetName + '"?';
            },
            function(btn, id) {
                const infoSheetId = getDataAttr(btn, 'info-sheet-id');
                if (!infoSheetId) {
                    console.error('Missing info-sheet-id for job-sheet delete');
                    return null;
                }
                return '/information-sheets/' + infoSheetId + '/job-sheets/' + id;
            },
            function(btn, id) {
                return function(response) {
                    const el = getClosest(btn, '.list-group-item');
                    fadeOutAndRemove(el, 300, function() {
                        showAlert('Job sheet deleted successfully!', 'success');
                    });
                };
            }
        );

        // Delete Homework
        setupDeleteHandler('.delete-homework-btn',
            function(btn) {
                return getDataAttr(btn, 'homework-id');
            },
            function(btn, id) {
                const homeworkName = getDataAttr(btn, 'homework-name');
                return 'Are you sure you want to delete the homework "' + homeworkName + '"?';
            },
            function(btn, id) {
                const infoSheetId = getDataAttr(btn, 'info-sheet-id');
                if (!infoSheetId) {
                    console.error('Missing info-sheet-id for homework delete');
                    return null;
                }
                return '/information-sheets/' + infoSheetId + '/homeworks/' + id;
            },
            function(btn, id) {
                return function(response) {
                    const el = getClosest(btn, '.list-group-item');
                    fadeOutAndRemove(el, 300, function() {
                        showAlert('Homework deleted successfully!', 'success');
                    });
                };
            }
        );

        // Delete Checklist
        setupDeleteHandler('.delete-checklist-btn',
            function(btn) {
                return getDataAttr(btn, 'checklist-id');
            },
            function(btn, id) {
                const checklistName = getDataAttr(btn, 'checklist-name');
                return 'Are you sure you want to delete the checklist "' + checklistName + '"?';
            },
            function(btn, id) {
                const infoSheetId = getDataAttr(btn, 'info-sheet-id');
                if (!infoSheetId) {
                    console.error('Missing info-sheet-id for checklist delete');
                    return null;
                }
                return '/information-sheets/' + infoSheetId + '/checklists/' + id;
            },
            function(btn, id) {
                return function(response) {
                    const el = getClosest(btn, '.list-group-item');
                    fadeOutAndRemove(el, 300, function() {
                        showAlert('Checklist deleted successfully!', 'success');
                    });
                };
            }
        );

        // Delete Document Assessment
        setupDeleteHandler('.delete-doc-assessment-btn',
            function(btn) {
                return getDataAttr(btn, 'doc-assessment-id');
            },
            function(btn, id) {
                const docAssessmentName = getDataAttr(btn, 'doc-assessment-name');
                return 'Are you sure you want to delete the document assessment "' + docAssessmentName + '"?';
            },
            function(btn, id) {
                const infoSheetId = getDataAttr(btn, 'info-sheet-id');
                if (!infoSheetId) {
                    console.error('Missing info-sheet-id for document assessment delete');
                    return null;
                }
                return '/information-sheets/' + infoSheetId + '/document-assessments/' + id;
            },
            function(btn, id) {
                return function(response) {
                    const el = getClosest(btn, '.list-group-item');
                    fadeOutAndRemove(el, 300, function() {
                        showAlert('Document assessment deleted successfully!', 'success');
                    });
                };
            }
        );

        // Delete Topic
        setupDeleteHandler('.delete-topic-btn',
            function(btn) {
                return getDataAttr(btn, 'topic-id');
            },
            function(btn, id) {
                const topicName = getDataAttr(btn, 'topic-name');
                return 'Are you sure you want to delete the topic "' + topicName + '"?';
            },
            function(btn, id) {
                const infoSheetId = getDataAttr(btn, 'info-sheet-id');
                if (!infoSheetId) {
                    console.error('Missing info-sheet-id for topic delete');
                    return null;
                }
                return '/topics/' + id;
            },
            function(btn, id) {
                return function(response) {
                    const el = getClosest(btn, '.list-group-item');
                    fadeOutAndRemove(el, 300, function() {
                        showAlert('Topic deleted successfully!', 'success');
                    });
                };
            }
        );

        // Confirm Delete Button Handler
        if (confirmDeleteBtn) {
            confirmDeleteBtn.addEventListener('click', function() {
                if (!currentDeleteUrl || currentDeleteUrl === '' || currentDeleteUrl === '/content-management') {
                    showAlert('Error: No delete URL set. Please refresh the page and try again.', 'error');
                    return;
                }

                const button = confirmDeleteBtn;
                setButtonLoading(button, true);

                // Create the URL explicitly to avoid any potential mutation
                const deleteUrl = String(currentDeleteUrl);

                // Create a Request object to debug what's being sent
                const fetchUrl = deleteUrl;
                const fetchOptions = {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    redirect: 'manual'
                };

                // Try creating a Request object to see what URL it resolves to
                try {
                    const testRequest = new Request(fetchUrl, fetchOptions);
                } catch (e) {
                    console.error('Error creating Request object:', e);
                    showAlert('Error: Invalid delete URL. Please refresh the page and try again.', 'error');
                    setButtonLoading(button, false);
                    return;
                }

                fetch(fetchUrl, fetchOptions)
                    .then(function(response) {
                        if (!response.ok) {
                            return response.json().then(function(err) {
                                throw err;
                            });
                        }
                        return response.json();
                    })
                    .then(function(response) {
                        deleteModal.hide();
                        setButtonLoading(button, false);
                        if (currentDeleteCallback) {
                            currentDeleteCallback(response);
                        }
                    })
                    .catch(function(error) {
                        deleteModal.hide();
                        setButtonLoading(button, false);

                        let errorMessage = 'Failed to delete. Please try again.';
                        if (error && error.message) {
                            errorMessage = error.message;
                        }

                        showAlert(errorMessage, 'error');
                    });
            });
        }

        // Reset modal when hidden
        if (deleteConfirmModal) {
            deleteConfirmModal.addEventListener('hidden.bs.modal', function() {
                if (confirmDeleteBtn) {
                    setButtonLoading(confirmDeleteBtn, false);
                }
            });
        }

        // Function to show alert messages
        function showAlert(message, type) {
            const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
            const alertHtml = document.createElement('div');
            alertHtml.className = 'alert ' + alertClass + ' alert-dismissible fade show';
            alertHtml.setAttribute('role', 'alert');
            alertHtml.innerHTML = message + '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';

            const container = document.querySelector('.container-fluid .row .col-12');
            if (container) {
                container.insertBefore(alertHtml, container.firstChild);
            }

            setTimeout(function() {
                fadeOutAndRemove(alertHtml, 300);
            }, 5000);
        }

        // Function to check if no courses are left
        // Pre-resolve the route in Blade (safe, outside the JS string)
        const CREATE_COURSE_URL = "{{ route('courses.create') }}";

        function checkIfNoCourses() {
            const accordion = document.getElementById('coursesAccordion');
            if (accordion && accordion.querySelectorAll('.accordion-item').length === 0) {
                const container = document.querySelector('.container-fluid .row .col-12');
                if (container) {
                    container.innerHTML = '<div class="text-center py-5">' +
                        '<i class="fas fa-graduation-cap fa-3x text-muted mb-3"></i>' +
                        '<h4>No Courses Available</h4>' +
                        '<p class="text-muted">No learning courses have been created yet.</p>' +
                        '<a href="' + CREATE_COURSE_URL + '" class="btn btn-primary">' +
                        '<i class="fas fa-plus me-2"></i>Create First Course</a>' +
                        '</div>';
                }
            }
        }
    })();
</script>
@endpush
@endsection
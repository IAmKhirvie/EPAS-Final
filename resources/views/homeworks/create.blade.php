@extends('layouts.app')

@section('title', 'Create Homework')

@section('content')
<div class="content-area">
    <x-breadcrumb :items="[
        ['label' => 'Content', 'url' => route('content.management')],
        ['label' => $informationSheet->module->module_name, 'url' => route('courses.modules.show', [$informationSheet->module->course_id, $informationSheet->module])],
        ['label' => 'Create Homework'],
    ]" />

    <form action="{{ route('homeworks.store', $informationSheet) }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="cb-builder-layout">
            {{-- MAIN CONTENT --}}
            <div class="cb-main">
                <div class="cb-header cb-header--homework">
                    <h4><i class="fas fa-book-open me-2"></i>Create Homework</h4>
                    <p>For: {{ $informationSheet->title }}</p>
                </div>

                <div class="cb-body">
                    {{-- Compact Settings --}}
                    <div class="cb-settings">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label class="cb-field-label">Homework Number <span class="required">*</span></label>
                                <input type="text" class="form-control @error('homework_number') is-invalid @enderror" name="homework_number" value="{{ old('homework_number') }}" placeholder="e.g., HW-1.1" required>
                                @error('homework_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-5 mb-3">
                                <label class="cb-field-label">Title <span class="required">*</span></label>
                                <input type="text" class="form-control @error('title') is-invalid @enderror" name="title" value="{{ old('title') }}" required>
                                @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-2 mb-3">
                                <label class="cb-field-label">Max Points <span class="required">*</span></label>
                                <input type="number" class="form-control @error('max_points') is-invalid @enderror" name="max_points" value="{{ old('max_points', 100) }}" min="1" required>
                                @error('max_points')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-2 mb-3">
                                <label class="cb-field-label">Due Date <span class="required">*</span></label>
                                <input type="datetime-local" class="form-control @error('due_date') is-invalid @enderror" name="due_date" value="{{ old('due_date') }}" required>
                                @error('due_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="cb-field-label">Description <span class="optional">(optional)</span></label>
                                <textarea class="form-control @error('description') is-invalid @enderror" name="description" rows="2">{{ old('description') }}</textarea>
                                @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="cb-field-label">Instructions <span class="required">*</span></label>
                                <textarea class="form-control @error('instructions') is-invalid @enderror" name="instructions" rows="2" required>{{ old('instructions') }}</textarea>
                                @error('instructions')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    {{-- Requirements & Guidelines side by side --}}
                    <div class="cb-detail-row">
                        <div class="cb-detail-col">
                            <div class="cb-section__title"><i class="fas fa-list-check"></i> Requirements</div>
                            <div id="requirements-container">
                                <div class="cb-list-item">
                                    <i class="fas fa-grip-vertical cb-list-item__handle"></i>
                                    <input type="text" class="form-control form-control-sm" name="requirements[]" placeholder="Enter requirement" required>
                                    <button type="button" class="cb-list-item__remove" onclick="DynamicForm.removeListItem(this)"><i class="fas fa-times"></i></button>
                                </div>
                            </div>
                            <button type="button" class="cb-add-btn mt-1" onclick="DynamicForm.addListItem('requirements-container', 'requirements[]', 'Enter requirement', true)">
                                <i class="fas fa-plus"></i> Add Requirement
                            </button>
                        </div>
                        <div class="cb-detail-col">
                            <div class="cb-section__title"><i class="fas fa-file-lines"></i> Submission Guidelines</div>
                            <div id="guidelines-container">
                                <div class="cb-list-item">
                                    <i class="fas fa-grip-vertical cb-list-item__handle"></i>
                                    <input type="text" class="form-control form-control-sm" name="submission_guidelines[]" placeholder="Enter guideline" required>
                                    <button type="button" class="cb-list-item__remove" onclick="DynamicForm.removeListItem(this)"><i class="fas fa-times"></i></button>
                                </div>
                            </div>
                            <button type="button" class="cb-add-btn mt-1" onclick="DynamicForm.addListItem('guidelines-container', 'submission_guidelines[]', 'Enter guideline', true)">
                                <i class="fas fa-plus"></i> Add Guideline
                            </button>
                        </div>
                    </div>

                    {{-- Reference Images --}}
                    <div class="cb-section">
                        <div class="cb-section__title"><i class="fas fa-images"></i> Reference Images <span class="optional">(optional)</span></div>
                        <label class="cb-upload-area">
                            <input type="file" class="d-none" name="reference_images[]" accept="image/*" multiple onchange="this.closest('.cb-upload-area').classList.add('has-file'); this.closest('.cb-upload-area').querySelector('.upload-name').textContent = this.files.length + ' file(s) selected';">
                            <i class="fas fa-cloud-upload-alt d-block"></i>
                            <div class="cb-upload-area__text">
                                <strong>Click to upload</strong> images<br>
                                <small>You can upload multiple images</small>
                            </div>
                            <span class="upload-name" style="color: #388e3c; font-weight: 600; font-size: 0.8rem;"></span>
                        </label>
                        @error('reference_images')<div class="text-danger mt-1" style="font-size: 0.85rem;">{{ $message }}</div>@enderror
                    </div>
                </div>

                {{-- Footer --}}
                <div class="cb-footer">
                    <a href="{{ route('information-sheets.show', ['module' => $informationSheet->module_id, 'informationSheet' => $informationSheet->id]) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Cancel
                    </a>
                    <div class="btn-group-footer">
                        <span class="cb-footer__hint d-none d-md-inline">All fields marked * are required</span>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Create Homework
                        </button>
                    </div>
                </div>
            </div>

            {{-- RIGHT SIDEBAR --}}
            <div class="cb-sidebar">
                <div class="cb-sidebar__title">
                    <i class="fas fa-plus-circle me-2"></i>Quick Add
                </div>

                <div class="cb-sidebar__group">
                    <div class="cb-sidebar__group-label"><i class="fas fa-bolt"></i> Requirement Templates</div>

                    <button type="button" class="cb-sidebar__item" onclick="addRequirementTemplate('report')">
                        <div class="cb-sidebar__item-icon cb-sidebar__item-icon--blue"><i class="fas fa-file-alt"></i></div>
                        <div class="cb-sidebar__item-text">
                            <span class="cb-sidebar__item-name">Written Report</span>
                            <span class="cb-sidebar__item-desc">Report, essay, analysis</span>
                        </div>
                    </button>

                    <button type="button" class="cb-sidebar__item" onclick="addRequirementTemplate('practical')">
                        <div class="cb-sidebar__item-icon cb-sidebar__item-icon--green"><i class="fas fa-tools"></i></div>
                        <div class="cb-sidebar__item-text">
                            <span class="cb-sidebar__item-name">Practical Work</span>
                            <span class="cb-sidebar__item-desc">Hands-on project</span>
                        </div>
                    </button>

                    <button type="button" class="cb-sidebar__item" onclick="addRequirementTemplate('research')">
                        <div class="cb-sidebar__item-icon cb-sidebar__item-icon--orange"><i class="fas fa-search"></i></div>
                        <div class="cb-sidebar__item-text">
                            <span class="cb-sidebar__item-name">Research</span>
                            <span class="cb-sidebar__item-desc">Research & documentation</span>
                        </div>
                    </button>

                    <button type="button" class="cb-sidebar__item" onclick="addRequirementTemplate('diagram')">
                        <div class="cb-sidebar__item-icon cb-sidebar__item-icon--purple"><i class="fas fa-project-diagram"></i></div>
                        <div class="cb-sidebar__item-text">
                            <span class="cb-sidebar__item-name">Diagram/Drawing</span>
                            <span class="cb-sidebar__item-desc">Schematic or layout</span>
                        </div>
                    </button>
                </div>

                <div class="cb-sidebar__info">
                    <div class="cb-sidebar__info-title"><i class="fas fa-lightbulb"></i> Tips</div>
                    Set clear deadlines and point values. Detailed requirements help students understand what's expected.
                </div>
            </div>
        </div>
    </form>
</div>

<script>
const requirementTemplates = {
    report: {
        requirements: ['Submit a typed written report (minimum 2 pages)', 'Include introduction, body, and conclusion', 'Cite all references used'],
        guidelines: ['Submit as PDF or DOCX format', 'Use 12pt font, double-spaced', 'Include your name and section on the cover page']
    },
    practical: {
        requirements: ['Complete the practical exercise as demonstrated', 'Document your process with photos', 'Submit a brief summary of results'],
        guidelines: ['Take clear photos of each major step', 'Submit all files in a ZIP archive', 'Label all photos with step numbers']
    },
    research: {
        requirements: ['Research the assigned topic thoroughly', 'Provide at least 3 credible sources', 'Write a summary of findings'],
        guidelines: ['Use academic or industry sources', 'Proper citation format required', 'Submit as PDF document']
    },
    diagram: {
        requirements: ['Draw the required schematic/layout diagram', 'Label all components clearly', 'Include a legend or key'],
        guidelines: ['Use proper symbols and conventions', 'Submit as image (PNG/JPG) or PDF', 'Ensure diagram is legible and clean']
    }
};

function addRequirementTemplate(type) {
    const t = requirementTemplates[type];
    t.requirements.forEach(req => {
        DynamicForm.addListItem('requirements-container', 'requirements[]', 'Enter requirement', true);
        const items = document.querySelectorAll('#requirements-container .cb-list-item');
        const lastInput = items[items.length - 1].querySelector('input');
        if (lastInput) lastInput.value = req;
    });
    t.guidelines.forEach(guide => {
        DynamicForm.addListItem('guidelines-container', 'submission_guidelines[]', 'Enter guideline', true);
        const items = document.querySelectorAll('#guidelines-container .cb-list-item');
        const lastInput = items[items.length - 1].querySelector('input');
        if (lastInput) lastInput.value = guide;
    });
}
</script>
@endsection

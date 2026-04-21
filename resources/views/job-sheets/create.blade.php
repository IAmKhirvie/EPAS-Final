@extends('layouts.app')

@section('title', 'Create Job Sheet')

@section('content')
<div class="content-area">
    <x-breadcrumb :items="[
        ['label' => 'Content', 'url' => route('content.management')],
        ['label' => $informationSheet->module->module_name, 'url' => route('courses.modules.show', [$informationSheet->module->course_id, $informationSheet->module])],
        ['label' => 'Create Job Sheet'],
    ]" />

    <form action="{{ route('job-sheets.store', $informationSheet) }}" method="POST" enctype="multipart/form-data" id="jobSheetForm" class="cb-builder-layout-form">
        @csrf

        <div class="cb-builder-layout">
            {{-- MAIN CONTENT --}}
            <div class="cb-main">
                <div class="cb-header cb-header--job">
                    <h4><i class="fas fa-hard-hat me-2"></i>Create Job Sheet</h4>
                    <p>For: {{ $informationSheet->title }}</p>
                </div>

                <div class="cb-body">
                    {{-- Compact Settings --}}
                    <div class="cb-settings">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="cb-field-label">Job Number <span class="required">*</span></label>
                                <input type="text" class="form-control @error('job_number') is-invalid @enderror" name="job_number" value="{{ old('job_number') }}" placeholder="e.g., JS-1.1" required>
                                @error('job_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-8 mb-3">
                                <label class="cb-field-label">Title <span class="required">*</span></label>
                                <input type="text" class="form-control @error('title') is-invalid @enderror" name="title" value="{{ old('title') }}" required>
                                @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12 mb-3">
                                <label class="cb-field-label">Description <span class="optional">(optional)</span></label>
                                <textarea class="form-control @error('description') is-invalid @enderror" name="description" rows="2">{{ old('description') }}</textarea>
                                @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="cb-field-label d-block"><i class="fas fa-random text-primary me-1"></i> Randomization</label>
                                <div class="form-check mt-2">
                                    <input type="checkbox" class="form-check-input" name="randomize_steps" id="randomize_steps" value="1"
                                           {{ old('randomize_steps') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="randomize_steps">Randomize steps order</label>
                                </div>
                                <small class="text-muted">Each student sees steps in a different order</small>
                            </div>
                        </div>
                    </div>

                    {{-- Document Upload --}}
                    <div class="cb-section">
                        <div class="cb-section__title"><i class="fas fa-upload"></i> Document Attachment <span class="optional">(optional)</span></div>
                        <label class="cb-upload-area">
                            <input type="file" class="d-none" name="file"
                                   accept=".pdf,.xlsx,.xls,.doc,.docx,.ppt,.pptx"
                                   onchange="this.closest('.cb-upload-area').classList.add('has-file'); this.closest('.cb-upload-area').querySelector('.upload-name').textContent = this.files[0].name;">
                            <i class="fas fa-cloud-upload-alt d-block"></i>
                            <div class="cb-upload-area__text">
                                <strong>Click to upload</strong> or drag and drop<br>
                                <small>PDF, Word, Excel, PowerPoint (max 10MB)</small>
                            </div>
                            <span class="upload-name"></span>
                        </label>
                        @error('file')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>

                    {{-- Objectives, Tools, Safety, References in compact row --}}
                    <div class="cb-detail-row">
                        <div class="cb-detail-col">
                            <div class="cb-section__title"><i class="fas fa-bullseye"></i> Objectives</div>
                            <div id="objectives-container">
                                <div class="cb-list-item">
                                    <i class="fas fa-grip-vertical cb-list-item__handle"></i>
                                    <input type="text" class="form-control form-control-sm" name="objectives[]" placeholder="Enter objective" required>
                                    <button type="button" class="cb-list-item__remove" onclick="DynamicForm.removeListItem(this)"><i class="fas fa-times"></i></button>
                                </div>
                            </div>
                            <button type="button" class="cb-add-btn mt-1" onclick="DynamicForm.addListItem('objectives-container', 'objectives[]', 'Enter objective', true)">
                                <i class="fas fa-plus"></i> Add
                            </button>
                        </div>
                        <div class="cb-detail-col">
                            <div class="cb-section__title"><i class="fas fa-wrench"></i> Tools Required</div>
                            <div id="tools-container">
                                <div class="cb-list-item">
                                    <i class="fas fa-grip-vertical cb-list-item__handle"></i>
                                    <input type="text" class="form-control form-control-sm" name="tools_required[]" placeholder="Enter tool" required>
                                    <button type="button" class="cb-list-item__remove" onclick="DynamicForm.removeListItem(this)"><i class="fas fa-times"></i></button>
                                </div>
                            </div>
                            <button type="button" class="cb-add-btn mt-1" onclick="DynamicForm.addListItem('tools-container', 'tools_required[]', 'Enter tool', true)">
                                <i class="fas fa-plus"></i> Add
                            </button>
                        </div>
                        <div class="cb-detail-col">
                            <div class="cb-section__title"><i class="fas fa-shield-alt"></i> Safety</div>
                            <div id="safety-container">
                                <div class="cb-list-item">
                                    <i class="fas fa-grip-vertical cb-list-item__handle"></i>
                                    <input type="text" class="form-control form-control-sm" name="safety_requirements[]" placeholder="Enter requirement" required>
                                    <button type="button" class="cb-list-item__remove" onclick="DynamicForm.removeListItem(this)"><i class="fas fa-times"></i></button>
                                </div>
                            </div>
                            <button type="button" class="cb-add-btn mt-1" onclick="DynamicForm.addListItem('safety-container', 'safety_requirements[]', 'Enter requirement', true)">
                                <i class="fas fa-plus"></i> Add
                            </button>
                        </div>
                        <div class="cb-detail-col">
                            <div class="cb-section__title"><i class="fas fa-book"></i> References</div>
                            <div id="references-container">
                                <div class="cb-list-item">
                                    <i class="fas fa-grip-vertical cb-list-item__handle"></i>
                                    <input type="text" class="form-control form-control-sm" name="reference_materials[]" placeholder="Enter reference">
                                    <button type="button" class="cb-list-item__remove" onclick="DynamicForm.removeListItem(this)"><i class="fas fa-times"></i></button>
                                </div>
                            </div>
                            <button type="button" class="cb-add-btn mt-1" onclick="DynamicForm.addListItem('references-container', 'reference_materials[]', 'Enter reference')">
                                <i class="fas fa-plus"></i> Add
                            </button>
                        </div>
                    </div>

                    {{-- Job Steps --}}
                    <div class="cb-items-header">
                        <h5>
                            <i class="fas fa-list-ol text-primary me-2"></i>
                            Job Steps
                            <span class="cb-count-badge" id="step-count">0</span>
                        </h5>
                    </div>

                    <div id="steps-container">
                        <div class="cb-empty-state" id="empty-state">
                            <i class="fas fa-mouse-pointer d-block"></i>
                            <p><strong>No steps yet</strong><br>Click a template from the right panel or add a blank step</p>
                        </div>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="cb-footer">
                    <a href="{{ route('information-sheets.show', ['module' => $informationSheet->module_id, 'informationSheet' => $informationSheet->id]) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Cancel
                    </a>
                    <div class="btn-group-footer">
                        <small class="text-muted" id="save-hint">Add at least one step to enable saving</small>
                        <button type="submit" class="btn btn-primary" id="save-btn" disabled>
                            <i class="fas fa-save me-1"></i>Create Job Sheet
                        </button>
                    </div>
                </div>
            </div>

            {{-- RIGHT SIDEBAR --}}
            <div class="cb-sidebar">
                <div class="cb-sidebar__title">
                    <i class="fas fa-plus-circle me-2"></i>Add Job Step
                </div>

                <div class="cb-sidebar__group">
                    <div class="cb-sidebar__group-label"><i class="fas fa-bolt"></i> Quick Templates</div>

                    <button type="button" class="cb-sidebar__item" onclick="addTemplateStep('preparation')">
                        <div class="cb-sidebar__item-icon cb-sidebar__item-icon--blue"><i class="fas fa-clipboard-check"></i></div>
                        <div class="cb-sidebar__item-text">
                            <span class="cb-sidebar__item-name">Preparation</span>
                            <span class="cb-sidebar__item-desc">Setup & workspace ready</span>
                        </div>
                    </button>

                    <button type="button" class="cb-sidebar__item" onclick="addTemplateStep('assembly')">
                        <div class="cb-sidebar__item-icon cb-sidebar__item-icon--green"><i class="fas fa-cogs"></i></div>
                        <div class="cb-sidebar__item-text">
                            <span class="cb-sidebar__item-name">Assembly</span>
                            <span class="cb-sidebar__item-desc">Build & connect parts</span>
                        </div>
                    </button>

                    <button type="button" class="cb-sidebar__item" onclick="addTemplateStep('testing')">
                        <div class="cb-sidebar__item-icon cb-sidebar__item-icon--orange"><i class="fas fa-vial"></i></div>
                        <div class="cb-sidebar__item-text">
                            <span class="cb-sidebar__item-name">Testing</span>
                            <span class="cb-sidebar__item-desc">Verify & validate results</span>
                        </div>
                    </button>

                    <button type="button" class="cb-sidebar__item" onclick="addTemplateStep('cleanup')">
                        <div class="cb-sidebar__item-icon cb-sidebar__item-icon--red"><i class="fas fa-broom"></i></div>
                        <div class="cb-sidebar__item-text">
                            <span class="cb-sidebar__item-name">Cleanup</span>
                            <span class="cb-sidebar__item-desc">Organize & store tools</span>
                        </div>
                    </button>
                </div>

                <div class="cb-sidebar__group">
                    <div class="cb-sidebar__group-label"><i class="fas fa-plus"></i> Manual</div>
                    <button type="button" class="cb-sidebar__item" onclick="addStep()">
                        <div class="cb-sidebar__item-icon"><i class="fas fa-plus"></i></div>
                        <div class="cb-sidebar__item-text">
                            <span class="cb-sidebar__item-name">Blank Step</span>
                            <span class="cb-sidebar__item-desc">Add empty step</span>
                        </div>
                    </button>
                </div>

                <div class="cb-sidebar__info">
                    <div class="cb-sidebar__info-title"><i class="fas fa-lightbulb"></i> Tips</div>
                    Each step should have clear instructions and an expected outcome so students know what to aim for.
                </div>
            </div>
        </div>
    </form>
</div>

<script>
let stepCount = 0;

const stepTemplates = {
    preparation: { instruction: 'Prepare the workspace and gather all required tools and materials.', outcome: 'Workspace is clean, organized, and all materials are ready.' },
    assembly:    { instruction: 'Assemble the components following the schematic diagram.', outcome: 'All components are properly connected and secured.' },
    testing:     { instruction: 'Perform functional testing of the assembled circuit/system.', outcome: 'System operates within specified parameters.' },
    cleanup:     { instruction: 'Clean the workspace and return all tools to proper storage.', outcome: 'Workspace is clean and all tools are accounted for.' }
};

function addStep(instruction = '', outcome = '') {
    const container = document.getElementById('steps-container');
    const emptyState = document.getElementById('empty-state');
    if (emptyState) emptyState.remove();

    const card = document.createElement('div');
    card.className = 'cb-item-card step-card';
    card.innerHTML = `
        <div class="cb-item-card__header">
            <div class="left-section">
                <span class="cb-item-card__number">${stepCount + 1}</span>
                <span class="cb-item-card__title">Step #${stepCount + 1}</span>
            </div>
            <div class="right-section">
                <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeStep(this)">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </div>
        </div>
        <div class="cb-item-card__body">
            <input type="hidden" name="steps[${stepCount}][step_number]" value="${stepCount + 1}">
            <div class="mb-3">
                <label class="cb-field-label">Instruction <span class="required">*</span></label>
                <textarea class="form-control" name="steps[${stepCount}][instruction]" rows="2" required>${instruction}</textarea>
            </div>
            <div class="mb-3">
                <label class="cb-field-label">Expected Outcome <span class="required">*</span></label>
                <textarea class="form-control" name="steps[${stepCount}][expected_outcome]" rows="2" required>${outcome}</textarea>
            </div>
            <div>
                <label class="cb-field-label">Step Image <span class="optional">(optional)</span></label>
                <input type="file" class="form-control form-control-sm" name="steps[${stepCount}][image]" accept="image/*">
            </div>
        </div>
    `;
    container.appendChild(card);
    stepCount++;
    updateUI();
}

function addTemplateStep(type) {
    const t = stepTemplates[type];
    addStep(t.instruction, t.outcome);
}

function removeStep(btn) {
    btn.closest('.step-card').remove();
    renumberSteps();
    updateUI();
}

function renumberSteps() {
    document.querySelectorAll('#steps-container .step-card').forEach((card, i) => {
        card.querySelector('.cb-item-card__number').textContent = i + 1;
        card.querySelector('.cb-item-card__title').textContent = `Step #${i + 1}`;
    });
}

function updateUI() {
    const count = document.querySelectorAll('#steps-container .step-card').length;
    document.getElementById('step-count').textContent = count;
    document.getElementById('save-btn').disabled = count === 0;
    document.getElementById('save-hint').textContent = count === 0
        ? 'Add at least one step to enable saving'
        : `${count} step${count > 1 ? 's' : ''} ready`;

    if (count === 0) {
        const container = document.getElementById('steps-container');
        if (!document.getElementById('empty-state')) {
            container.innerHTML = `
                <div class="cb-empty-state" id="empty-state">
                    <i class="fas fa-mouse-pointer d-block"></i>
                    <p><strong>No steps yet</strong><br>Click a template from the right panel or add a blank step</p>
                </div>`;
        }
    }
}
</script>
@endsection

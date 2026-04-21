@extends('layouts.app')

@section('title', 'Create Task Sheet')

@section('content')
<div class="content-area">
    <x-breadcrumb :items="[
        ['label' => 'Content', 'url' => route('content.management')],
        ['label' => $informationSheet->module->module_name, 'url' => route('courses.modules.show', [$informationSheet->module->course_id, $informationSheet->module])],
        ['label' => 'Create Task Sheet'],
    ]" />

    <form action="{{ route('task-sheets.store', $informationSheet) }}" method="POST" enctype="multipart/form-data" id="taskSheetForm" class="cb-builder-layout-form">
        @csrf

        <div class="cb-builder-layout">
            {{-- MAIN CONTENT --}}
            <div class="cb-main">
                <div class="cb-header cb-header--task">
                    <h4><i class="fas fa-clipboard-list me-2"></i>Create Task Sheet</h4>
                    <p>For: {{ $informationSheet->title }}</p>
                </div>

                <div class="cb-body">
                    {{-- Compact Settings --}}
                    <div class="cb-settings">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label class="cb-field-label">Task Number <span class="required">*</span></label>
                                <input type="text" class="form-control @error('task_number') is-invalid @enderror" name="task_number" value="{{ old('task_number') }}" placeholder="e.g., TS-1.1" required>
                                @error('task_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-5 mb-3">
                                <label class="cb-field-label">Title <span class="required">*</span></label>
                                <input type="text" class="form-control @error('title') is-invalid @enderror" name="title" value="{{ old('title') }}" required>
                                @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="cb-field-label">Reference Image <span class="optional">(optional)</span></label>
                                <input type="file" class="form-control form-control-sm @error('image') is-invalid @enderror" name="image" accept="image/*">
                                @error('image')<div class="invalid-feedback">{{ $message }}</div>@enderror
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
                            <div class="col-md-6 mb-3">
                                <label class="cb-field-label d-block"><i class="fas fa-random text-primary me-1"></i> Randomization</label>
                                <div class="form-check mt-2">
                                    <input type="checkbox" class="form-check-input" name="randomize_items" id="randomize_items" value="1"
                                           {{ old('randomize_items') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="randomize_items">Randomize task items order</label>
                                </div>
                                <small class="text-muted">Each student sees items in a different order</small>
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

                    {{-- Objectives, Materials, Safety in compact row --}}
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
                                <i class="fas fa-plus"></i> Add Objective
                            </button>
                        </div>
                        <div class="cb-detail-col">
                            <div class="cb-section__title"><i class="fas fa-tools"></i> Materials</div>
                            <div id="materials-container">
                                <div class="cb-list-item">
                                    <i class="fas fa-grip-vertical cb-list-item__handle"></i>
                                    <input type="text" class="form-control form-control-sm" name="materials[]" placeholder="Enter material" required>
                                    <button type="button" class="cb-list-item__remove" onclick="DynamicForm.removeListItem(this)"><i class="fas fa-times"></i></button>
                                </div>
                            </div>
                            <button type="button" class="cb-add-btn mt-1" onclick="DynamicForm.addListItem('materials-container', 'materials[]', 'Enter material', true)">
                                <i class="fas fa-plus"></i> Add Material
                            </button>
                        </div>
                        <div class="cb-detail-col">
                            <div class="cb-section__title"><i class="fas fa-shield-alt"></i> Safety</div>
                            <div id="safety-container">
                                <div class="cb-list-item">
                                    <i class="fas fa-grip-vertical cb-list-item__handle"></i>
                                    <input type="text" class="form-control form-control-sm" name="safety_precautions[]" placeholder="Enter precaution">
                                    <button type="button" class="cb-list-item__remove" onclick="DynamicForm.removeListItem(this)"><i class="fas fa-times"></i></button>
                                </div>
                            </div>
                            <button type="button" class="cb-add-btn mt-1" onclick="DynamicForm.addListItem('safety-container', 'safety_precautions[]', 'Enter precaution')">
                                <i class="fas fa-plus"></i> Add Precaution
                            </button>
                        </div>
                    </div>

                    {{-- Task Items --}}
                    <div class="cb-items-header">
                        <h5>
                            <i class="fas fa-list-check text-primary me-2"></i>
                            Task Items
                            <span class="cb-count-badge" id="item-count">0</span>
                        </h5>
                    </div>

                    <div id="items-container">
                        <div class="cb-empty-state" id="empty-state">
                            <i class="fas fa-mouse-pointer d-block"></i>
                            <p><strong>No task items yet</strong><br>Click a template from the right panel or add a blank item</p>
                        </div>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="cb-footer">
                    <a href="{{ route('information-sheets.show', ['module' => $informationSheet->module_id, 'informationSheet' => $informationSheet->id]) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Cancel
                    </a>
                    <div class="btn-group-footer">
                        <small class="text-muted" id="save-hint">Add at least one task item to enable saving</small>
                        <button type="submit" class="btn btn-primary" id="save-btn" disabled>
                            <i class="fas fa-save me-1"></i>Create Task Sheet
                        </button>
                    </div>
                </div>
            </div>

            {{-- RIGHT SIDEBAR --}}
            <div class="cb-sidebar">
                <div class="cb-sidebar__title">
                    <i class="fas fa-plus-circle me-2"></i>Add Task Item
                </div>

                <div class="cb-sidebar__group">
                    <div class="cb-sidebar__group-label"><i class="fas fa-bolt"></i> Quick Templates</div>

                    <button type="button" class="cb-sidebar__item" onclick="addTemplateItem('measurement')">
                        <div class="cb-sidebar__item-icon cb-sidebar__item-icon--blue"><i class="fas fa-ruler"></i></div>
                        <div class="cb-sidebar__item-text">
                            <span class="cb-sidebar__item-name">Measurement</span>
                            <span class="cb-sidebar__item-desc">Measure & record values</span>
                        </div>
                    </button>

                    <button type="button" class="cb-sidebar__item" onclick="addTemplateItem('inspection')">
                        <div class="cb-sidebar__item-icon cb-sidebar__item-icon--green"><i class="fas fa-search"></i></div>
                        <div class="cb-sidebar__item-text">
                            <span class="cb-sidebar__item-name">Inspection</span>
                            <span class="cb-sidebar__item-desc">Visual check & condition</span>
                        </div>
                    </button>

                    <button type="button" class="cb-sidebar__item" onclick="addTemplateItem('testing')">
                        <div class="cb-sidebar__item-icon cb-sidebar__item-icon--orange"><i class="fas fa-vial"></i></div>
                        <div class="cb-sidebar__item-text">
                            <span class="cb-sidebar__item-name">Testing</span>
                            <span class="cb-sidebar__item-desc">Functional test & results</span>
                        </div>
                    </button>

                    <button type="button" class="cb-sidebar__item" onclick="addTemplateItem('assembly')">
                        <div class="cb-sidebar__item-icon cb-sidebar__item-icon--purple"><i class="fas fa-cogs"></i></div>
                        <div class="cb-sidebar__item-text">
                            <span class="cb-sidebar__item-name">Assembly</span>
                            <span class="cb-sidebar__item-desc">Assemble & verify parts</span>
                        </div>
                    </button>
                </div>

                <div class="cb-sidebar__group">
                    <div class="cb-sidebar__group-label"><i class="fas fa-plus"></i> Manual</div>
                    <button type="button" class="cb-sidebar__item" onclick="addTaskItem()">
                        <div class="cb-sidebar__item-icon"><i class="fas fa-plus"></i></div>
                        <div class="cb-sidebar__item-text">
                            <span class="cb-sidebar__item-name">Blank Item</span>
                            <span class="cb-sidebar__item-desc">Add empty task item</span>
                        </div>
                    </button>
                </div>

                <div class="cb-sidebar__info">
                    <div class="cb-sidebar__info-title"><i class="fas fa-lightbulb"></i> Tips</div>
                    Be specific with expected findings and acceptable ranges. Students will self-assess against these values.
                </div>
            </div>
        </div>
    </form>
</div>

<script>
let itemCount = 0;

const templates = {
    measurement: { part: 'Measurement Point', desc: 'Measure the specified component', expected: 'Within specification', range: '±5%' },
    inspection:  { part: 'Inspection Point', desc: 'Visually inspect for defects or damage', expected: 'No visible defects', range: 'Pass/Fail' },
    testing:     { part: 'Test Point', desc: 'Perform functional test', expected: 'Operates correctly', range: 'Pass/Fail' },
    assembly:    { part: 'Assembly Point', desc: 'Assemble components as instructed', expected: 'Properly assembled', range: 'Pass/Fail' }
};

function addTaskItem(partName = '', description = '', expected = '', range = '') {
    const container = document.getElementById('items-container');
    const emptyState = document.getElementById('empty-state');
    if (emptyState) emptyState.remove();

    const card = document.createElement('div');
    card.className = 'cb-item-card item-card';
    card.innerHTML = `
        <div class="cb-item-card__header">
            <div class="left-section">
                <span class="cb-item-card__number">${itemCount + 1}</span>
                <span class="cb-item-card__title">Item #${itemCount + 1}</span>
            </div>
            <div class="right-section">
                <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeItem(this)">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </div>
        </div>
        <div class="cb-item-card__body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="cb-field-label">Part Name <span class="required">*</span></label>
                    <input type="text" class="form-control" name="items[${itemCount}][part_name]" value="${partName}" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="cb-field-label">Description <span class="required">*</span></label>
                    <input type="text" class="form-control" name="items[${itemCount}][description]" value="${description}" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="cb-field-label">Expected Finding <span class="required">*</span></label>
                    <input type="text" class="form-control" name="items[${itemCount}][expected_finding]" value="${expected}" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="cb-field-label">Acceptable Range <span class="required">*</span></label>
                    <input type="text" class="form-control" name="items[${itemCount}][acceptable_range]" value="${range}" required>
                </div>
                <input type="hidden" name="items[${itemCount}][order]" value="${itemCount}">
            </div>
        </div>
    `;
    container.appendChild(card);
    itemCount++;
    updateUI();
}

function addTemplateItem(type) {
    const t = templates[type];
    addTaskItem(t.part, t.desc, t.expected, t.range);
}

function removeItem(btn) {
    btn.closest('.item-card').remove();
    renumberItems();
    updateUI();
}

function renumberItems() {
    document.querySelectorAll('#items-container .item-card').forEach((card, i) => {
        card.querySelector('.cb-item-card__number').textContent = i + 1;
        card.querySelector('.cb-item-card__title').textContent = `Item #${i + 1}`;
    });
}

function updateUI() {
    const count = document.querySelectorAll('#items-container .item-card').length;
    document.getElementById('item-count').textContent = count;
    document.getElementById('save-btn').disabled = count === 0;
    document.getElementById('save-hint').textContent = count === 0
        ? 'Add at least one task item to enable saving'
        : `${count} item${count > 1 ? 's' : ''} ready`;

    if (count === 0) {
        const container = document.getElementById('items-container');
        if (!document.getElementById('empty-state')) {
            container.innerHTML = `
                <div class="cb-empty-state" id="empty-state">
                    <i class="fas fa-mouse-pointer d-block"></i>
                    <p><strong>No task items yet</strong><br>Click a template from the right panel or add a blank item</p>
                </div>`;
        }
    }
}
</script>
@endsection

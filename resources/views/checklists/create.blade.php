@extends('layouts.app')

@section('title', 'Create Checklist')

@section('content')
<div class="content-area">
    <x-breadcrumb :items="[
        ['label' => 'Content', 'url' => route('content.management')],
        ['label' => $informationSheet->module->module_name, 'url' => route('courses.modules.show', [$informationSheet->module->course_id, $informationSheet->module])],
        ['label' => 'Create Checklist'],
    ]" />

    <form action="{{ route('checklists.store', $informationSheet) }}" method="POST" id="checklistForm">
        @csrf

        <div class="cb-builder-layout">
            {{-- MAIN CONTENT --}}
            <div class="cb-main">
                <div class="cb-header cb-header--checklist">
                    <h4><i class="fas fa-clipboard-check me-2"></i>Create Checklist</h4>
                    <p>For: {{ $informationSheet->title }}</p>
                </div>

                <div class="cb-body">
                    {{-- Compact Settings --}}
                    <div class="cb-settings">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label class="cb-field-label">Checklist Number <span class="required">*</span></label>
                                <input type="text" class="form-control @error('checklist_number') is-invalid @enderror" name="checklist_number" value="{{ old('checklist_number') }}" placeholder="e.g., CL-1.1" required>
                                @error('checklist_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="cb-field-label">Title <span class="required">*</span></label>
                                <input type="text" class="form-control @error('title') is-invalid @enderror" name="title" value="{{ old('title') }}" required>
                                @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="cb-field-label">Rating Scale</label>
                                <input type="text" class="form-control" value="1-5 (Poor → Excellent)" disabled>
                            </div>
                            <div class="col-12">
                                <label class="cb-field-label">Description <span class="optional">(optional)</span></label>
                                <textarea class="form-control @error('description') is-invalid @enderror" name="description" rows="2">{{ old('description') }}</textarea>
                                @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    {{-- Checklist Items --}}
                    <div class="cb-items-header">
                        <h5>
                            <i class="fas fa-clipboard-check text-primary me-2"></i>
                            Checklist Items
                            <span class="cb-count-badge" id="item-count">0</span>
                        </h5>
                    </div>

                    <div id="items-container">
                        <div class="cb-empty-state" id="empty-state">
                            <i class="fas fa-mouse-pointer d-block"></i>
                            <p><strong>No checklist items yet</strong><br>Click a template from the right panel or add a blank item</p>
                        </div>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="cb-footer">
                    <a href="{{ route('information-sheets.show', ['module' => $informationSheet->module_id, 'informationSheet' => $informationSheet->id]) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Cancel
                    </a>
                    <div class="btn-group-footer">
                        <small class="text-muted" id="save-hint">Add at least one item to enable saving</small>
                        <button type="submit" class="btn btn-primary" id="save-btn" disabled>
                            <i class="fas fa-save me-1"></i>Create Checklist
                        </button>
                    </div>
                </div>
            </div>

            {{-- RIGHT SIDEBAR --}}
            <div class="cb-sidebar">
                <div class="cb-sidebar__title">
                    <i class="fas fa-plus-circle me-2"></i>Add Items
                </div>

                <div class="cb-sidebar__group">
                    <div class="cb-sidebar__group-label"><i class="fas fa-bolt"></i> Quick Templates</div>

                    <button type="button" class="cb-sidebar__item" onclick="addCommonItems('safety')">
                        <div class="cb-sidebar__item-icon cb-sidebar__item-icon--red"><i class="fas fa-shield-alt"></i></div>
                        <div class="cb-sidebar__item-text">
                            <span class="cb-sidebar__item-name">Safety Checks</span>
                            <span class="cb-sidebar__item-desc">PPE, protocols, workspace</span>
                        </div>
                    </button>

                    <button type="button" class="cb-sidebar__item" onclick="addCommonItems('tools')">
                        <div class="cb-sidebar__item-icon cb-sidebar__item-icon--orange"><i class="fas fa-tools"></i></div>
                        <div class="cb-sidebar__item-text">
                            <span class="cb-sidebar__item-name">Tool Usage</span>
                            <span class="cb-sidebar__item-desc">Selection, handling, storage</span>
                        </div>
                    </button>

                    <button type="button" class="cb-sidebar__item" onclick="addCommonItems('quality')">
                        <div class="cb-sidebar__item-icon cb-sidebar__item-icon--green"><i class="fas fa-check-double"></i></div>
                        <div class="cb-sidebar__item-text">
                            <span class="cb-sidebar__item-name">Quality Checks</span>
                            <span class="cb-sidebar__item-desc">Standards, defects, function</span>
                        </div>
                    </button>

                    <button type="button" class="cb-sidebar__item" onclick="addCommonItems('procedure')">
                        <div class="cb-sidebar__item-icon cb-sidebar__item-icon--blue"><i class="fas fa-list-ol"></i></div>
                        <div class="cb-sidebar__item-text">
                            <span class="cb-sidebar__item-name">Procedure Checks</span>
                            <span class="cb-sidebar__item-desc">Steps, completeness</span>
                        </div>
                    </button>
                </div>

                <div class="cb-sidebar__group">
                    <div class="cb-sidebar__group-label"><i class="fas fa-plus"></i> Manual</div>
                    <button type="button" class="cb-sidebar__item" onclick="addChecklistItem()">
                        <div class="cb-sidebar__item-icon"><i class="fas fa-plus"></i></div>
                        <div class="cb-sidebar__item-text">
                            <span class="cb-sidebar__item-name">Blank Item</span>
                            <span class="cb-sidebar__item-desc">Add empty checklist item</span>
                        </div>
                    </button>
                </div>

                <div class="cb-sidebar__group">
                    <div class="cb-sidebar__group-label"><i class="fas fa-star"></i> Rating Scale</div>
                    <div class="cb-sidebar__info">
                        <div style="font-size: 0.8rem; line-height: 1.8;">
                            <strong>5</strong> - Excellent<br>
                            <strong>4</strong> - Good<br>
                            <strong>3</strong> - Average<br>
                            <strong>2</strong> - Below Average<br>
                            <strong>1</strong> - Poor
                        </div>
                    </div>
                </div>

                <div class="cb-sidebar__info">
                    <div class="cb-sidebar__info-title"><i class="fas fa-lightbulb"></i> Tips</div>
                    Each item should be specific and observable. Use clear language that students can understand.
                </div>
            </div>
        </div>
    </form>
</div>

<script>
let itemCount = 0;

const commonItems = {
    safety: [
        'Wore appropriate PPE (safety glasses, gloves, etc.)',
        'Followed safety protocols before starting work',
        'Maintained clean and organized workspace',
        'Properly handled hazardous materials'
    ],
    tools: [
        'Selected correct tools for the task',
        'Used tools properly and safely',
        'Returned tools to proper storage after use',
        'Reported damaged or missing tools'
    ],
    quality: [
        'Work meets quality standards',
        'Finished product functions correctly',
        'No visible defects or errors',
        'Proper connections and soldering'
    ],
    procedure: [
        'Followed the correct sequence of steps',
        'Read and understood instructions before starting',
        'Completed all required steps',
        'Asked questions when unsure'
    ]
};

function addChecklistItem(description = '') {
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
                <div class="col-md-8 mb-3">
                    <label class="cb-field-label">Description <span class="required">*</span></label>
                    <input type="text" class="form-control" name="items[${itemCount}][description]" value="${description}" placeholder="What needs to be checked" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="cb-field-label">Rating (1-5) <span class="required">*</span></label>
                    <select class="form-select" name="items[${itemCount}][rating]" required>
                        <option value="">Select rating...</option>
                        <option value="1">1 - Poor</option>
                        <option value="2">2 - Below Average</option>
                        <option value="3">3 - Average</option>
                        <option value="4">4 - Good</option>
                        <option value="5">5 - Excellent</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="cb-field-label">Remarks <span class="optional">(optional)</span></label>
                    <textarea class="form-control" name="items[${itemCount}][remarks]" rows="1" placeholder="Additional notes..."></textarea>
                </div>
            </div>
        </div>
    `;
    container.appendChild(card);
    itemCount++;
    updateUI();
}

function addCommonItems(type) {
    commonItems[type].forEach(desc => addChecklistItem(desc));
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
        ? 'Add at least one item to enable saving'
        : `${count} item${count > 1 ? 's' : ''} ready`;

    if (count === 0) {
        const container = document.getElementById('items-container');
        if (!document.getElementById('empty-state')) {
            container.innerHTML = `
                <div class="cb-empty-state" id="empty-state">
                    <i class="fas fa-mouse-pointer d-block"></i>
                    <p><strong>No checklist items yet</strong><br>Click a template from the right panel or add a blank item</p>
                </div>`;
        }
    }
}
</script>
@endsection

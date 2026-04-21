@extends('layouts.app')

@section('title', 'Edit Checklist')

@section('content')
<div class="content-area">
    <x-breadcrumb :items="[
        ['label' => 'Content', 'url' => route('content.management')],
        ['label' => $informationSheet->module->module_name, 'url' => route('courses.modules.show', [$informationSheet->module->course_id, $informationSheet->module])],
        ['label' => 'Edit Checklist'],
    ]" />

    @php $items = json_decode($checklist->items, true) ?? []; @endphp

    <form action="{{ route('checklists.update', [$informationSheet, $checklist]) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="cb-builder-layout">
            {{-- MAIN CONTENT --}}
            <div class="cb-main">
                <div class="cb-header cb-header--checklist">
                    <h4><i class="fas fa-edit me-2"></i>Edit Checklist</h4>
                    <p>{{ $checklist->checklist_number }} &mdash; {{ $informationSheet->title }}</p>
                </div>

                <div class="cb-body">
                    {{-- Compact Settings --}}
                    <div class="cb-settings">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label class="cb-field-label">Checklist Number <span class="required">*</span></label>
                                <input type="text" class="form-control @error('checklist_number') is-invalid @enderror" name="checklist_number" value="{{ old('checklist_number', $checklist->checklist_number) }}" required>
                                @error('checklist_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="cb-field-label">Title <span class="required">*</span></label>
                                <input type="text" class="form-control @error('title') is-invalid @enderror" name="title" value="{{ old('title', $checklist->title) }}" required>
                                @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="cb-field-label">Rating Scale</label>
                                <input type="text" class="form-control" value="1-5 (Poor → Excellent)" disabled>
                            </div>
                            <div class="col-12">
                                <label class="cb-field-label">Description <span class="optional">(optional)</span></label>
                                <textarea class="form-control @error('description') is-invalid @enderror" name="description" rows="2">{{ old('description', $checklist->description) }}</textarea>
                                @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    {{-- Checklist Items --}}
                    <div class="cb-items-header">
                        <h5>
                            <i class="fas fa-clipboard-check text-primary me-2"></i>
                            Checklist Items
                            <span class="cb-count-badge" id="item-count">{{ count($items) }}</span>
                        </h5>
                    </div>

                    <div id="items-container">
                        @foreach($items as $index => $item)
                        <div class="cb-item-card item-card">
                            <div class="cb-item-card__header">
                                <div class="left-section">
                                    <span class="cb-item-card__number">{{ $index + 1 }}</span>
                                    <span class="cb-item-card__title">Item #{{ $index + 1 }}</span>
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
                                        <input type="text" class="form-control" name="items[{{ $index }}][description]" value="{{ $item['description'] ?? '' }}" required>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="cb-field-label">Rating (1-5) <span class="required">*</span></label>
                                        <select class="form-select" name="items[{{ $index }}][rating]" required>
                                            <option value="">Select rating...</option>
                                            @for($i = 1; $i <= 5; $i++)
                                            <option value="{{ $i }}" {{ ($item['rating'] ?? '') == $i ? 'selected' : '' }}>
                                                {{ $i }} - {{ ['Poor', 'Below Average', 'Average', 'Good', 'Excellent'][$i-1] }}
                                            </option>
                                            @endfor
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <label class="cb-field-label">Remarks <span class="optional">(optional)</span></label>
                                        <textarea class="form-control" name="items[{{ $index }}][remarks]" rows="1">{{ $item['remarks'] ?? '' }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Footer --}}
                <div class="cb-footer">
                    <a href="{{ route('checklists.show', $checklist) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Cancel
                    </a>
                    <div class="btn-group-footer">
                        <small class="text-muted" id="save-hint">{{ count($items) }} item{{ count($items) > 1 ? 's' : '' }} ready</small>
                        <button type="submit" class="btn btn-primary" id="save-btn">
                            <i class="fas fa-save me-1"></i>Update Checklist
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
let itemCount = {{ count($items) }};

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
}
</script>
@endsection

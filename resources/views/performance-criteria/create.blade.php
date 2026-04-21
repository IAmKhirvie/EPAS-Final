@extends('layouts.app')

@section('title', 'Performance Criteria')

@section('content')
<div class="content-area">
    <x-breadcrumb :items="[
        ['label' => 'Content', 'url' => route('content.management')],
        ['label' => 'Performance Criteria'],
    ]" />

    <form action="{{ route('performance-criteria.store') }}" method="POST" id="performanceCriteriaForm">
        @csrf
        <input type="hidden" name="type" value="{{ $type }}">
        <input type="hidden" name="related_id" value="{{ $relatedId }}">

        <div class="cb-builder-layout">
            {{-- MAIN CONTENT --}}
            <div class="cb-main">
                <div class="cb-header cb-header--criteria">
                    <h4><i class="fas fa-clipboard-list me-2"></i>Performance Criteria Checklist</h4>
                    <p>Evaluate student performance with observable criteria</p>
                </div>

                <div class="cb-body">
                    @if($taskSheet)
                    <div class="cb-context-badge">
                        <i class="fas fa-clipboard-list"></i>
                        <span>Task Sheet: <strong>{{ $taskSheet->title }} ({{ $taskSheet->task_number }})</strong></span>
                    </div>
                    @elseif($jobSheet)
                    <div class="cb-context-badge">
                        <i class="fas fa-hard-hat"></i>
                        <span>Job Sheet: <strong>{{ $jobSheet->title }} ({{ $jobSheet->job_number }})</strong></span>
                    </div>
                    @endif

                    <div class="cb-items-header">
                        <h5>
                            <i class="fas fa-clipboard-list text-primary me-2"></i>
                            Evaluation Criteria
                            <span class="cb-count-badge" id="criteria-count">1</span>
                        </h5>
                    </div>

                    <div id="criteria-container">
                        <div class="cb-item-card criteria-card">
                            <div class="cb-item-card__header">
                                <div class="left-section">
                                    <span class="cb-item-card__number">1</span>
                                    <span class="cb-item-card__title">Criterion #1</span>
                                </div>
                                <div class="right-section">
                                    <button type="button" class="btn btn-outline-danger btn-sm" onclick="DynamicForm.removeItemCard(this, 'criteria-card', 'Criterion')">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="cb-item-card__body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="cb-field-label">Description <span class="required">*</span></label>
                                        <input type="text" class="form-control" name="criteria[0][description]" placeholder="What is being evaluated" required>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="cb-field-label">Observed <span class="required">*</span></label>
                                        <select class="form-select" name="criteria[0][observed]" required>
                                            <option value="">Select...</option>
                                            <option value="1">Yes</option>
                                            <option value="0">No</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="cb-field-label">Remarks <span class="optional">(optional)</span></label>
                                        <input type="text" class="form-control" name="criteria[0][remarks]" placeholder="Notes">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button type="button" class="cb-add-btn" onclick="addCriterion()">
                        <i class="fas fa-plus"></i> Add Criterion
                    </button>
                </div>

                <div class="cb-footer">
                    <a href="{{ route('content.management') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Cancel
                    </a>
                    <div class="btn-group-footer">
                        <span class="cb-footer__hint d-none d-md-inline">All fields marked * are required</span>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Submit Performance Criteria
                        </button>
                    </div>
                </div>
            </div>

            {{-- RIGHT SIDEBAR --}}
            <div class="cb-sidebar">
                <div class="cb-sidebar__title">Quick Add Templates</div>

                <div class="cb-sidebar__group">
                    <div class="cb-sidebar__group-label"><i class="fas fa-bolt"></i> Common Criteria</div>
                    <button type="button" class="cb-sidebar__item" onclick="addCommonCriteria('safety')">
                        <span class="cb-sidebar__item-icon cb-sidebar__item-icon--red"><i class="fas fa-shield-alt"></i></span>
                        <span class="cb-sidebar__item-text">
                            <span class="cb-sidebar__item-name">Safety Practices</span>
                            <span class="cb-sidebar__item-desc">PPE, protocols, workspace</span>
                        </span>
                    </button>
                    <button type="button" class="cb-sidebar__item" onclick="addCommonCriteria('tools')">
                        <span class="cb-sidebar__item-icon cb-sidebar__item-icon--orange"><i class="fas fa-tools"></i></span>
                        <span class="cb-sidebar__item-text">
                            <span class="cb-sidebar__item-name">Proper Tool Usage</span>
                            <span class="cb-sidebar__item-desc">Selection, handling, storage</span>
                        </span>
                    </button>
                    <button type="button" class="cb-sidebar__item" onclick="addCommonCriteria('procedure')">
                        <span class="cb-sidebar__item-icon cb-sidebar__item-icon--blue"><i class="fas fa-list-ol"></i></span>
                        <span class="cb-sidebar__item-text">
                            <span class="cb-sidebar__item-name">Correct Procedure</span>
                            <span class="cb-sidebar__item-desc">Steps, instructions, completeness</span>
                        </span>
                    </button>
                    <button type="button" class="cb-sidebar__item" onclick="addCommonCriteria('quality')">
                        <span class="cb-sidebar__item-icon cb-sidebar__item-icon--green"><i class="fas fa-check-double"></i></span>
                        <span class="cb-sidebar__item-text">
                            <span class="cb-sidebar__item-name">Quality of Work</span>
                            <span class="cb-sidebar__item-desc">Standards, defects, function</span>
                        </span>
                    </button>
                    <button type="button" class="cb-sidebar__item" onclick="addCommonCriteria('time')">
                        <span class="cb-sidebar__item-icon cb-sidebar__item-icon--teal"><i class="fas fa-clock"></i></span>
                        <span class="cb-sidebar__item-text">
                            <span class="cb-sidebar__item-name">Time Management</span>
                            <span class="cb-sidebar__item-desc">Deadlines, efficiency</span>
                        </span>
                    </button>
                </div>

                <div class="cb-sidebar__info">
                    <div class="cb-sidebar__info-title"><i class="fas fa-lightbulb"></i> Tips</div>
                    Click a template to add pre-defined criteria. You can edit them after adding.
                </div>
            </div>

        </div>
    </form>
</div>

<script>
let criterionCount = 1;

function addCriterion(description = '') {
    DynamicForm.addItemCard('criteria-container', 'criteria-card', (count) => `
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="cb-field-label">Description <span class="required">*</span></label>
                <input type="text" class="form-control" name="criteria[${criterionCount}][description]" value="${description}" placeholder="What is being evaluated" required>
            </div>
            <div class="col-md-3 mb-3">
                <label class="cb-field-label">Observed <span class="required">*</span></label>
                <select class="form-select" name="criteria[${criterionCount}][observed]" required>
                    <option value="">Select...</option>
                    <option value="1">Yes</option>
                    <option value="0">No</option>
                </select>
            </div>
            <div class="col-md-3 mb-3">
                <label class="cb-field-label">Remarks <span class="optional">(optional)</span></label>
                <input type="text" class="form-control" name="criteria[${criterionCount}][remarks]" placeholder="Notes">
            </div>
        </div>
    `, 'Criterion');
    criterionCount++;
}

const commonCriteria = {
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
    procedure: [
        'Followed the correct sequence of steps',
        'Read and understood instructions before starting',
        'Asked questions when unsure',
        'Completed all required steps'
    ],
    quality: [
        'Work meets quality standards',
        'Finished product functions correctly',
        'No visible defects or errors',
        'Proper soldering/connections'
    ],
    time: [
        'Completed task within allocated time',
        'Worked efficiently without wasting time',
        'Prioritized tasks appropriately',
        'Met deadline requirements'
    ]
};

function addCommonCriteria(type) {
    const criteria = commonCriteria[type];
    criteria.forEach(criterion => {
        addCriterion(criterion);
    });
}
</script>
@endsection

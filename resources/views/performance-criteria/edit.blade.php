@extends('layouts.app')

@section('title', 'Edit Performance Criteria')

@section('content')
<div class="content-area">
    <x-breadcrumb :items="[
        ['label' => 'Content', 'url' => route('content.management')],
        ['label' => 'Edit Performance Criteria'],
    ]" />

    <form action="{{ route('performance-criteria.update', $performanceCriteria) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="cb-container">
            {{-- Sidebar --}}
            <div class="cb-sidebar">
                <div class="cb-sidebar__title">Performance Criteria</div>

                @if($performanceCriteria->score !== null)
                <div class="cb-sidebar__group">
                    <div class="cb-sidebar__group-label"><i class="fas fa-chart-bar"></i> Current Score</div>
                    <div class="cb-sidebar__info">
                        <div class="cb-sidebar__info-title">Score</div>
                        <div style="font-size: 1.5rem; font-weight: 700; color: {{ $performanceCriteria->score >= 80 ? '#198754' : ($performanceCriteria->score >= 60 ? '#ffc107' : '#dc3545') }};">
                            {{ number_format($performanceCriteria->score, 1) }}%
                        </div>
                    </div>
                </div>
                @endif

                <div class="cb-sidebar__group">
                    <div class="cb-sidebar__group-label"><i class="fas fa-info-circle"></i> Context</div>
                    @if($taskSheet)
                    <div class="cb-sidebar__info">
                        <div class="cb-sidebar__info-title">Task Sheet</div>
                        {{ $taskSheet->title }} ({{ $taskSheet->task_number }})
                    </div>
                    @elseif($jobSheet)
                    <div class="cb-sidebar__info">
                        <div class="cb-sidebar__info-title">Job Sheet</div>
                        {{ $jobSheet->title }} ({{ $jobSheet->job_number }})
                    </div>
                    @endif
                </div>
            </div>

            {{-- Main Panel --}}
            <div class="cb-main">
                <div class="cb-header cb-header--criteria">
                    <h4><i class="fas fa-edit me-2"></i>Edit Performance Criteria</h4>
                    <p>Update evaluation criteria and observations</p>
                </div>

                <div class="cb-body">
                    {{-- Criteria Items --}}
                    <div class="cb-section">
                        <div class="cb-items-header">
                            <h5><i class="fas fa-clipboard-list"></i> Evaluation Criteria <span class="cb-count-badge">{{ count(json_decode($performanceCriteria->criteria, true) ?? []) }}</span></h5>
                        </div>

                        <div id="criteria-container">
                            @php $criteria = json_decode($performanceCriteria->criteria, true) ?? []; @endphp
                            @foreach($criteria as $index => $criterion)
                            <div class="cb-item-card criteria-card">
                                <div class="cb-item-card__header">
                                    <div class="left-section">
                                        <span class="cb-item-card__number">{{ $index + 1 }}</span>
                                        <span class="cb-item-card__title">Criterion #{{ $index + 1 }}</span>
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
                                            <input type="text" class="form-control" name="criteria[{{ $index }}][description]" value="{{ $criterion['description'] ?? '' }}" required>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label class="cb-field-label">Observed <span class="required">*</span></label>
                                            <select class="form-select" name="criteria[{{ $index }}][observed]" required>
                                                <option value="">Select...</option>
                                                <option value="1" {{ ($criterion['observed'] ?? '') == '1' || ($criterion['observed'] ?? '') === true ? 'selected' : '' }}>Yes</option>
                                                <option value="0" {{ ($criterion['observed'] ?? '') == '0' || ($criterion['observed'] ?? '') === false ? 'selected' : '' }}>No</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label class="cb-field-label">Remarks <span class="optional">(optional)</span></label>
                                            <input type="text" class="form-control" name="criteria[{{ $index }}][remarks]" value="{{ $criterion['remarks'] ?? '' }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>

                        <button type="button" class="cb-add-btn" onclick="addCriterion()">
                            <i class="fas fa-plus"></i> Add Criterion
                        </button>
                    </div>
                </div>

                <div class="cb-footer">
                    <a href="{{ route('content.management') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-1"></i>Cancel
                    </a>
                    <div class="btn-group-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Update Performance Criteria
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
let criterionCount = {{ count($criteria) }};

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
</script>
@endsection

@extends('layouts.app')

@section('title', 'Create Competency Test')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-1">Create Competency Test</h4>
                    <p class="text-muted mb-0">{{ $module->module_title }}</p>
                </div>
                <a href="{{ route('courses.modules.show', [$course, $module]) }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back to Module
                </a>
            </div>

            <form action="{{ route('courses.modules.competency-tests.store', [$course, $module]) }}" method="POST">
                @csrf

                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-clipboard-check me-2"></i>Test Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="title" class="form-label">Test Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror"
                                   id="title" name="title" value="{{ old('title') }}" required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <x-rich-editor
                                name="description"
                                label="Description"
                                placeholder="Test description..."
                                :value="old('description')"
                                toolbar="standard"
                                :height="100"
                            />
                            @error('description')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <x-rich-editor
                                name="instructions"
                                label="Instructions"
                                placeholder="Instructions shown to students before starting the test"
                                :value="old('instructions')"
                                toolbar="standard"
                                :height="100"
                            />
                            @error('instructions')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-cog me-2"></i>Test Settings</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="time_limit" class="form-label">Time Limit (minutes)</label>
                                <input type="number" class="form-control @error('time_limit') is-invalid @enderror"
                                       id="time_limit" name="time_limit" value="{{ old('time_limit') }}" min="1"
                                       placeholder="No limit">
                                @error('time_limit')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="passing_score" class="form-label">Passing Score (%)</label>
                                <input type="number" class="form-control @error('passing_score') is-invalid @enderror"
                                       id="passing_score" name="passing_score" value="{{ old('passing_score', 70) }}"
                                       min="0" max="100">
                                @error('passing_score')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="max_attempts" class="form-label">Max Attempts</label>
                                <input type="number" class="form-control @error('max_attempts') is-invalid @enderror"
                                       id="max_attempts" name="max_attempts" value="{{ old('max_attempts') }}" min="1"
                                       placeholder="Unlimited">
                                @error('max_attempts')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="reveal_answers"
                                           name="reveal_answers" value="1" {{ old('reveal_answers', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="reveal_answers">Show answers after submission</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="randomize_questions"
                                           name="randomize_questions" value="1" {{ old('randomize_questions') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="randomize_questions">Randomize question order</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="randomize_options"
                                           name="randomize_options" value="1" {{ old('randomize_options') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="randomize_options">Randomize answer options</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Parts Section --}}
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-layer-group me-2"></i>Test Parts (Optional)</h5>
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="addPart()">
                            <i class="fas fa-plus me-1"></i> Add Part
                        </button>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-3">
                            Organize your test into parts (e.g., Part 1: Multiple Choice, Part 2: Essay).
                            Questions can be assigned to parts when adding them.
                        </p>
                        <div id="partsContainer">
                            {{-- Parts will be added here dynamically --}}
                        </div>
                        <div id="noPartsMessage" class="text-muted text-center py-3">
                            <i class="fas fa-info-circle me-1"></i> No parts defined. All questions will appear together.
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('courses.modules.show', [$course, $module]) }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Create Test
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
let partIndex = 0;

function addPart() {
    document.getElementById('noPartsMessage').style.display = 'none';

    const container = document.getElementById('partsContainer');
    const html = `
        <div class="card mb-3 part-item" data-index="${partIndex}">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <h6 class="mb-0">Part ${partIndex + 1}</h6>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removePart(this)">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="mb-2">
                    <input type="text" class="form-control form-control-sm"
                           name="parts[${partIndex}][name]"
                           placeholder="Part name (e.g., Multiple Choice)" required>
                </div>
                <div>
                    <textarea class="form-control form-control-sm"
                              name="parts[${partIndex}][instructions]"
                              rows="2" placeholder="Part instructions (optional)"></textarea>
                </div>
            </div>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', html);
    partIndex++;
    updatePartNumbers();
}

function removePart(btn) {
    btn.closest('.part-item').remove();
    updatePartNumbers();

    if (document.querySelectorAll('.part-item').length === 0) {
        document.getElementById('noPartsMessage').style.display = 'block';
    }
}

function updatePartNumbers() {
    document.querySelectorAll('.part-item').forEach((item, index) => {
        item.querySelector('h6').textContent = `Part ${index + 1}`;
    });
}
</script>
@endpush
@endsection

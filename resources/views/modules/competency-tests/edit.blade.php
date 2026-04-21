@extends('layouts.app')

@section('title', 'Edit Competency Test')

@section('content')
<div class="container py-4">
    <div class="row">
        <div class="col-lg-8">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-1">Edit Competency Test</h4>
                    <p class="text-muted mb-0">{{ $module->module_title }}</p>
                </div>
                <a href="{{ route('courses.modules.show', [$course, $module]) }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back
                </a>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <form action="{{ route('courses.modules.competency-tests.update', [$course, $module, $test]) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-clipboard-check me-2"></i>Test Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="title" class="form-label">Test Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror"
                                   id="title" name="title" value="{{ old('title', $test->title) }}" required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <x-rich-editor
                                name="description"
                                label="Description"
                                placeholder="Test description..."
                                :value="old('description', $test->description)"
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
                                :value="old('instructions', $test->instructions)"
                                toolbar="standard"
                                :height="100"
                            />
                            @error('instructions')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>

                        <div class="form-check mb-3">
                            <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1"
                                   {{ old('is_active', $test->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">Active (students can take this test)</label>
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
                                <input type="number" class="form-control" id="time_limit" name="time_limit"
                                       value="{{ old('time_limit', $test->time_limit) }}" min="1" placeholder="No limit">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="passing_score" class="form-label">Passing Score (%)</label>
                                <input type="number" class="form-control" id="passing_score" name="passing_score"
                                       value="{{ old('passing_score', $test->passing_score) }}" min="0" max="100">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="max_attempts" class="form-label">Max Attempts</label>
                                <input type="number" class="form-control" id="max_attempts" name="max_attempts"
                                       value="{{ old('max_attempts', $test->max_attempts) }}" min="1" placeholder="Unlimited">
                            </div>
                        </div>

                        <div class="row mt-2">
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="reveal_answers" name="reveal_answers" value="1"
                                           {{ old('reveal_answers', $test->reveal_answers) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="reveal_answers">Show answers after submission</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="randomize_questions" name="randomize_questions" value="1"
                                           {{ old('randomize_questions', $test->randomize_questions) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="randomize_questions">Randomize questions</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="randomize_options" name="randomize_options" value="1"
                                           {{ old('randomize_options', $test->randomize_options) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="randomize_options">Randomize options</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-danger" onclick="confirmDelete()">
                        <i class="fas fa-trash me-1"></i> Delete Test
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Save Changes
                    </button>
                </div>
            </form>

            <form id="deleteForm" action="{{ route('courses.modules.competency-tests.destroy', [$course, $module, $test]) }}" method="POST" class="d-none">
                @csrf
                @method('DELETE')
            </form>
        </div>

        {{-- Questions Sidebar --}}
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 sticky-top" style="top: 1rem;">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-list me-2"></i>Questions ({{ $test->questions->count() }})</h5>
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addQuestionModal">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
                <div class="card-body" style="max-height: 60vh; overflow-y: auto;">
                    @if($test->questions->isEmpty())
                        <p class="text-muted text-center mb-0">No questions yet. Click + to add.</p>
                    @else
                        @php $parts = $test->parts ?? []; @endphp
                        @foreach($test->questions->groupBy('part_index') as $partIndex => $questions)
                            @if($partIndex !== '' && isset($parts[$partIndex]))
                                <h6 class="text-muted mb-2 mt-3">{{ $parts[$partIndex]['name'] ?? 'Part ' . ($partIndex + 1) }}</h6>
                            @endif
                            @foreach($questions as $index => $question)
                                <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                                    <div class="small">
                                        <strong>Q{{ $loop->parent->iteration }}.{{ $loop->iteration }}</strong>
                                        <span class="text-muted">{{ Str::limit($question->question_text, 30) }}</span>
                                    </div>
                                    <span class="badge bg-secondary">{{ $question->points }} pt</span>
                                </div>
                            @endforeach
                        @endforeach
                    @endif
                </div>
                <div class="card-footer bg-light">
                    <small class="text-muted">Total Points: <strong>{{ $test->total_points }}</strong></small>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Add Question Modal --}}
<div class="modal fade" id="addQuestionModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Question</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addQuestionForm">
                    <div class="mb-3">
                        <label class="form-label">Question Text</label>
                        <textarea class="form-control" name="question_text" rows="3" required></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Question Type</label>
                            <select class="form-select" name="question_type" id="questionType">
                                <option value="multiple_choice">Multiple Choice</option>
                                <option value="true_false">True/False</option>
                                <option value="fill_blank">Fill in the Blank</option>
                                <option value="short_answer">Short Answer</option>
                                <option value="essay">Essay</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Points</label>
                            <input type="number" class="form-control" name="points" value="1" min="1">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Part</label>
                            <select class="form-select" name="part_index">
                                <option value="">No Part</option>
                                @foreach($test->parts ?? [] as $index => $part)
                                    <option value="{{ $index }}">{{ $part['name'] ?? 'Part ' . ($index + 1) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div id="optionsContainer" class="mb-3">
                        <label class="form-label">Options</label>
                        <div id="optionsList">
                            <div class="input-group mb-2">
                                <span class="input-group-text">A</span>
                                <input type="text" class="form-control" name="options[]" placeholder="Option A">
                            </div>
                            <div class="input-group mb-2">
                                <span class="input-group-text">B</span>
                                <input type="text" class="form-control" name="options[]" placeholder="Option B">
                            </div>
                            <div class="input-group mb-2">
                                <span class="input-group-text">C</span>
                                <input type="text" class="form-control" name="options[]" placeholder="Option C">
                            </div>
                            <div class="input-group mb-2">
                                <span class="input-group-text">D</span>
                                <input type="text" class="form-control" name="options[]" placeholder="Option D">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Correct Answer</label>
                        <input type="text" class="form-control" name="correct_answer"
                               placeholder="For multiple choice: 0, 1, 2, or 3 (A=0, B=1, etc.)">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Explanation (Optional)</label>
                        <textarea class="form-control" name="explanation" rows="2"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="addQuestion()">Add Question</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function confirmDelete() {
    if (confirm('Are you sure you want to delete this test? This action cannot be undone.')) {
        document.getElementById('deleteForm').submit();
    }
}

document.getElementById('questionType').addEventListener('change', function() {
    const optionsContainer = document.getElementById('optionsContainer');
    const type = this.value;

    if (['multiple_choice', 'multiple_select', 'image_choice'].includes(type)) {
        optionsContainer.style.display = 'block';
    } else {
        optionsContainer.style.display = 'none';
    }
});

function addQuestion() {
    const form = document.getElementById('addQuestionForm');
    const formData = new FormData(form);

    const question = {
        question_text: formData.get('question_text'),
        question_type: formData.get('question_type'),
        points: parseInt(formData.get('points')) || 1,
        correct_answer: formData.get('correct_answer'),
        explanation: formData.get('explanation'),
        part_index: formData.get('part_index') ? parseInt(formData.get('part_index')) : null,
        options: formData.getAll('options[]').filter(o => o.trim() !== '')
    };

    fetch('{{ route("courses.modules.competency-tests.questions.store", [$course, $module, $test]) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ questions: [question] })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error adding question');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error adding question');
    });
}
</script>
@endpush
@endsection

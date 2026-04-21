@extends('layouts.app')

@section('title', 'Edit Self-Check')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/pages/quiz-builder.css') }}">
@endpush

@section('content')
<div class="content-area">
    {{-- Breadcrumb --}}
    <x-breadcrumb :items="[
        ['label' => 'Content', 'url' => route('content.management')],
        ['label' => $selfCheck->title, 'url' => route('self-checks.show', $selfCheck)],
        ['label' => 'Edit'],
    ]" />

    <form action="{{ route('self-checks.update', [$informationSheet, $selfCheck]) }}" method="POST" id="quiz-form" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="quiz-builder-layout">
            {{-- RIGHT SIDEBAR: Question Types --}}
            <div class="cb-sidebar">
                <button class="btn btn-outline-primary w-100 mb-2 sidebar-toggle-btn" type="button" data-bs-toggle="collapse" data-bs-target="#questionTypesSidebar">
                    <i class="fas fa-plus-circle me-2"></i>Question Types <i class="fas fa-chevron-down ms-1"></i>
                </button>
                <div class="collapse show" id="questionTypesSidebar">
                <div class="cb-sidebar__title d-none d-lg-block">
                    <i class="fas fa-plus-circle me-2"></i>Add Question
                </div>

                {{-- Basic Questions --}}
                <div class="cb-sidebar__group">
                    <div class="cb-sidebar__group-label">
                        <i class="fas fa-font"></i> Basic
                    </div>

                    <button type="button" class="cb-sidebar__item" data-type="multiple_choice">
                        <div class="cb-sidebar__item-icon cb-sidebar__item-icon--blue"><i class="fas fa-list-ul"></i></div>
                        <div class="cb-sidebar__item-text">
                            <span class="cb-sidebar__item-name">Multiple Choice</span>
                            <span class="cb-sidebar__item-desc">Single correct answer</span>
                        </div>
                    </button>

                    <button type="button" class="cb-sidebar__item" data-type="multiple_select">
                        <div class="cb-sidebar__item-icon cb-sidebar__item-icon--blue"><i class="fas fa-check-double"></i></div>
                        <div class="cb-sidebar__item-text">
                            <span class="cb-sidebar__item-name">Multiple Select</span>
                            <span class="cb-sidebar__item-desc">Multiple correct answers</span>
                        </div>
                    </button>

                    <button type="button" class="cb-sidebar__item" data-type="true_false">
                        <div class="cb-sidebar__item-icon cb-sidebar__item-icon--blue"><i class="fas fa-check-circle"></i></div>
                        <div class="cb-sidebar__item-text">
                            <span class="cb-sidebar__item-name">True / False</span>
                            <span class="cb-sidebar__item-desc">Binary choice</span>
                        </div>
                    </button>

                    <button type="button" class="cb-sidebar__item" data-type="fill_blank">
                        <div class="cb-sidebar__item-icon cb-sidebar__item-icon--blue"><i class="fas fa-i-cursor"></i></div>
                        <div class="cb-sidebar__item-text">
                            <span class="cb-sidebar__item-name">Fill in the Blank</span>
                            <span class="cb-sidebar__item-desc">Type the answer</span>
                        </div>
                    </button>

                    <button type="button" class="cb-sidebar__item" data-type="short_answer">
                        <div class="cb-sidebar__item-icon cb-sidebar__item-icon--blue"><i class="fas fa-align-left"></i></div>
                        <div class="cb-sidebar__item-text">
                            <span class="cb-sidebar__item-name">Short Answer</span>
                            <span class="cb-sidebar__item-desc">Brief text response</span>
                        </div>
                    </button>

                    <button type="button" class="cb-sidebar__item" data-type="numeric">
                        <div class="cb-sidebar__item-icon cb-sidebar__item-icon--blue"><i class="fas fa-calculator"></i></div>
                        <div class="cb-sidebar__item-text">
                            <span class="cb-sidebar__item-name">Numeric</span>
                            <span class="cb-sidebar__item-desc">Number with tolerance</span>
                        </div>
                    </button>
                </div>

                {{-- Interactive Questions --}}
                <div class="cb-sidebar__group">
                    <div class="cb-sidebar__group-label">
                        <i class="fas fa-hand-pointer"></i> Interactive
                    </div>

                    <button type="button" class="cb-sidebar__item" data-type="matching">
                        <div class="cb-sidebar__item-icon cb-sidebar__item-icon--green"><i class="fas fa-arrows-alt-h"></i></div>
                        <div class="cb-sidebar__item-text">
                            <span class="cb-sidebar__item-name">Matching</span>
                            <span class="cb-sidebar__item-desc">Column A to Column B</span>
                        </div>
                    </button>

                    <button type="button" class="cb-sidebar__item" data-type="ordering">
                        <div class="cb-sidebar__item-icon cb-sidebar__item-icon--green"><i class="fas fa-sort-numeric-down"></i></div>
                        <div class="cb-sidebar__item-text">
                            <span class="cb-sidebar__item-name">Ordering</span>
                            <span class="cb-sidebar__item-desc">Arrange in sequence</span>
                        </div>
                    </button>

                    <button type="button" class="cb-sidebar__item" data-type="classification">
                        <div class="cb-sidebar__item-icon cb-sidebar__item-icon--green"><i class="fas fa-th-large"></i></div>
                        <div class="cb-sidebar__item-text">
                            <span class="cb-sidebar__item-name">Classification</span>
                            <span class="cb-sidebar__item-desc">Sort into categories</span>
                        </div>
                    </button>

                    <button type="button" class="cb-sidebar__item" data-type="drag_drop">
                        <div class="cb-sidebar__item-icon cb-sidebar__item-icon--green"><i class="fas fa-hand-pointer"></i></div>
                        <div class="cb-sidebar__item-text">
                            <span class="cb-sidebar__item-name">Drag & Drop</span>
                            <span class="cb-sidebar__item-desc">Drag items to zones</span>
                        </div>
                    </button>

                    <button type="button" class="cb-sidebar__item" data-type="slider">
                        <div class="cb-sidebar__item-icon cb-sidebar__item-icon--teal"><i class="fas fa-sliders-h"></i></div>
                        <div class="cb-sidebar__item-text">
                            <span class="cb-sidebar__item-name">Slider</span>
                            <span class="cb-sidebar__item-desc">Select value on range</span>
                        </div>
                    </button>
                </div>

                {{-- Image-Based Questions --}}
                <div class="cb-sidebar__group">
                    <div class="cb-sidebar__group-label">
                        <i class="fas fa-image"></i> Image-Based
                    </div>

                    <button type="button" class="cb-sidebar__item" data-type="image_choice">
                        <div class="cb-sidebar__item-icon cb-sidebar__item-icon--red"><i class="fas fa-images"></i></div>
                        <div class="cb-sidebar__item-text">
                            <span class="cb-sidebar__item-name">Image Choice</span>
                            <span class="cb-sidebar__item-desc">Select from images</span>
                        </div>
                    </button>

                    <button type="button" class="cb-sidebar__item" data-type="image_identification">
                        <div class="cb-sidebar__item-icon cb-sidebar__item-icon--red"><i class="fas fa-search"></i></div>
                        <div class="cb-sidebar__item-text">
                            <span class="cb-sidebar__item-name">Name This Picture</span>
                            <span class="cb-sidebar__item-desc">Identify the image</span>
                        </div>
                    </button>

                    <button type="button" class="cb-sidebar__item" data-type="hotspot">
                        <div class="cb-sidebar__item-icon cb-sidebar__item-icon--red"><i class="fas fa-crosshairs"></i></div>
                        <div class="cb-sidebar__item-text">
                            <span class="cb-sidebar__item-name">Hotspot</span>
                            <span class="cb-sidebar__item-desc">Click correct area</span>
                        </div>
                    </button>

                    <button type="button" class="cb-sidebar__item" data-type="image_labeling">
                        <div class="cb-sidebar__item-icon cb-sidebar__item-icon--red"><i class="fas fa-tags"></i></div>
                        <div class="cb-sidebar__item-text">
                            <span class="cb-sidebar__item-name">Image Labeling</span>
                            <span class="cb-sidebar__item-desc">Label parts of image</span>
                        </div>
                    </button>
                </div>

                {{-- Media Questions --}}
                <div class="cb-sidebar__group">
                    <div class="cb-sidebar__group-label">
                        <i class="fas fa-play-circle"></i> Media
                    </div>

                    <button type="button" class="cb-sidebar__item" data-type="audio_question">
                        <div class="cb-sidebar__item-icon cb-sidebar__item-icon--purple"><i class="fas fa-headphones"></i></div>
                        <div class="cb-sidebar__item-text">
                            <span class="cb-sidebar__item-name">Audio Question</span>
                            <span class="cb-sidebar__item-desc">Listen and answer</span>
                        </div>
                    </button>

                    <button type="button" class="cb-sidebar__item" data-type="video_question">
                        <div class="cb-sidebar__item-icon cb-sidebar__item-icon--indigo"><i class="fas fa-video"></i></div>
                        <div class="cb-sidebar__item-text">
                            <span class="cb-sidebar__item-name">Video Question</span>
                            <span class="cb-sidebar__item-desc">Watch and answer</span>
                        </div>
                    </button>
                </div>

                {{-- Advanced Questions --}}
                <div class="cb-sidebar__group">
                    <div class="cb-sidebar__group-label">
                        <i class="fas fa-graduation-cap"></i> Advanced
                    </div>

                    <button type="button" class="cb-sidebar__item" data-type="essay">
                        <div class="cb-sidebar__item-icon cb-sidebar__item-icon--orange"><i class="fas fa-file-alt"></i></div>
                        <div class="cb-sidebar__item-text">
                            <span class="cb-sidebar__item-name">Essay</span>
                            <span class="cb-sidebar__item-desc">Long form response</span>
                        </div>
                    </button>
                </div>
                </div>{{-- end collapse --}}
            </div>

            {{-- MAIN CONTENT: Quiz Settings & Questions --}}
            <div class="cb-main">
                <div class="cb-header cb-header--self-check">
                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                        <div>
                            <h4 class="mb-1"><i class="fas fa-edit me-2"></i>Edit Self-Check</h4>
                            <p class="mb-0">{{ $selfCheck->check_number }}: {{ $selfCheck->title }}</p>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            @if($selfCheck->file_path)
                            <a href="{{ route('self-checks.download', $selfCheck) }}" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-download me-1"></i>{{ $selfCheck->original_filename }}
                            </a>
                            @endif
                            <label class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-2 mb-0" style="cursor: pointer;">
                                <i class="fas fa-paperclip"></i>
                                <span id="file-label">{{ $selfCheck->file_path ? 'Replace File' : 'Attach Document' }}</span>
                                <input type="file" class="d-none" name="file" accept=".pdf,.xlsx,.xls,.doc,.docx,.ppt,.pptx"
                                       onchange="handleFilePreview(this)">
                            </label>
                        </div>
                    </div>
                </div>

                <div class="cb-body">
                    {{-- Existing Document Preview --}}
                    @if($selfCheck->document_content)
                    <div id="docPreviewSection" style="background: #f0f0f0; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
                        <div style="background: #fff; box-shadow: 0 1px 4px rgba(0,0,0,0.1); border-radius: 4px; padding: 1.5rem; max-height: 350px; overflow: hidden; overflow-y: auto; line-height: 1.7; font-size: 0.9rem;" id="docPreviewContent">
                            {!! $selfCheck->document_content !!}
                        </div>
                        <div class="text-center py-2">
                            <small class="text-muted"><i class="fas fa-eye me-1"></i>Current document preview — upload a new file to replace</small>
                        </div>
                    </div>
                    @else
                    <div class="d-none" id="docPreviewSection" style="background: #f0f0f0; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
                        <div style="background: #fff; box-shadow: 0 1px 4px rgba(0,0,0,0.1); border-radius: 4px; padding: 1.5rem; max-height: 350px; overflow: hidden; overflow-y: auto; line-height: 1.7; font-size: 0.9rem;" id="docPreviewContent"></div>
                        <div class="text-center py-2">
                            <small class="text-muted"><i class="fas fa-eye me-1"></i>Document preview</small>
                        </div>
                    </div>
                    @endif
                    <div class="d-none text-center py-3" id="docConvertingSpinner">
                        <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                        <span class="text-muted ms-2">Converting document for preview...</span>
                    </div>
                    <div class="d-none" id="docPdfNotice" style="background: #fff3cd; padding: 0.75rem 1rem; border-radius: 6px; margin-bottom: 1rem; border-left: 4px solid #ffc107;">
                        <i class="fas fa-file-pdf text-warning me-1"></i>
                        <span class="small">PDF/Excel files will be available for download only (no inline preview).</span>
                    </div>
                    {{-- Quiz Settings --}}
                    <div class="cb-settings">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="cb-field-label">Quiz Title <span class="required">*</span></label>
                                    <input type="text" class="form-control @error('title') is-invalid @enderror" name="title"
                                           value="{{ old('title', $selfCheck->title) }}" required>
                                    @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="cb-field-label">Passing Score (%)</label>
                                    <input type="number" class="form-control @error('passing_score') is-invalid @enderror" name="passing_score"
                                           value="{{ old('passing_score', $selfCheck->passing_score) }}" min="0" max="100">
                                    @error('passing_score')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="cb-field-label">Time Limit (min)</label>
                                    <input type="number" class="form-control @error('time_limit') is-invalid @enderror" name="time_limit"
                                           value="{{ old('time_limit', $selfCheck->time_limit) }}" placeholder="No limit" min="1">
                                    @error('time_limit')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="cb-field-label">Deadline <span class="optional">(optional)</span></label>
                                    <input type="datetime-local" class="form-control @error('due_date') is-invalid @enderror" name="due_date"
                                           value="{{ old('due_date', $selfCheck->due_date ? $selfCheck->due_date->format('Y-m-d\TH:i') : '') }}">
                                    @error('due_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    <small class="text-muted">Submissions blocked after this date</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="cb-field-label">Max Attempts <span class="optional">(optional)</span></label>
                                    <input type="number" class="form-control @error('max_attempts') is-invalid @enderror" name="max_attempts"
                                           value="{{ old('max_attempts', $selfCheck->max_attempts) }}" placeholder="Unlimited" min="1">
                                    @error('max_attempts')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    <small class="text-muted">Leave blank for unlimited</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="cb-field-label d-block">Reveal Answers</label>
                                    <div class="form-check mt-2">
                                        <input type="checkbox" class="form-check-input" name="reveal_answers" id="reveal_answers" value="1"
                                               {{ old('reveal_answers', $selfCheck->reveal_answers) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="reveal_answers">Show per-question feedback</label>
                                    </div>
                                    <small class="text-muted">When unchecked, students only see their overall score</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="cb-field-label d-block"><i class="fas fa-random text-primary me-1"></i> Randomization</label>
                                    <div class="form-check mt-2">
                                        <input type="checkbox" class="form-check-input" name="randomize_questions" id="randomize_questions" value="1"
                                               {{ old('randomize_questions', $selfCheck->randomize_questions) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="randomize_questions">Randomize question order</label>
                                    </div>
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" name="randomize_options" id="randomize_options" value="1"
                                               {{ old('randomize_options', $selfCheck->randomize_options) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="randomize_options">Randomize answer options</label>
                                    </div>
                                    <small class="text-muted">Each student sees questions/options in different order</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-0">
                                    <x-rich-editor
                                        name="description"
                                        label="Description"
                                        placeholder="Brief description of this quiz..."
                                        :value="old('description', $selfCheck->description)"
                                        toolbar="standard"
                                        :height="80"
                                    />
                                    @error('description')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-0">
                                    <x-rich-editor
                                        name="instructions"
                                        label="Instructions"
                                        placeholder="Instructions for this quiz..."
                                        :value="old('instructions', $selfCheck->instructions)"
                                        toolbar="standard"
                                        :height="80"
                                        :required="true"
                                    />
                                    @error('instructions')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <input type="hidden" name="check_number" value="{{ old('check_number', $selfCheck->check_number) }}">

                    {{-- Questions Section --}}
                    <div class="cb-items-header">
                        <h5>
                            <i class="fas fa-question-circle text-primary me-2"></i>
                            Questions
                            <span class="cb-count-badge" id="question-count">{{ $selfCheck->questions->count() }}</span>
                        </h5>
                        <div class="text-muted">
                            <small>Total Points: <strong id="total-points">{{ $selfCheck->questions->sum('points') }}</strong></small>
                        </div>
                    </div>

                    <div id="questions-container">
                        <div class="cb-empty-state" id="empty-state" style="{{ $selfCheck->questions->count() > 0 ? 'display:none' : '' }}">
                            <i class="fas fa-mouse-pointer d-block"></i>
                            <p><strong>No questions yet</strong><br>Click a question type from the left panel to add questions</p>
                        </div>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="cb-footer">
                    <a href="{{ route('self-checks.show', $selfCheck) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Cancel
                    </a>
                    <div class="btn-group-footer">
                        <button type="submit" class="btn btn-primary" id="save-btn">
                            <i class="fas fa-save me-1"></i>Update Self-Check
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
function handleFilePreview(input) {
    var file = input.files[0];
    if (!file) return;
    var label = document.getElementById('file-label');
    label.textContent = file.name;
    input.closest('label').classList.remove('btn-outline-secondary');
    input.closest('label').classList.add('btn-primary');
    var ext = file.name.split('.').pop().toLowerCase();
    var preview = document.getElementById('docPreviewSection');
    var spinner = document.getElementById('docConvertingSpinner');
    var pdfNotice = document.getElementById('docPdfNotice');
    var content = document.getElementById('docPreviewContent');
    preview.classList.add('d-none');
    spinner.classList.add('d-none');
    pdfNotice.classList.add('d-none');
    if (['docx', 'pptx', 'doc', 'ppt', 'pdf', 'xlsx', 'xls'].includes(ext)) {
        spinner.classList.remove('d-none');
        var formData = new FormData();
        formData.append('document', file);
        fetch("{{ route('document-assessments.convert') }}", {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            body: formData,
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            spinner.classList.add('d-none');
            if (data.success && data.html) { content.innerHTML = data.html; preview.classList.remove('d-none'); }
        })
        .catch(function() { spinner.classList.add('d-none'); });
    }
}
</script>
<script>
window.quizConfig = {
    csrf: '{{ csrf_token() }}',
    uploadImageUrl: '{{ route("quiz.upload-image") }}',
    uploadAudioUrl: '{{ route("quiz.upload-audio") }}',
    uploadVideoUrl: '{{ route("quiz.upload-video") }}',
    existingQuestions: @json($existingQuestions ?? [])
};
</script>
<script src="{{ asset('js/components/quiz-builder.js') }}?v={{ filemtime(public_path('js/components/quiz-builder.js')) }}"></script>
@endpush

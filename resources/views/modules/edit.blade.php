@extends('layouts.app')

@section('title', 'Edit Module - EPAS-E')

@section('content')
<div class="content-area">
    <x-breadcrumb :items="[
        ['label' => 'Courses', 'url' => route('content.management')],
        ['label' => 'Edit: ' . $module->module_number],
    ]" />

    <div class="cb-container--simple">
        <form method="POST" action="{{ route('courses.modules.update', [$course, $module]) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="cb-main">
                <div class="cb-header cb-header--module">
                    <h4><i class="fas fa-edit me-2"></i>Edit Learning Module</h4>
                    <p>{{ $module->module_number }}: {{ $module->module_name }}</p>
                </div>

                <div class="cb-body">
                    {{-- Module Identity --}}
                    <div class="cb-section">
                        <div class="cb-section__title"><i class="fas fa-id-card"></i> Module Identity</div>
                        <div class="cb-settings">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="cb-field-label">Qualification Title <span class="required">*</span></label>
                                    <input type="text" class="form-control @error('qualification_title') is-invalid @enderror"
                                           name="qualification_title" value="{{ old('qualification_title', $module->qualification_title) }}" required>
                                    @error('qualification_title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="cb-field-label">Unit of Competency <span class="required">*</span></label>
                                    <input type="text" class="form-control @error('unit_of_competency') is-invalid @enderror"
                                           name="unit_of_competency" value="{{ old('unit_of_competency', $module->unit_of_competency) }}" required>
                                    @error('unit_of_competency')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="cb-field-label">Module Title <span class="required">*</span></label>
                                    <input type="text" class="form-control @error('module_title') is-invalid @enderror"
                                           name="module_title" value="{{ old('module_title', $module->module_title) }}" required>
                                    @error('module_title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="cb-field-label">Module Number <span class="required">*</span></label>
                                    <input type="text" class="form-control @error('module_number') is-invalid @enderror"
                                           name="module_number" value="{{ old('module_number', $module->module_number) }}" required>
                                    @error('module_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="cb-field-label">Module Name <span class="required">*</span></label>
                                <input type="text" class="form-control @error('module_name') is-invalid @enderror"
                                       name="module_name" value="{{ old('module_name', $module->module_name) }}" required>
                                @error('module_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div>
                                <label class="cb-field-label">Module Thumbnail <span class="optional">(optional)</span></label>
                                @if($module->thumbnail)
                                <div class="mb-2">
                                    <img src="{{ asset('storage/' . $module->thumbnail) }}" alt="Current thumbnail"
                                         class="img-thumbnail" style="max-height: 150px;">
                                    <div class="form-check mt-2">
                                        <input class="form-check-input" type="checkbox" name="remove_thumbnail" value="1" id="removeThumbnail">
                                        <label class="form-check-label" for="removeThumbnail">Remove current thumbnail</label>
                                    </div>
                                </div>
                                @endif
                                <input type="file" class="form-control @error('thumbnail') is-invalid @enderror"
                                       name="thumbnail" accept="image/jpeg,image/png,image/jpg,image/gif,image/webp">
                                @error('thumbnail')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                <small class="text-muted">Recommended: 800x450px (16:9 ratio). Max 5MB.</small>
                            </div>
                        </div>
                    </div>

                    {{-- Content --}}
                    <div class="cb-section">
                        <div class="cb-section__title"><i class="fas fa-file-alt"></i> Module Content</div>
                        <div class="mb-3">
                            <x-rich-editor
                                name="table_of_contents"
                                label="Table of Contents"
                                placeholder="Enter the table of contents..."
                                :value="old('table_of_contents', $module->table_of_contents)"
                                toolbar="standard"
                                :height="150"
                            />
                            @error('table_of_contents')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <x-rich-editor
                                name="how_to_use_cblm"
                                label="How to Use CBLM"
                                placeholder="Instructions on how to use this CBLM..."
                                :value="old('how_to_use_cblm', $module->how_to_use_cblm)"
                                toolbar="standard"
                                :height="120"
                            />
                            @error('how_to_use_cblm')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <x-rich-editor
                                name="introduction"
                                label="Introduction"
                                placeholder="Module introduction..."
                                :value="old('introduction', $module->introduction)"
                                toolbar="standard"
                                :height="120"
                            />
                            @error('introduction')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <x-rich-editor
                                name="learning_outcomes"
                                label="Learning Outcomes"
                                placeholder="What students will learn..."
                                :value="old('learning_outcomes', $module->learning_outcomes)"
                                toolbar="standard"
                                :height="120"
                            />
                            @error('learning_outcomes')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    {{-- Settings --}}
                    <div class="cb-section">
                        <div class="cb-section__title"><i class="fas fa-cog"></i> Settings</div>
                        <div class="cb-settings">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1"
                                       {{ old('is_active', $module->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">Active Module</label>
                            </div>
                        </div>
                    </div>

                    {{-- Final Assessment Settings --}}
                    <div class="cb-section">
                        <div class="cb-section__title"><i class="fas fa-clipboard-check"></i> Final Assessment</div>
                        <div class="cb-settings">
                            <p class="text-muted small mb-3">
                                <i class="fas fa-info-circle me-1"></i>
                                A final assessment combines all self-check questions from this module into one exam.
                                Students must pass this assessment to complete the module.
                            </p>

                            {{-- Enable Assessment Toggle --}}
                            <div class="form-check form-switch mb-3">
                                <input type="checkbox" class="form-check-input" id="require_final_assessment"
                                       name="require_final_assessment" value="1"
                                       {{ old('require_final_assessment', $module->require_final_assessment) ? 'checked' : '' }}
                                       onchange="toggleAssessmentSettings()">
                                <label class="form-check-label fw-semibold" for="require_final_assessment">
                                    Require Final Assessment
                                </label>
                            </div>

                            {{-- Assessment Settings (shown when enabled) --}}
                            <div id="assessmentSettings" class="{{ old('require_final_assessment', $module->require_final_assessment) ? '' : 'd-none' }}">
                                <div class="border rounded p-3 bg-light">
                                    <div class="row">
                                        {{-- Question Options --}}
                                        <div class="col-md-6 mb-3">
                                            <label class="cb-field-label">Question Mode</label>
                                            <select class="form-select" name="assessment_question_mode" id="assessment_question_mode" onchange="toggleQuestionCount()">
                                                <option value="all" {{ old('assessment_question_mode', $module->assessment_question_mode) == 'all' ? 'selected' : '' }}>
                                                    All Questions
                                                </option>
                                                <option value="random_subset" {{ old('assessment_question_mode', $module->assessment_question_mode) == 'random_subset' ? 'selected' : '' }}>
                                                    Random Subset
                                                </option>
                                            </select>
                                            <small class="text-muted">Choose how questions are selected</small>
                                        </div>

                                        <div class="col-md-6 mb-3" id="questionCountWrapper" style="{{ old('assessment_question_mode', $module->assessment_question_mode) == 'random_subset' ? '' : 'display: none;' }}">
                                            <label class="cb-field-label">Number of Questions</label>
                                            <input type="number" class="form-control" name="assessment_question_count"
                                                   value="{{ old('assessment_question_count', $module->assessment_question_count) }}"
                                                   min="1" placeholder="Leave empty for all">
                                            <small class="text-muted">Questions to include in each attempt</small>
                                        </div>
                                    </div>

                                    <div class="row">
                                        {{-- Passing Score --}}
                                        <div class="col-md-4 mb-3">
                                            <label class="cb-field-label">Passing Score (%)</label>
                                            <input type="number" class="form-control" name="assessment_passing_score"
                                                   value="{{ old('assessment_passing_score', $module->assessment_passing_score ?? 70) }}"
                                                   min="0" max="100" required>
                                        </div>

                                        {{-- Time Limit --}}
                                        <div class="col-md-4 mb-3">
                                            <label class="cb-field-label">Time Limit (minutes)</label>
                                            <input type="number" class="form-control" name="assessment_time_limit"
                                                   value="{{ old('assessment_time_limit', $module->assessment_time_limit) }}"
                                                   min="1" placeholder="No limit">
                                            <small class="text-muted">Leave empty for no time limit</small>
                                        </div>

                                        {{-- Max Attempts --}}
                                        <div class="col-md-4 mb-3">
                                            <label class="cb-field-label">Max Attempts</label>
                                            <input type="number" class="form-control" name="assessment_max_attempts"
                                                   value="{{ old('assessment_max_attempts', $module->assessment_max_attempts) }}"
                                                   min="1" placeholder="Unlimited">
                                            <small class="text-muted">Leave empty for unlimited</small>
                                        </div>
                                    </div>

                                    {{-- Toggles --}}
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="form-check form-switch mb-2">
                                                <input type="checkbox" class="form-check-input" id="assessment_randomize_questions"
                                                       name="assessment_randomize_questions" value="1"
                                                       {{ old('assessment_randomize_questions', $module->assessment_randomize_questions ?? true) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="assessment_randomize_questions">
                                                    <i class="fas fa-random me-1 text-primary"></i>
                                                    Randomize Question Order
                                                </label>
                                            </div>

                                            <div class="form-check form-switch">
                                                <input type="checkbox" class="form-check-input" id="assessment_show_answers"
                                                       name="assessment_show_answers" value="1"
                                                       {{ old('assessment_show_answers', $module->assessment_show_answers) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="assessment_show_answers">
                                                    <i class="fas fa-eye me-1 text-success"></i>
                                                    Show Correct Answers After Completion
                                                </label>
                                            </div>

                                            {{-- Always require completion of activities (hidden, always true) --}}
                                            <input type="hidden" name="assessment_require_completion" value="1">
                                            <p class="text-muted small mt-2 mb-0">
                                                <i class="fas fa-info-circle me-1"></i>
                                                Students must complete all activities before taking the final assessment.
                                            </p>
                                        </div>
                                    </div>

                                    {{-- Question Sources (Future Enhancement) --}}
                                    {{--
                                    <div class="mt-3">
                                        <label class="cb-field-label">Include Questions From:</label>
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="source_self_check"
                                                   name="assessment_include_sources[]" value="self_check" checked disabled>
                                            <label class="form-check-label" for="source_self_check">Self-Checks</label>
                                        </div>
                                    </div>
                                    --}}
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Prerequisites --}}
                    @if(isset($availablePrerequisites) && $availablePrerequisites->count() > 0)
                    <div class="cb-section">
                        <div class="cb-section__title"><i class="fas fa-lock"></i> Prerequisites</div>
                        <div class="cb-settings">
                            <p class="text-muted small mb-3">
                                <i class="fas fa-info-circle me-1"></i>
                                Select modules that students must complete before accessing this module.
                                Students will see a locked message until they complete all prerequisites.
                            </p>
                            <div class="prerequisites-list">
                                @foreach($availablePrerequisites as $prereqModule)
                                    <div class="form-check mb-2">
                                        <input type="checkbox"
                                               class="form-check-input"
                                               id="prereq_{{ $prereqModule->id }}"
                                               name="prerequisites[]"
                                               value="{{ $prereqModule->id }}"
                                               {{ in_array($prereqModule->id, old('prerequisites', $currentPrerequisites ?? [])) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="prereq_{{ $prereqModule->id }}">
                                            <strong>{{ $prereqModule->module_number }}:</strong> {{ $prereqModule->module_title }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                            @error('prerequisites')
                                <div class="text-danger small mt-2">{{ $message }}</div>
                            @enderror
                            @error('prerequisites.*')
                                <div class="text-danger small mt-2">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    @endif
                </div>

                <div class="cb-footer">
                    <a href="{{ route('courses.show', $course) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-1"></i>Cancel
                    </a>
                    <div class="btn-group-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Update Module
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function toggleAssessmentSettings() {
    const enabled = document.getElementById('require_final_assessment').checked;
    const settings = document.getElementById('assessmentSettings');
    if (enabled) {
        settings.classList.remove('d-none');
    } else {
        settings.classList.add('d-none');
    }
}

function toggleQuestionCount() {
    const mode = document.getElementById('assessment_question_mode').value;
    const wrapper = document.getElementById('questionCountWrapper');
    wrapper.style.display = mode === 'random_subset' ? '' : 'none';
}
</script>
@endpush

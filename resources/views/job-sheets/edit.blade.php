@extends('layouts.app')

@section('title', 'Edit Job Sheet')

@section('content')
<div class="content-area">
    <x-breadcrumb :items="[
        ['label' => 'Content', 'url' => route('content.management')],
        ['label' => $informationSheet->module->module_name, 'url' => route('courses.modules.show', [$informationSheet->module->course_id, $informationSheet->module])],
        ['label' => 'Edit Job Sheet'],
    ]" />

    <form action="{{ route('job-sheets.update', [$informationSheet, $jobSheet]) }}" method="POST" enctype="multipart/form-data" class="cb-builder-layout-form">
        @csrf
        @method('PUT')

        <div class="cb-builder-layout">
            {{-- MAIN CONTENT --}}
            <div class="cb-main">
                <div class="cb-header cb-header--job">
                    <h4><i class="fas fa-edit me-2"></i>Edit Job Sheet</h4>
                    <p>{{ $jobSheet->job_number }} &mdash; {{ $informationSheet->title }}</p>
                </div>

                <div class="cb-body">
                    {{-- Compact Settings --}}
                    <div class="cb-settings">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="cb-field-label">Job Number <span class="required">*</span></label>
                                <input type="text" class="form-control @error('job_number') is-invalid @enderror" name="job_number" value="{{ old('job_number', $jobSheet->job_number) }}" required>
                                @error('job_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-8 mb-3">
                                <label class="cb-field-label">Title <span class="required">*</span></label>
                                <input type="text" class="form-control @error('title') is-invalid @enderror" name="title" value="{{ old('title', $jobSheet->title) }}" required>
                                @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12 mb-3">
                                <label class="cb-field-label">Description <span class="optional">(optional)</span></label>
                                <textarea class="form-control @error('description') is-invalid @enderror" name="description" rows="2">{{ old('description', $jobSheet->description) }}</textarea>
                                @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="cb-field-label d-block"><i class="fas fa-random text-primary me-1"></i> Randomization</label>
                                <div class="form-check mt-2">
                                    <input type="checkbox" class="form-check-input" name="randomize_steps" id="randomize_steps" value="1"
                                           {{ old('randomize_steps', $jobSheet->randomize_steps) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="randomize_steps">Randomize steps order</label>
                                </div>
                                <small class="text-muted">Each student sees steps in a different order</small>
                            </div>
                        </div>
                    </div>

                    {{-- Document Attachment --}}
                    <div class="cb-section">
                        <div class="cb-section__title"><i class="fas fa-upload"></i> Document Attachment <span class="optional">(optional)</span></div>
                        @if($jobSheet->file_path)
                        <div class="cb-context-badge mb-3">
                            <i class="fas fa-file-alt"></i>
                            <span class="flex-grow-1">Current file: <strong>{{ $jobSheet->original_filename }}</strong></span>
                            <a href="{{ route('job-sheets.download', $jobSheet) }}" class="btn btn-sm btn-outline-primary ms-2">
                                <i class="fas fa-download me-1"></i>Download
                            </a>
                        </div>
                        @endif
                        <label class="cb-upload-area">
                            <input type="file" class="d-none" name="file"
                                   accept=".pdf,.xlsx,.xls,.doc,.docx,.ppt,.pptx"
                                   onchange="this.closest('.cb-upload-area').classList.add('has-file'); this.closest('.cb-upload-area').querySelector('.upload-name').textContent = this.files[0].name;">
                            <i class="fas fa-cloud-upload-alt d-block"></i>
                            <div class="cb-upload-area__text">
                                <strong>{{ $jobSheet->file_path ? 'Upload new file to replace' : 'Click to upload' }}</strong> or drag and drop<br>
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
                                @foreach($jobSheet->objectives_list as $objective)
                                <div class="cb-list-item">
                                    <i class="fas fa-grip-vertical cb-list-item__handle"></i>
                                    <input type="text" class="form-control form-control-sm" name="objectives[]" value="{{ $objective }}" required>
                                    <button type="button" class="cb-list-item__remove" onclick="DynamicForm.removeListItem(this)"><i class="fas fa-times"></i></button>
                                </div>
                                @endforeach
                            </div>
                            <button type="button" class="cb-add-btn mt-1" onclick="DynamicForm.addListItem('objectives-container', 'objectives[]', 'Enter objective', true)">
                                <i class="fas fa-plus"></i> Add
                            </button>
                        </div>
                        <div class="cb-detail-col">
                            <div class="cb-section__title"><i class="fas fa-wrench"></i> Tools Required</div>
                            <div id="tools-container">
                                @foreach($jobSheet->tools_required_list as $tool)
                                <div class="cb-list-item">
                                    <i class="fas fa-grip-vertical cb-list-item__handle"></i>
                                    <input type="text" class="form-control form-control-sm" name="tools_required[]" value="{{ $tool }}" required>
                                    <button type="button" class="cb-list-item__remove" onclick="DynamicForm.removeListItem(this)"><i class="fas fa-times"></i></button>
                                </div>
                                @endforeach
                            </div>
                            <button type="button" class="cb-add-btn mt-1" onclick="DynamicForm.addListItem('tools-container', 'tools_required[]', 'Enter tool', true)">
                                <i class="fas fa-plus"></i> Add
                            </button>
                        </div>
                        <div class="cb-detail-col">
                            <div class="cb-section__title"><i class="fas fa-shield-alt"></i> Safety</div>
                            <div id="safety-container">
                                @foreach($jobSheet->safety_requirements_list as $safety)
                                <div class="cb-list-item">
                                    <i class="fas fa-grip-vertical cb-list-item__handle"></i>
                                    <input type="text" class="form-control form-control-sm" name="safety_requirements[]" value="{{ $safety }}" required>
                                    <button type="button" class="cb-list-item__remove" onclick="DynamicForm.removeListItem(this)"><i class="fas fa-times"></i></button>
                                </div>
                                @endforeach
                            </div>
                            <button type="button" class="cb-add-btn mt-1" onclick="DynamicForm.addListItem('safety-container', 'safety_requirements[]', 'Enter requirement', true)">
                                <i class="fas fa-plus"></i> Add
                            </button>
                        </div>
                        <div class="cb-detail-col">
                            <div class="cb-section__title"><i class="fas fa-book"></i> References</div>
                            <div id="references-container">
                                @forelse($jobSheet->reference_materials_list as $ref)
                                <div class="cb-list-item">
                                    <i class="fas fa-grip-vertical cb-list-item__handle"></i>
                                    <input type="text" class="form-control form-control-sm" name="reference_materials[]" value="{{ $ref }}">
                                    <button type="button" class="cb-list-item__remove" onclick="DynamicForm.removeListItem(this)"><i class="fas fa-times"></i></button>
                                </div>
                                @empty
                                <div class="cb-list-item">
                                    <i class="fas fa-grip-vertical cb-list-item__handle"></i>
                                    <input type="text" class="form-control form-control-sm" name="reference_materials[]" placeholder="Enter reference">
                                    <button type="button" class="cb-list-item__remove" onclick="DynamicForm.removeListItem(this)"><i class="fas fa-times"></i></button>
                                </div>
                                @endforelse
                            </div>
                            <button type="button" class="cb-add-btn mt-1" onclick="DynamicForm.addListItem('references-container', 'reference_materials[]', 'Enter reference')">
                                <i class="fas fa-plus"></i> Add
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="cb-footer">
                    <a href="{{ route('job-sheets.show', $jobSheet) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Cancel
                    </a>
                    <div class="btn-group-footer">
                        <span class="cb-footer__hint d-none d-md-inline">All fields marked * are required</span>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Update Job Sheet
                        </button>
                    </div>
                </div>
            </div>

            {{-- RIGHT SIDEBAR --}}
            <div class="cb-sidebar">
                <div class="cb-sidebar__title">
                    <i class="fas fa-edit me-2"></i>Edit Guide
                </div>

                <div class="cb-sidebar__group">
                    <div class="cb-sidebar__group-label"><i class="fas fa-info-circle"></i> Current Info</div>
                    <div class="cb-sidebar__info">
                        <div style="font-size: 0.8rem; line-height: 1.8;">
                            <strong>Steps:</strong> {{ $jobSheet->steps->count() }}<br>
                            <strong>Objectives:</strong> {{ count($jobSheet->objectives_list) }}<br>
                            <strong>Tools:</strong> {{ count($jobSheet->tools_required_list) }}<br>
                            <strong>Safety:</strong> {{ count($jobSheet->safety_requirements_list) }}
                        </div>
                    </div>
                </div>

                <div class="cb-sidebar__info">
                    <div class="cb-sidebar__info-title"><i class="fas fa-lightbulb"></i> Tips</div>
                    Each step should have clear instructions and an expected outcome so students know what to aim for.
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

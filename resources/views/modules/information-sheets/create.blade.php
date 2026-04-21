@extends('layouts.app')

@section('title', 'Create Information Sheet - EPAS-E')

@section('content')
<div class="content-area">
    <x-breadcrumb :items="[
        ['label' => 'Courses', 'url' => route('content.management')],
        ['label' => $module->course->course_name],
        ['label' => 'Module ' . $module->module_number],
        ['label' => 'Create Information Sheet'],
    ]" />

    <div class="cb-container--simple">
        <form action="{{ route('information-sheets.store', $module) }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="cb-main">
                <div class="cb-header cb-header--info-sheet">
                    <h4><i class="fas fa-file-alt me-2"></i>Create Information Sheet</h4>
                    <p>Add a new information sheet to Module {{ $module->module_number }}</p>
                </div>

                <div class="cb-body">
                    {{-- Context --}}
                    <div class="cb-context-badge">
                        <i class="fas fa-book"></i>
                        <span>Module: <strong>{{ $module->module_number }} &mdash; {{ $module->module_name }}</strong></span>
                    </div>

                    {{-- Sheet Details --}}
                    <div class="cb-section">
                        <div class="cb-section__title"><i class="fas fa-pen"></i> Sheet Details</div>
                        <div class="cb-settings">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="cb-field-label">Sheet Number <span class="required">*</span></label>
                                    <input type="text" class="form-control @error('sheet_number') is-invalid @enderror"
                                           name="sheet_number" value="{{ old('sheet_number') }}"
                                           placeholder="e.g., 1.1, 1.2, 2.1" required>
                                    @error('sheet_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    <div class="cb-field-hint">Format: ModuleNumber.SheetNumber (e.g., 1.1)</div>
                                </div>
                                <div class="col-md-8 mb-3">
                                    <label class="cb-field-label">Title <span class="required">*</span></label>
                                    <input type="text" class="form-control @error('title') is-invalid @enderror"
                                           name="title" value="{{ old('title') }}"
                                           placeholder="e.g., Introduction to Electronics and Electricity" required>
                                    @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Document Upload --}}
                    <div class="cb-section">
                        <div class="cb-section__title"><i class="fas fa-upload"></i> Document Upload</div>
                        <div class="cb-upload-area">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <p>Click to upload or drag and drop</p>
                            <small>PDF, Word, Excel, PowerPoint (max 10MB)</small>
                            <input type="file" class="form-control mt-2 @error('file') is-invalid @enderror"
                                   name="file" accept=".pdf,.xlsx,.xls,.doc,.docx,.ppt,.pptx">
                            @error('file')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    {{-- Content --}}
                    <div class="cb-section">
                        <div class="cb-section__title"><i class="fas fa-align-left"></i> Content</div>
                        <div class="mb-3">
                            <x-rich-editor
                                name="content"
                                label="Content"
                                placeholder="Enter the main content for this information sheet..."
                                :value="old('content')"
                                toolbar="full"
                                :height="300"
                            />
                            @error('content')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    {{-- Settings --}}
                    <div class="cb-section">
                        <div class="cb-section__title"><i class="fas fa-cog"></i> Settings</div>
                        <div class="cb-settings">
                            <div class="row">
                                <div class="col-md-4">
                                    <label class="cb-field-label">Display Order</label>
                                    <input type="number" class="form-control @error('order') is-invalid @enderror"
                                           name="order" value="{{ old('order', $nextOrder) }}" min="0">
                                    @error('order')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    <div class="cb-field-hint">Lower numbers appear first</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="cb-footer">
                    <a href="{{ route('content.management') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-1"></i>Cancel
                    </a>
                    <div class="btn-group-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Create Information Sheet
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

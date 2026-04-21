@extends('layouts.app')

@section('title', 'Create Topic - EPAS-E')

@section('content')
<div class="content-area">
    <x-breadcrumb :items="[
        ['label' => 'Courses', 'url' => route('content.management')],
        ['label' => $informationSheet->module->course->course_name],
        ['label' => 'Module ' . $informationSheet->module->module_number],
        ['label' => 'Info Sheet ' . $informationSheet->sheet_number],
        ['label' => 'Create Topic'],
    ]" />

    <div class="cb-container--simple">
        <form action="{{ route('topics.store', $informationSheet->id) }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="cb-main">
                <div class="cb-header cb-header--topic">
                    <h4><i class="fas fa-bookmark me-2"></i>Create New Topic</h4>
                    <p>Add a topic to Information Sheet {{ $informationSheet->sheet_number }}</p>
                </div>

                <div class="cb-body">
                    {{-- Context --}}
                    <div class="cb-context-badge">
                        <i class="fas fa-file-alt"></i>
                        <span>Info Sheet: <strong>{{ $informationSheet->sheet_number }} &mdash; {{ $informationSheet->title }}</strong></span>
                    </div>

                    {{-- Topic Details --}}
                    <div class="cb-section">
                        <div class="cb-section__title"><i class="fas fa-pen"></i> Topic Details</div>
                        <div class="cb-settings">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="cb-field-label">Topic Number <span class="required">*</span></label>
                                    <input type="text" class="form-control @error('topic_number') is-invalid @enderror"
                                           name="topic_number" value="{{ old('topic_number') }}"
                                           placeholder="e.g., 1, 2, 3 or 1.1.1" required>
                                    @error('topic_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-8 mb-3">
                                    <label class="cb-field-label">Title <span class="required">*</span></label>
                                    <input type="text" class="form-control @error('title') is-invalid @enderror"
                                           name="title" value="{{ old('title') }}"
                                           placeholder="e.g., Scientists Who Contributed to Electricity" required>
                                    @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Load rich editor CSS/JS for block editor --}}
                    <div style="display:none;">
                        <x-rich-editor name="_block_editor_init" />
                    </div>

                    {{-- Block-based Content Editor --}}
                    @include('components.block-editor', ['existingBlocks' => []])

                    @error('blocks')<div class="text-danger small mt-1">{{ $message }}</div>@enderror

                    {{-- Settings --}}
                    <div class="cb-section">
                        <div class="cb-section__title"><i class="fas fa-cog"></i> Settings</div>
                        <div class="cb-settings">
                            <div class="row">
                                <div class="col-md-4">
                                    <label class="cb-field-label">Display Order</label>
                                    <input type="number" class="form-control @error('order') is-invalid @enderror"
                                           name="order" value="{{ old('order', $nextOrder ?? 0) }}" min="0">
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
                            <i class="fas fa-save me-1"></i>Create Topic
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection

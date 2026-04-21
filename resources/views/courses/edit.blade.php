@extends('layouts.app')

@section('title', 'Edit Course - EPAS-E')

@section('content')
<div class="content-area">
    <x-breadcrumb :items="[
        ['label' => 'Courses', 'url' => route('content.management')],
        ['label' => 'Edit: ' . $course->course_name],
    ]" />

    <div class="cb-container--simple">
        <form method="POST" action="{{ route('courses.update', $course) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="cb-main">
                <div class="cb-header cb-header--course">
                    <h4><i class="fas fa-edit me-2"></i>Edit Course</h4>
                    <p>{{ $course->course_name }}</p>
                </div>

                <div class="cb-body">
                    <div class="cb-section">
                        <div class="cb-section__title"><i class="fas fa-pen"></i> Course Details</div>
                        <div class="cb-settings">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="cb-field-label">Course Name <span class="required">*</span></label>
                                    <input type="text" class="form-control @error('course_name') is-invalid @enderror"
                                        name="course_name" value="{{ old('course_name', $course->course_name) }}" required>
                                    @error('course_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="cb-field-label">Course Code <span class="required">*</span></label>
                                    <input type="text" class="form-control @error('course_code') is-invalid @enderror"
                                        name="course_code" value="{{ old('course_code', $course->course_code) }}" required>
                                    @error('course_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>

                            {{-- Category Selection --}}
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="cb-field-label">Category <span class="optional">(optional)</span></label>
                                    <div class="category-select-wrapper">
                                        <select class="form-select @error('category_id') is-invalid @enderror"
                                            name="category_id" id="category_select">
                                            <option value="">-- Select a Category --</option>
                                            @foreach($categories as $category)
                                            <option value="{{ $category->id }}"
                                                data-color="{{ $category->color }}"
                                                data-icon="{{ $category->icon }}"
                                                {{ old('category_id', $course->category_id) == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                            @endforeach
                                        </select>
                                        @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="cb-field-hint">Category determines the course card color theme.</div>

                                    {{-- Add New Category --}}
                                    <div class="mt-2">
                                        <button type="button" class="btn btn-sm btn-outline-secondary" id="toggleNewCategory">
                                            <i class="fas fa-plus me-1"></i>Add New Category
                                        </button>
                                        <div id="newCategoryInput" class="mt-2" style="display: none;">
                                            <div class="input-group">
                                                <input type="text" class="form-control" name="new_category" id="new_category"
                                                    placeholder="Enter new category name...">
                                                <button type="button" class="btn btn-outline-danger" id="cancelNewCategory">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                            <small class="text-muted">A color will be automatically assigned.</small>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="cb-field-label">Sector <span class="optional">(optional)</span></label>
                                    <input type="text" class="form-control @error('sector') is-invalid @enderror"
                                        name="sector" value="{{ old('sector', $course->sector) }}" placeholder="e.g., Electronics Sector">
                                    @error('sector')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>

                            {{-- Thumbnail Upload --}}
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="cb-field-label">Course Thumbnail <span class="optional">(optional)</span></label>
                                    <div class="thumbnail-upload-wrapper">
                                        <div class="thumbnail-preview {{ $course->thumbnail ? 'has-image' : '' }}" id="thumbnailPreview">
                                            @if($course->thumbnail)
                                            <img src="{{ asset('storage/' . $course->thumbnail) }}" alt="Current thumbnail">
                                            @else
                                            <i class="fas fa-image"></i>
                                            <span>Click to upload image</span>
                                            @endif
                                        </div>
                                        <input type="file" class="form-control d-none @error('thumbnail') is-invalid @enderror"
                                            name="thumbnail" id="thumbnail_input" accept="image/jpeg,image/png,image/jpg,image/gif,image/webp">
                                        @error('thumbnail')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                    </div>
                                    @if($course->thumbnail)
                                    <div class="form-check mt-2">
                                        <input class="form-check-input" type="checkbox" name="remove_thumbnail" value="1" id="remove_thumbnail">
                                        <label class="form-check-label text-danger" for="remove_thumbnail">
                                            <i class="fas fa-trash me-1"></i>Remove current thumbnail
                                        </label>
                                    </div>
                                    @endif
                                    <div class="cb-field-hint">Recommended: 800x450px (16:9 ratio). Max 2MB.</div>
                                </div>

                                @if(auth()->user()->role === \App\Constants\Roles::ADMIN && isset($instructors))
                                <div class="col-md-6 mb-3">
                                    <label class="cb-field-label">Assigned Instructor <span class="optional">(optional)</span></label>
                                    <select class="form-select @error('instructor_id') is-invalid @enderror"
                                        name="instructor_id" id="instructor_select">
                                        <option value="">-- No Instructor Assigned --</option>
                                        @foreach($instructors->sortBy(fn($i) => $i->last_name . $i->first_name) as $instructor)
                                        <option value="{{ $instructor->id }}" {{ old('instructor_id', $course->instructor_id) == $instructor->id ? 'selected' : '' }}>
                                            {{ $instructor->last_name }}, {{ $instructor->first_name }} ({{ $instructor->email }})
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('instructor_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    <div class="cb-field-hint">Only the assigned instructor can edit this course and its modules.</div>
                                </div>
                                @endif
                            </div>

                            @php $currentSections = $course->target_sections ? explode(',', $course->target_sections) : []; @endphp
                            <div class="mb-3">
                                <label class="cb-field-label">Target Sections <span class="optional">(optional — leave empty for all sections)</span></label>
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach($sections as $sec)
                                    <div class="form-check">
                                        <input class="form-check-input section-checkbox" type="checkbox" value="{{ $sec }}" id="sec_{{ $sec }}"
                                            {{ in_array($sec, old('target_sections') ? explode(',', old('target_sections')) : $currentSections) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="sec_{{ $sec }}">{{ $sec }}</label>
                                    </div>
                                    @endforeach
                                </div>
                                <input type="hidden" name="target_sections" id="target_sections_input" value="{{ old('target_sections', $course->target_sections) }}">
                                <div class="cb-field-hint">Select which sections can see this course. Leave all unchecked for all sections.</div>
                            </div>

                            <div class="mb-3">
                                <label class="cb-field-label">Description <span class="optional">(optional)</span></label>
                                <textarea class="form-control @error('description') is-invalid @enderror"
                                    name="description" rows="4" placeholder="Enter course description...">{{ old('description', $course->description) }}</textarea>
                                @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    {{-- Schedule Section --}}
                    <div class="cb-section">
                        <div class="cb-section__title"><i class="fas fa-calendar-alt"></i> Schedule & Duration</div>
                        <div class="cb-settings">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="cb-field-label">Start Date <span class="optional">(optional)</span></label>
                                    <input type="date" class="form-control @error('start_date') is-invalid @enderror"
                                        name="start_date" value="{{ old('start_date', $course->start_date?->format('Y-m-d')) }}">
                                    @error('start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="cb-field-label">End Date <span class="optional">(optional)</span></label>
                                    <input type="date" class="form-control @error('end_date') is-invalid @enderror"
                                        name="end_date" value="{{ old('end_date', $course->end_date?->format('Y-m-d')) }}">
                                    @error('end_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="cb-field-label">Total Hours <span class="optional">(optional)</span></label>
                                    <div class="input-group">
                                        <input type="number" class="form-control @error('duration_hours') is-invalid @enderror"
                                            name="duration_hours" value="{{ old('duration_hours', $course->duration_hours) }}" min="1" placeholder="e.g., 40">
                                        <span class="input-group-text">hours</span>
                                    </div>
                                    @error('duration_hours')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="cb-field-label">Schedule Days <span class="optional">(optional)</span></label>
                                    <div class="schedule-days-wrapper">
                                        @php
                                        $days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
                                        $selectedDays = old('schedule_days', $course->schedule_days) ? explode(',', old('schedule_days', $course->schedule_days)) : [];
                                        @endphp
                                        <div class="d-flex flex-wrap gap-2">
                                            @foreach($days as $day)
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input day-checkbox" type="checkbox" value="{{ $day }}"
                                                    id="day_{{ $day }}" {{ in_array($day, $selectedDays) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="day_{{ $day }}">{{ $day }}</label>
                                            </div>
                                            @endforeach
                                        </div>
                                        <input type="hidden" name="schedule_days" id="schedule_days_input" value="{{ old('schedule_days', $course->schedule_days) }}">
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="cb-field-label">Time Start <span class="optional">(optional)</span></label>
                                    <input type="time" class="form-control @error('schedule_time_start') is-invalid @enderror"
                                        name="schedule_time_start" value="{{ old('schedule_time_start', $course->schedule_time_start ? \Carbon\Carbon::parse($course->schedule_time_start)->format('H:i') : '') }}">
                                    @error('schedule_time_start')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="cb-field-label">Time End <span class="optional">(optional)</span></label>
                                    <input type="time" class="form-control @error('schedule_time_end') is-invalid @enderror"
                                        name="schedule_time_end" value="{{ old('schedule_time_end', $course->schedule_time_end ? \Carbon\Carbon::parse($course->schedule_time_end)->format('H:i') : '') }}">
                                    @error('schedule_time_end')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="cb-section">
                        <div class="cb-section__title"><i class="fas fa-cog"></i> Settings</div>
                        <div class="cb-settings">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1"
                                    {{ old('is_active', $course->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">Active Course</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="cb-footer">
                    <a href="{{ route('courses.show', $course) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-1"></i>Cancel
                    </a>
                    <div class="btn-group-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Update Course
                        </button>
                    </div>
                </div>
            </div>
        </form>
        @if(in_array(auth()->user()->role, [\App\Constants\Roles::ADMIN, \App\Constants\Roles::INSTRUCTOR]) && $course->modules->isEmpty())
        <div class="cb-section">
            <div class="cb-section__title" style="color: #dc3545;"><i class="fas fa-exclamation-triangle"></i> Danger Zone</div>
            <div class="cb-settings" style="border: 1px solid #fecaca; background: #fef2f2;">
                <p class="mb-2" style="font-size: 0.85rem;">This course has no modules. You can safely delete it if needed.</p>
                <form action="{{ route('courses.destroy', $course) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this course? This action cannot be undone.')">
                        <i class="fas fa-trash me-1"></i>Delete Course
                    </button>
                </form>
            </div>
        </div>
        @endif
    </div>
</div>

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
<style>
    /* Category Select with Color Indicator */
    .category-option {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .category-color-dot {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        flex-shrink: 0;
    }

    /* Thumbnail Upload */
    .thumbnail-upload-wrapper {
        position: relative;
    }

    .thumbnail-preview {
        width: 100%;
        height: 150px;
        border: 2px dashed var(--border);
        border-radius: var(--border-radius);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        cursor: pointer;
        transition: all 0.3s ease;
        background: var(--surface);
        color: var(--text-muted);
        overflow: hidden;
    }

    .thumbnail-preview:hover {
        border-color: var(--primary);
        color: var(--primary);
    }

    .thumbnail-preview i {
        font-size: 2rem;
    }

    .thumbnail-preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .thumbnail-preview.has-image {
        border-style: solid;
    }

    .thumbnail-preview.has-image span,
    .thumbnail-preview.has-image i {
        display: none;
    }

    /* Tom Select - light/dark mode support */
    .ts-wrapper .ts-control {
        background-color: var(--cb-surface, #fff);
        border-color: var(--cb-border, #dee2e6);
        color: var(--cb-text, #212529);
    }

    .ts-wrapper .ts-dropdown {
        background-color: var(--cb-surface, #fff);
        border-color: var(--cb-border, #dee2e6);
        color: var(--cb-text, #212529);
    }

    .ts-wrapper .ts-dropdown .option {
        color: var(--cb-text, #212529);
    }

    .ts-wrapper .ts-dropdown .option:hover,
    .ts-wrapper .ts-dropdown .option.active {
        background-color: var(--cb-surface-alt, #f8f9fa);
        color: var(--cb-text, #212529);
    }

    /* Dark mode */
    .dark-mode .ts-wrapper .ts-control {
        background-color: var(--card-bg);
        border-color: var(--border);
        color: var(--text-primary);
    }

    .dark-mode .ts-wrapper .ts-dropdown {
        background-color: var(--card-bg);
        border-color: var(--border);
        color: var(--text-primary);
    }

    .dark-mode .ts-wrapper .ts-dropdown .option {
        color: var(--text-primary);
    }

    .dark-mode .ts-wrapper .ts-dropdown .option:hover,
    .dark-mode .ts-wrapper .ts-dropdown .option.active {
        background-color: var(--light-gray);
        color: var(--text-primary);
    }

    .dark-mode .thumbnail-preview {
        background: var(--card-bg);
        border-color: var(--border);
    }
</style>
@endpush
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Tom Select for instructor dropdown
        if (document.getElementById('instructor_select')) {
            new TomSelect('#instructor_select', {
                mode: 'single',
                allowEmptyOption: true,
                placeholder: 'Search instructor by name or email...',
                sortField: {
                    field: 'text',
                    direction: 'asc'
                },
                maxOptions: 500,
            });
        }

        // Tom Select for category dropdown with color dots
        if (document.getElementById('category_select')) {
            new TomSelect('#category_select', {
                mode: 'single',
                allowEmptyOption: true,
                placeholder: 'Select a category...',
                render: {
                    option: function(data, escape) {
                        const color = data.color || '#6366f1';
                        const icon = data.icon || 'fas fa-folder';
                        return '<div class="category-option">' +
                            '<span class="category-color-dot" style="background-color: ' + escape(color) + '"></span>' +
                            '<i class="' + escape(icon) + ' me-1" style="color: ' + escape(color) + '"></i>' +
                            '<span>' + escape(data.text) + '</span>' +
                            '</div>';
                    },
                    item: function(data, escape) {
                        const color = data.color || '#6366f1';
                        const icon = data.icon || 'fas fa-folder';
                        return '<div class="category-option">' +
                            '<span class="category-color-dot" style="background-color: ' + escape(color) + '"></span>' +
                            '<i class="' + escape(icon) + ' me-1" style="color: ' + escape(color) + '"></i>' +
                            '<span>' + escape(data.text) + '</span>' +
                            '</div>';
                    }
                }
            });
        }

        // Sync section checkboxes with hidden input
        const sectionCheckboxes = document.querySelectorAll('.section-checkbox');
        const hiddenSectionInput = document.getElementById('target_sections_input');

        function syncSections() {
            const checked = [...sectionCheckboxes].filter(cb => cb.checked).map(cb => cb.value);
            hiddenSectionInput.value = checked.join(',');
        }
        sectionCheckboxes.forEach(cb => cb.addEventListener('change', syncSections));

        // Sync day checkboxes with hidden input
        const dayCheckboxes = document.querySelectorAll('.day-checkbox');
        const hiddenDaysInput = document.getElementById('schedule_days_input');

        function syncDays() {
            const checked = [...dayCheckboxes].filter(cb => cb.checked).map(cb => cb.value);
            hiddenDaysInput.value = checked.join(',');
        }
        dayCheckboxes.forEach(cb => cb.addEventListener('change', syncDays));

        // Toggle new category input
        const toggleBtn = document.getElementById('toggleNewCategory');
        const newCategoryInput = document.getElementById('newCategoryInput');
        const cancelBtn = document.getElementById('cancelNewCategory');
        const categorySelect = document.getElementById('category_select');

        toggleBtn.addEventListener('click', function() {
            newCategoryInput.style.display = 'block';
            toggleBtn.style.display = 'none';
            if (categorySelect.tomselect) {
                categorySelect.tomselect.disable();
            }
        });

        cancelBtn.addEventListener('click', function() {
            newCategoryInput.style.display = 'none';
            toggleBtn.style.display = 'inline-flex';
            document.getElementById('new_category').value = '';
            if (categorySelect.tomselect) {
                categorySelect.tomselect.enable();
            }
        });

        // Thumbnail preview
        const thumbnailInput = document.getElementById('thumbnail_input');
        const thumbnailPreview = document.getElementById('thumbnailPreview');

        thumbnailPreview.addEventListener('click', function() {
            thumbnailInput.click();
        });

        thumbnailInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    thumbnailPreview.innerHTML = '<img src="' + e.target.result + '" alt="Preview">';
                    thumbnailPreview.classList.add('has-image');
                };
                reader.readAsDataURL(file);
            }
        });
    });
</script>
@endpush
@endsection
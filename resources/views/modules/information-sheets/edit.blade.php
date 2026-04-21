@extends('layouts.app')

@section('title', 'Edit Information Sheet - EPAS-E')

@section('content')
<div class="content-area">
    <x-breadcrumb :items="[
        ['label' => 'Courses', 'url' => route('content.management')],
        ['label' => $module->course->course_name ?? ''],
        ['label' => 'Module ' . $module->module_number],
        ['label' => 'Edit: ' . $informationSheet->sheet_number],
    ]" />

    <div class="cb-container--simple">
        {{-- Edit Form --}}
        <form method="POST" action="{{ route('information-sheets.update', [$module, $informationSheet]) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="cb-main">
                <div class="cb-header cb-header--info-sheet">
                    <h4><i class="fas fa-edit me-2"></i>Edit Information Sheet</h4>
                    <p>{{ $informationSheet->sheet_number }}: {{ $informationSheet->title }}</p>
                </div>

                <div class="cb-body">
                    {{-- Sheet Details --}}
                    <div class="cb-section">
                        <div class="cb-section__title"><i class="fas fa-pen"></i> Sheet Details</div>
                        <div class="cb-settings">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="cb-field-label">Sheet Number <span class="required">*</span></label>
                                    <input type="text" class="form-control @error('sheet_number') is-invalid @enderror"
                                           name="sheet_number" value="{{ old('sheet_number', $informationSheet->sheet_number) }}" required>
                                    @error('sheet_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-8 mb-3">
                                    <label class="cb-field-label">Title <span class="required">*</span></label>
                                    <input type="text" class="form-control @error('title') is-invalid @enderror"
                                           name="title" value="{{ old('title', $informationSheet->title) }}" required>
                                    @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Current File --}}
                    <div class="cb-section">
                        <div class="cb-section__title"><i class="fas fa-upload"></i> Document</div>
                        @if($informationSheet->file_path)
                        <div class="cb-context-badge" style="margin-bottom: 1rem;">
                            <i class="fas fa-file-alt"></i>
                            <span class="flex-grow-1">Current file: <strong>{{ $informationSheet->original_filename }}</strong></span>
                            <a href="{{ route('information-sheets.download', [$module, $informationSheet]) }}"
                               class="btn btn-sm btn-outline-primary ms-2">
                                <i class="fas fa-download me-1"></i>Download
                            </a>
                        </div>
                        @endif
                        <div class="cb-upload-area">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <p>{{ $informationSheet->file_path ? 'Upload a new file to replace the current one' : 'Click to upload or drag and drop' }}</p>
                            <small>PDF, Word, Excel, PowerPoint (max 10MB)</small>
                            <input type="file" class="form-control mt-2 @error('file') is-invalid @enderror"
                                   name="file" accept=".pdf,.xlsx,.xls,.doc,.docx,.ppt,.pptx">
                            @error('file')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    {{-- Content --}}
                    <div class="cb-section">
                        <div class="cb-section__title"><i class="fas fa-align-left"></i> Content</div>
                        <div>
                            <x-rich-editor
                                name="content"
                                label="Content"
                                placeholder="Enter the main content for this information sheet..."
                                :value="old('content', $informationSheet->content)"
                                toolbar="full"
                                :height="300"
                            />
                            @error('content')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                <div class="cb-footer">
                    <a href="{{ route('courses.modules.show', [$module->course_id, $module]) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-1"></i>Cancel
                    </a>
                    <div class="btn-group-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Update Information Sheet
                        </button>
                    </div>
                </div>
            </div>
        </form>

        {{-- Content Items Management --}}
        <div class="cb-main" style="margin-top: 1.5rem;">
            <div class="cb-header" style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); color: #fff;">
                <h4><i class="fas fa-cubes me-2"></i>Content Items</h4>
                <p>Information Sheet {{ $informationSheet->sheet_number }}</p>
            </div>

            <div class="cb-body">
                {{-- Quick Stats --}}
                <div class="row mb-4">
                    <div class="col-md-3 col-6">
                        <div class="text-center p-3" style="background: var(--cb-surface-alt); border-radius: var(--cb-radius-sm); border: 1px solid var(--cb-border);">
                            <div class="h4 mb-1 text-primary">{{ $informationSheet->topics->count() }}</div>
                            <small style="color: var(--cb-text-hint);">Topics</small>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="text-center p-3" style="background: var(--cb-surface-alt); border-radius: var(--cb-radius-sm); border: 1px solid var(--cb-border);">
                            <div class="h4 mb-1 text-warning">{{ $informationSheet->selfChecks->count() }}</div>
                            <small style="color: var(--cb-text-hint);">Self-Checks</small>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="text-center p-3" style="background: var(--cb-surface-alt); border-radius: var(--cb-radius-sm); border: 1px solid var(--cb-border);">
                            <div class="h4 mb-1 text-success">{{ $informationSheet->taskSheets->count() }}</div>
                            <small style="color: var(--cb-text-hint);">Task Sheets</small>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="text-center p-3" style="background: var(--cb-surface-alt); border-radius: var(--cb-radius-sm); border: 1px solid var(--cb-border);">
                            <div class="h4 mb-1 text-info">{{ $informationSheet->jobSheets->count() }}</div>
                            <small style="color: var(--cb-text-hint);">Job Sheets</small>
                        </div>
                    </div>
                </div>

                {{-- Add Content Buttons --}}
                <div class="cb-section">
                    <div class="cb-section__title"><i class="fas fa-plus-circle"></i> Add Content</div>
                    <div class="d-flex flex-wrap gap-2">
                        <button type="button" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-file-alt me-1"></i>Add Topic
                        </button>
                        <button type="button" class="btn btn-outline-warning btn-sm">
                            <i class="fas fa-question-circle me-1"></i>Add Self-Check
                        </button>
                        <button type="button" class="btn btn-outline-success btn-sm">
                            <i class="fas fa-tasks me-1"></i>Add Task Sheet
                        </button>
                        <button type="button" class="btn btn-outline-info btn-sm">
                            <i class="fas fa-briefcase me-1"></i>Add Job Sheet
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-pencil-alt me-1"></i>Add Homework
                        </button>
                        <button type="button" class="btn btn-outline-dark btn-sm">
                            <i class="fas fa-star me-1"></i>Add Performance Criteria
                        </button>
                        <button type="button" class="btn btn-outline-danger btn-sm">
                            <i class="fas fa-check-square me-1"></i>Add Check List
                        </button>
                    </div>
                </div>

                {{-- Existing Content Items --}}
                <div class="cb-section">
                    <div class="cb-section__title"><i class="fas fa-list"></i> Existing Content</div>
                    @if($informationSheet->topics->count() > 0 || $informationSheet->selfChecks->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Title/Number</th>
                                    <th>Order</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($informationSheet->topics as $topic)
                                <tr>
                                    <td><span class="badge bg-primary">Topic</span></td>
                                    <td>{{ $topic->title }}</td>
                                    <td>{{ $topic->order }}</td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-primary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-outline-danger" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                                @foreach($informationSheet->selfChecks as $selfCheck)
                                <tr>
                                    <td><span class="badge bg-warning text-dark">Self-Check</span></td>
                                    <td>Self Check {{ $selfCheck->check_number }}</td>
                                    <td>{{ $selfCheck->order }}</td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-primary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-outline-danger" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="cb-empty-state">
                        <i class="fas fa-inbox"></i>
                        <p>No content items created yet. Use the buttons above to add topics, self-checks, and other content.</p>
                    </div>
                    @endif
                </div>

                {{-- Danger Zone --}}
                @if($informationSheet->topics->isEmpty() && $informationSheet->selfChecks->isEmpty())
                <div class="cb-section">
                    <div class="cb-section__title" style="color: #dc3545;"><i class="fas fa-exclamation-triangle"></i> Danger Zone</div>
                    <div class="cb-settings" style="border: 1px solid #fecaca; background: #fef2f2;">
                        <p class="mb-2" style="font-size: 0.85rem;">This information sheet has no topics or self-checks. You can delete it if needed.</p>
                        <form action="{{ route('information-sheets.destroy', [$module, $informationSheet]) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this information sheet? This action cannot be undone.')">
                                <i class="fas fa-trash me-1"></i>Delete Information Sheet
                            </button>
                        </form>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

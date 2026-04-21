@extends('layouts.app')

@section('title', 'Edit Document Assessment')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet">
@endpush

@section('content')
<div class="content-area">
    <x-breadcrumb :items="[
        ['label' => 'Content', 'url' => route('content.management')],
        ['label' => $assessment->title, 'url' => route('document-assessments.show', $assessment)],
        ['label' => 'Edit'],
    ]" />

    <form action="{{ route('document-assessments.update', [$informationSheet, $assessment]) }}" method="POST" enctype="multipart/form-data" id="docAssessmentForm">
        @csrf
        @method('PUT')
        <input type="hidden" name="assessment_number" value="{{ $assessment->assessment_number }}">
        <input type="hidden" name="document_content" id="documentContentInput">

        <div class="cb-container--simple">
            <div class="cb-main">
                <div class="cb-header cb-header--criteria">
                    <h4><i class="fas fa-edit me-2"></i>Edit Document Assessment</h4>
                    <p>{{ $assessment->title }}</p>
                </div>

                <div class="cb-body">
                    {{-- Basic Fields --}}
                    <div class="cb-section">
                        <div class="cb-section__title"><i class="fas fa-pen"></i> Assessment Details</div>
                        <div class="cb-settings">
                            <div class="row">
                                <div class="col-md-8 mb-3">
                                    <label class="cb-field-label">Title <span class="required">*</span></label>
                                    <input type="text" class="form-control @error('title') is-invalid @enderror"
                                           name="title" value="{{ old('title', $assessment->title) }}" required>
                                    @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="cb-field-label">Max Points <span class="required">*</span></label>
                                    <input type="number" class="form-control @error('max_points') is-invalid @enderror"
                                           name="max_points" value="{{ old('max_points', $assessment->max_points) }}" min="1" required>
                                    @error('max_points')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="cb-field-label">Time Limit (min) <span class="optional">(optional)</span></label>
                                    <input type="number" class="form-control @error('time_limit') is-invalid @enderror"
                                           name="time_limit" value="{{ old('time_limit', $assessment->time_limit) }}" placeholder="No limit" min="1">
                                    @error('time_limit')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="cb-field-label">Deadline <span class="optional">(optional)</span></label>
                                    <input type="datetime-local" class="form-control @error('due_date') is-invalid @enderror"
                                           name="due_date" value="{{ old('due_date', $assessment->due_date ? $assessment->due_date->format('Y-m-d\TH:i') : '') }}">
                                    @error('due_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    <small class="text-muted">Submissions blocked after this date</small>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="cb-field-label">Instructions <span class="required">*</span></label>
                                <textarea class="form-control @error('instructions') is-invalid @enderror"
                                          name="instructions" rows="2" required>{{ old('instructions', $assessment->instructions) }}</textarea>
                                @error('instructions')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="mb-0">
                                <label class="cb-field-label">Description <span class="optional">(optional)</span></label>
                                <textarea class="form-control @error('description') is-invalid @enderror"
                                          name="description" rows="2">{{ old('description', $assessment->description) }}</textarea>
                                @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    {{-- Current File + Upload New --}}
                    <div class="cb-section">
                        <div class="cb-section__title"><i class="fas fa-file"></i> Document</div>
                        <div class="cb-settings">
                            @if($assessment->file_path)
                            <div class="cb-context-badge mb-3">
                                <i class="fas fa-file-alt"></i>
                                <span class="flex-grow-1">Current file: <strong>{{ $assessment->original_filename }}</strong> ({{ strtoupper($assessment->file_type) }})</span>
                                <a href="{{ route('document-assessments.download', $assessment) }}" class="btn btn-sm btn-outline-primary ms-2">
                                    <i class="fas fa-download me-1"></i>Download
                                </a>
                            </div>
                            @endif

                            <label class="cb-upload-area">
                                <input type="file" class="d-none" name="document" accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx"
                                       onchange="handleDocumentUpload(this)">
                                <i class="fas fa-cloud-upload-alt d-block"></i>
                                <div class="cb-upload-area__text">
                                    <strong>{{ $assessment->file_path ? 'Upload new file to replace' : 'Click to upload' }}</strong> or drag and drop<br>
                                    <small>DOCX, PPTX, XLSX, PDF &mdash; max 10MB</small>
                                </div>
                                <span class="upload-name" id="uploadFileName"></span>
                            </label>
                            @error('document')<div class="text-danger small mt-1">{{ $message }}</div>@enderror

                            <div class="alert alert-warning d-none mt-3 mb-0" id="pdfWarning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>PDF files are view-only.</strong> The document content cannot be edited in the browser.
                            </div>
                        </div>
                    </div>

                    {{-- Document Editor --}}
                    <div class="cb-section {{ $assessment->document_content ? '' : 'd-none' }}" id="editorSection">
                        <div class="cb-section__title"><i class="fas fa-edit"></i> Document Content (Editable)</div>
                        <div class="cb-settings">
                            <p class="text-muted small mb-2">
                                <i class="fas fa-info-circle me-1"></i>Review and edit the document content. Changes will be saved when you update.
                            </p>
                            <div id="quill-editor" style="min-height: 400px;">{!! $assessment->document_content !!}</div>
                        </div>
                    </div>

                    <div class="d-none text-center py-4" id="convertingSpinner">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="text-muted mt-2">Converting document...</p>
                    </div>
                </div>

                <div class="cb-footer">
                    <a href="{{ route('document-assessments.show', $assessment) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-1"></i>Cancel
                    </a>
                    <div class="btn-group-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Update Assessment
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
<script>
let quill = null;

function initQuill() {
    if (quill) return;
    quill = new Quill('#quill-editor', {
        theme: 'snow',
        modules: {
            toolbar: [
                [{ 'header': [1, 2, 3, false] }],
                ['bold', 'italic', 'underline'],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                ['blockquote'],
                ['clean']
            ]
        }
    });
}

// Initialize Quill if there's existing content
@if($assessment->document_content)
document.addEventListener('DOMContentLoaded', function() {
    initQuill();
});
@endif

async function handleDocumentUpload(input) {
    const file = input.files[0];
    if (!file) return;

    const ext = file.name.split('.').pop().toLowerCase();
    const uploadArea = input.closest('.cb-upload-area');
    const fileName = document.getElementById('uploadFileName');

    uploadArea.classList.add('has-file');
    fileName.textContent = file.name;

    document.getElementById('pdfWarning').classList.add('d-none');
    document.getElementById('convertingSpinner').classList.add('d-none');

    if (['docx', 'pptx', 'doc', 'ppt', 'pdf', 'xlsx', 'xls'].includes(ext)) {
        document.getElementById('convertingSpinner').classList.remove('d-none');

        try {
            const formData = new FormData();
            formData.append('document', file);

            const response = await fetch("{{ route('document-assessments.convert') }}", {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: formData,
            });

            const data = await response.json();
            document.getElementById('convertingSpinner').classList.add('d-none');

            if (data.success && data.html) {
                initQuill();
                document.getElementById('editorSection').classList.remove('d-none');
                quill.root.innerHTML = data.html;
            }
        } catch (err) {
            document.getElementById('convertingSpinner').classList.add('d-none');
            console.error('Conversion failed:', err);
        }
    }
}

document.getElementById('docAssessmentForm').addEventListener('submit', function() {
    if (quill) {
        document.getElementById('documentContentInput').value = quill.root.innerHTML;
    }
});
</script>
@endpush

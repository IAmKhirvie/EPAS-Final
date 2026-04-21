@extends('layouts.app')

@section('title', 'Create Document Assessment')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet">
@endpush

@section('content')
<div class="content-area">
    <x-breadcrumb :items="[
        ['label' => 'Content', 'url' => route('content.management')],
        ['label' => 'New Document Assessment'],
    ]" />

    <form action="{{ route('document-assessments.store', $informationSheet) }}" method="POST" enctype="multipart/form-data" id="docAssessmentForm">
        @csrf
        <input type="hidden" name="assessment_number" value="DA-{{ now()->timestamp }}">
        <input type="hidden" name="document_content" id="documentContentInput">

        <div class="cb-builder-layout">
            {{-- MAIN CONTENT --}}
            <div class="cb-main">
                <div class="cb-header cb-header--criteria">
                    <h4><i class="fas fa-file-word me-2"></i>New Document Assessment</h4>
                    <p>For: {{ $informationSheet->title }}</p>
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
                                           name="title" value="{{ old('title') }}" placeholder="e.g., Module 1 Document Review" required>
                                    @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="cb-field-label">Max Points <span class="required">*</span></label>
                                    <input type="number" class="form-control @error('max_points') is-invalid @enderror"
                                           name="max_points" value="{{ old('max_points', 100) }}" min="1" required>
                                    @error('max_points')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="cb-field-label">Time Limit (min) <span class="optional">(optional)</span></label>
                                    <input type="number" class="form-control @error('time_limit') is-invalid @enderror"
                                           name="time_limit" value="{{ old('time_limit') }}" placeholder="No limit" min="1">
                                    @error('time_limit')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="cb-field-label">Deadline <span class="optional">(optional)</span></label>
                                    <input type="datetime-local" class="form-control @error('due_date') is-invalid @enderror"
                                           name="due_date" value="{{ old('due_date') }}">
                                    @error('due_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    <small class="text-muted">Submissions blocked after this date</small>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="cb-field-label">Instructions <span class="required">*</span></label>
                                <textarea class="form-control @error('instructions') is-invalid @enderror"
                                          name="instructions" rows="2" required placeholder="Instructions for students...">{{ old('instructions', 'Read the document carefully and answer the questions below.') }}</textarea>
                                @error('instructions')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="mb-0">
                                <label class="cb-field-label">Description <span class="optional">(optional)</span></label>
                                <textarea class="form-control @error('description') is-invalid @enderror"
                                          name="description" rows="2" placeholder="Brief description...">{{ old('description') }}</textarea>
                                @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    {{-- Document Upload --}}
                    <div class="cb-section">
                        <div class="cb-section__title"><i class="fas fa-cloud-upload-alt"></i> Upload Document</div>
                        <div class="cb-settings">
                            <label class="cb-upload-area" id="docUploadArea">
                                <input type="file" class="d-none" name="document" id="documentInput"
                                       accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx" required
                                       onchange="handleDocumentUpload(this)">
                                <i class="fas fa-cloud-upload-alt d-block"></i>
                                <div class="cb-upload-area__text">
                                    <strong>Click to upload document</strong> or drag and drop<br>
                                    <small>DOCX, PPTX, XLSX, PDF &mdash; max 10MB</small>
                                </div>
                                <span class="upload-name" id="uploadFileName"></span>
                            </label>
                            @error('document')<div class="text-danger small mt-1">{{ $message }}</div>@enderror

                            {{-- PDF Warning --}}
                            <div class="alert alert-warning d-none mt-3 mb-0" id="pdfWarning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>PDF files are view-only.</strong> The document content cannot be edited in the browser. Students will be able to download the original file.
                            </div>
                        </div>
                    </div>

                    {{-- Document Editor (hidden until DOCX/PPTX uploaded) --}}
                    <div class="cb-section d-none" id="editorSection">
                        <div class="cb-section__title"><i class="fas fa-edit"></i> Document Content (Editable)</div>
                        <div class="cb-settings">
                            <p class="text-muted small mb-2">
                                <i class="fas fa-info-circle me-1"></i>Review the converted document below. You can edit text, fix typos, and adjust formatting before saving.
                            </p>
                            <div id="quill-editor" style="min-height: 400px;"></div>
                        </div>
                    </div>

                    {{-- Loading indicator --}}
                    <div class="d-none text-center py-4" id="convertingSpinner">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="text-muted mt-2">Converting document...</p>
                    </div>
                </div>

                <div class="cb-footer">
                    <a href="{{ route('content.management') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-1"></i>Cancel
                    </a>
                    <div class="btn-group-footer">
                        <span class="cb-footer__hint d-none d-md-inline">Upload a document to enable saving</span>
                        <button type="submit" class="btn btn-primary" id="saveBtn">
                            <i class="fas fa-save me-1"></i>Create Assessment
                        </button>
                    </div>
                </div>
            </div>

            {{-- SIDEBAR --}}
            <div class="cb-sidebar">
                <div class="cb-sidebar__title">Document Assessment</div>

                <div class="cb-sidebar__group">
                    <div class="cb-sidebar__group-label"><i class="fas fa-info-circle"></i> About</div>
                    <div class="cb-sidebar__info">
                        Upload a DOCX or PPTX document. The content will be converted and displayed in an editor so you can review and fix typos. Students will see the document and submit their answers via a text box. You will grade their submissions.
                    </div>
                </div>

                <div class="cb-sidebar__group">
                    <div class="cb-sidebar__group-label"><i class="fas fa-file"></i> Supported Formats</div>
                    <div class="cb-sidebar__info">
                        <div class="cb-sidebar__info-title">DOCX / PPTX</div>
                        Editable in browser
                    </div>
                    <div class="cb-sidebar__info">
                        <div class="cb-sidebar__info-title">PDF</div>
                        Download only (not editable)
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

async function handleDocumentUpload(input) {
    const file = input.files[0];
    if (!file) return;

    const ext = file.name.split('.').pop().toLowerCase();
    const uploadArea = document.getElementById('docUploadArea');
    const fileName = document.getElementById('uploadFileName');

    // Show selected filename
    uploadArea.classList.add('has-file');
    fileName.textContent = file.name;

    // Reset UI
    document.getElementById('pdfWarning').classList.add('d-none');
    document.getElementById('editorSection').classList.add('d-none');
    document.getElementById('convertingSpinner').classList.add('d-none');

    if (['docx', 'pptx', 'doc', 'ppt', 'pdf', 'xlsx', 'xls'].includes(ext)) {
        // Show loading
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
            } else if (data.error) {
                document.getElementById('editorSection').classList.add('d-none');
            }
        } catch (err) {
            document.getElementById('convertingSpinner').classList.add('d-none');
            console.error('Conversion failed:', err);
        }
    }
}

// Sync Quill content to hidden input before form submit
document.getElementById('docAssessmentForm').addEventListener('submit', function() {
    if (quill) {
        document.getElementById('documentContentInput').value = quill.root.innerHTML;
    }
});
</script>
@endpush

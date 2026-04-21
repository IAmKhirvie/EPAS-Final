{{-- Document Viewer Component
     Usage: @include('components.document-viewer', ['documentContent' => $model->document_content, 'filePath' => $model->file_path, 'originalFilename' => $model->original_filename, 'downloadRoute' => route('...download...', $model)])
--}}
@if(!empty($documentContent))
<div class="doc-viewer" id="docViewer">
    <div class="doc-viewer__page" id="docPage">
        <div id="docContent">
            {!! $documentContent !!}
        </div>
        <div class="doc-viewer__fade-bottom" id="docFade"></div>
    </div>
    <div class="doc-viewer__nav" id="docNav">
        <button class="doc-viewer__nav-btn" id="docPrev" title="Previous page">
            <i class="fas fa-chevron-left"></i>
        </button>
        <span class="doc-viewer__page-info" id="docPageInfo">Page 1 of 1</span>
        <button class="doc-viewer__nav-btn" id="docNext" title="Next page">
            <i class="fas fa-chevron-right"></i>
        </button>
    </div>
</div>
@elseif(!empty($filePath))
<div class="doc-viewer" style="padding-bottom: 1.5rem;">
    <div class="doc-viewer__page d-flex flex-column align-items-center justify-content-center text-center" style="min-height: 200px;">
        <i class="fas fa-file-alt text-secondary" style="font-size: 2.5rem; margin-bottom: 0.75rem;"></i>
        <p class="mb-2"><strong>{{ $originalFilename ?? 'Attached File' }}</strong></p>
        <a href="{{ $downloadRoute }}" class="btn btn-sm btn-primary">
            <i class="fas fa-download me-1"></i>Download to View
        </a>
    </div>
</div>
@endif

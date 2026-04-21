{{-- Document Block: Embedded document viewer with download --}}
@if(!empty($block['data']['document_content']))
    @include('components.document-viewer-css')
    @include('components.document-viewer', [
        'documentContent' => $block['data']['document_content'],
        'filePath' => $block['data']['file_path'] ?? null,
        'originalFilename' => $block['data']['original_filename'] ?? 'Document',
        'downloadRoute' => !empty($block['data']['file_path']) ? url('storage/' . $block['data']['file_path']) : '#',
    ])
    @if(!empty($block['data']['file_path']))
    <div class="mb-2 text-end">
        <a href="{{ url('storage/' . $block['data']['file_path']) }}" class="btn btn-outline-primary btn-sm" download>
            <i class="fas fa-download me-1"></i>Download {{ $block['data']['original_filename'] ?? 'Original' }}
        </a>
    </div>
    @endif
    @include('components.document-viewer-js')
@elseif(!empty($block['data']['file_path']))
    <div class="mb-4">
        <a href="{{ url('storage/' . $block['data']['file_path']) }}" class="btn btn-outline-primary btn-sm" download>
            <i class="fas fa-download me-1"></i>{{ $block['data']['original_filename'] ?? 'Download File' }}
        </a>
    </div>
@endif

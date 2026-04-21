@if($topic->usesBlocks())
{{-- Block-based rendering --}}
<div class="topic-content topic-content--blocks">
    <div class="topic-header mb-4">
        <h2 class="topic-title">{{ $topic->title }}</h2>
        <div class="topic-meta d-flex align-items-center gap-3 text-muted">
            <span class="topic-number">Topic {{ $topic->topic_number }}</span>
        </div>
    </div>

    @include('components.blocks.render', ['blocks' => $topic->blocks])
</div>

<style>
/* Block Layout Styles */
.content-block {
    margin-bottom: 1.5rem;
}

.content-block:last-child {
    margin-bottom: 0;
}

/* Image + Text Block */
.block-image-wrapper {
    position: relative;
    overflow: hidden;
    border-radius: 0.5rem;
}

.block-image-wrapper img {
    width: 100%;
    height: auto;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.block-image-wrapper:hover img {
    transform: scale(1.02);
}

.block-image-caption {
    text-align: center;
    color: #6c757d;
    font-size: 0.85rem;
    font-style: italic;
    margin-top: 0.5rem;
    margin-bottom: 0;
}

/* Standalone Image Block */
.block-image img {
    max-width: 100%;
    height: auto;
    border-radius: 0.5rem;
    transition: transform 0.3s ease;
}

.block-image img:hover {
    transform: scale(1.02);
}

/* Table Block */
.block-table {
    border-radius: 0.5rem;
    overflow: hidden;
}

.block-table table {
    width: 100%;
    margin-bottom: 0;
}

/* Callout Block */
.block-callout {
    display: flex;
    gap: 1rem;
    padding: 1rem 1.25rem;
    border-radius: 0.5rem;
    border-left: 4px solid;
}

.block-callout__icon {
    font-size: 1.25rem;
    flex-shrink: 0;
    padding-top: 2px;
}

.block-callout__body {
    flex: 1;
    min-width: 0;
}

.block-callout__title {
    font-weight: 600;
    margin-bottom: 0.35rem;
}

.block-callout--info {
    background: #e8f4fd;
    border-color: #2196F3;
}
.block-callout--info .block-callout__icon { color: #2196F3; }
.block-callout--info .block-callout__title { color: #1565C0; }

.block-callout--warning {
    background: #fff8e1;
    border-color: #FF9800;
}
.block-callout--warning .block-callout__icon { color: #FF9800; }
.block-callout--warning .block-callout__title { color: #E65100; }

.block-callout--tip {
    background: #e8f5e9;
    border-color: #4CAF50;
}
.block-callout--tip .block-callout__icon { color: #4CAF50; }
.block-callout--tip .block-callout__title { color: #2E7D32; }

.block-callout--danger {
    background: #fbe9e7;
    border-color: #f44336;
}
.block-callout--danger .block-callout__icon { color: #f44336; }
.block-callout--danger .block-callout__title { color: #c62828; }

/* Divider Block */
.block-divider {
    border: none;
    border-top: 2px solid #e9ecef;
    margin: 1.5rem 0;
}

/* Heading Block */
.block-heading {
    color: #333;
    font-weight: 600;
    margin-bottom: 0.5rem;
}

/* Dark Mode */
.dark-mode .block-callout--info {
    background: rgba(33, 150, 243, 0.1);
}
.dark-mode .block-callout--warning {
    background: rgba(255, 152, 0, 0.1);
}
.dark-mode .block-callout--tip {
    background: rgba(76, 175, 80, 0.1);
}
.dark-mode .block-callout--danger {
    background: rgba(244, 67, 54, 0.1);
}
.dark-mode .block-heading {
    color: #e0e0e0;
}
.dark-mode .block-image-caption {
    color: #adb5bd;
}
.dark-mode .block-divider {
    border-top-color: #444;
}
</style>

@else
{{-- Legacy rendering --}}
<div class="topic-content">
    <div class="topic-header mb-4">
        <h2 class="topic-title">{{ $topic->title }}</h2>
        <div class="topic-meta d-flex align-items-center gap-3 text-muted">
            <span class="topic-number">Topic {{ $topic->topic_number }}</span>
            <span class="topic-order">Order: {{ $topic->order }}</span>
        </div>
    </div>

    @if($topic->document_content)
    @include('components.document-viewer-css')
    @include('components.document-viewer', [
        'documentContent' => $topic->document_content,
        'filePath' => $topic->file_path,
        'originalFilename' => $topic->original_filename,
        'downloadRoute' => route('topics.download', $topic),
    ])
    @if($topic->file_path)
    <div class="mb-2 text-end">
        <a href="{{ route('topics.download', $topic) }}" class="btn btn-outline-primary btn-sm">
            <i class="fas fa-download me-1"></i>Download Original
        </a>
    </div>
    @endif
    @include('components.document-viewer-js')
    @elseif($topic->file_path)
    <div class="mb-4">
        <a href="{{ route('topics.download', $topic) }}" class="btn btn-outline-primary btn-sm">
            <i class="fas fa-download me-1"></i>{{ $topic->original_filename }}
        </a>
    </div>
    @endif

    @if($topic->content)
    <div class="content-body basic-formatting mb-4">
        {!! $topic->content !!}
    </div>
    @endif

    @if($topic->parts && count($topic->parts) > 0)
    <div class="topic-parts">
        @foreach($topic->parts as $index => $part)
        <div class="part-item mb-4">
            <div class="row align-items-start">
                @if(!empty($part['image']))
                <div class="col-md-3 mb-3 mb-md-0">
                    <div class="part-image-wrapper">
                        <img src="{{ $part['image'] }}" alt="{{ $part['title'] ?? 'Part Image' }}" class="img-fluid rounded shadow-sm">
                    </div>
                </div>
                <div class="col-md-9">
                @else
                <div class="col-12">
                @endif
                    <div class="part-content">
                        @if(!empty($part['title']))
                        <h5 class="part-title mb-2">
                            <span class="part-number-badge me-2">{{ $index + 1 }}</span>
                            {{ $part['title'] }}
                        </h5>
                        @endif
                        @if(!empty($part['explanation']))
                        <div class="part-explanation">
                            {!! nl2br(e($part['explanation'])) !!}
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @if(!$loop->last)
        <hr class="my-4">
        @endif
        @endforeach
    </div>
    @endif
</div>

<style>
.topic-parts {
    margin-top: 1.5rem;
}

.part-item {
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 0.5rem;
    border-left: 4px solid #ffb902;
}

.part-image-wrapper {
    position: relative;
    overflow: hidden;
    border-radius: 0.5rem;
}

.part-image-wrapper img {
    width: 100%;
    height: auto;
    max-height: 200px;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.part-image-wrapper:hover img {
    transform: scale(1.05);
}

.part-number-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 24px;
    height: 24px;
    background: #ffb902;
    color: white;
    border-radius: 50%;
    font-size: 0.75rem;
    font-weight: 600;
}

.part-title {
    color: #333;
    font-weight: 600;
}

.part-explanation {
    color: #555;
    line-height: 1.7;
}
</style>
@endif

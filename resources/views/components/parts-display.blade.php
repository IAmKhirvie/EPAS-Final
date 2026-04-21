{{-- Reusable Parts Display Component --}}
{{-- Usage: @include('components.parts-display', ['parts' => $model->parts]) --}}

@php
    $parts = $parts ?? [];
@endphp

@if($parts && count($parts) > 0)
<div class="parts-display-component mt-4">
    @foreach($parts as $index => $part)
    <div class="part-item mb-4">
        <div class="row align-items-start">
            @if(!empty($part['image']))
            <div class="col-md-3 mb-3 mb-md-0">
                <div class="part-image-wrapper">
                    <img src="{{ $part['image'] }}" alt="{{ $part['title'] ?? 'Part Image' }}" class="img-fluid rounded shadow-sm" onclick="openImageModal(this.src, '{{ $part['title'] ?? 'Part Image' }}')">
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

<style>
.parts-display-component .part-item {
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 0.5rem;
    border-left: 4px solid #ffb902;
}

.parts-display-component .part-image-wrapper {
    position: relative;
    overflow: hidden;
    border-radius: 0.5rem;
}

.parts-display-component .part-image-wrapper img {
    width: 100%;
    height: auto;
    max-height: 200px;
    object-fit: cover;
    transition: transform 0.3s ease;
    cursor: pointer;
}

.parts-display-component .part-image-wrapper:hover img {
    transform: scale(1.02);
}

.parts-display-component .part-number-badge {
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

.parts-display-component .part-title {
    color: #333;
    font-weight: 600;
}

.parts-display-component .part-explanation {
    color: #555;
    line-height: 1.7;
}

/* Dark mode support */
.dark-mode .parts-display-component .part-item {
    background: var(--card-bg, #2d2d2d);
    border-left-color: var(--primary, #ffb902);
}

.dark-mode .parts-display-component .part-title {
    color: var(--text-primary, #e9ecef);
}

.dark-mode .parts-display-component .part-explanation {
    color: var(--text-secondary, #adb5bd);
}
</style>

<!-- Image Modal -->
<div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="imageModalTitle">Image</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center p-0">
                <img src="" id="imageModalImg" class="img-fluid" alt="">
            </div>
        </div>
    </div>
</div>

<script>
function openImageModal(src, title) {
    document.getElementById('imageModalImg').src = src;
    document.getElementById('imageModalTitle').textContent = title || 'Image';
    new bootstrap.Modal(document.getElementById('imageModal')).show();
}
</script>
@endif

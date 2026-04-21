{{-- Image Block: Standalone image with optional caption --}}
@if(!empty($block['data']['image']))
@php
    $size = $block['data']['size'] ?? 'medium';
    $sizeClass = match($size) {
        'small' => 'col-md-6 mx-auto',
        'large' => 'col-12',
        default => 'col-md-8 mx-auto',
    };
@endphp

<div class="block-image text-center">
    <div class="row">
        <div class="{{ $sizeClass }}">
            <img src="{{ $block['data']['image'] }}" alt="{{ $block['data']['caption'] ?? 'Image' }}" class="img-fluid rounded shadow-sm">
            @if(!empty($block['data']['caption']))
            <p class="block-image-caption">{{ $block['data']['caption'] }}</p>
            @endif
        </div>
    </div>
</div>
@endif

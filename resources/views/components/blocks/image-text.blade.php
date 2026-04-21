{{-- Image + Text Block: Split layout with configurable image position --}}
@php
    $position = $block['data']['image_position'] ?? 'left';
    $hasImage = !empty($block['data']['image']);
@endphp

<div class="block-image-text">
    <div class="row align-items-start">
        @if($hasImage && $position === 'left')
        <div class="col-md-5 mb-3 mb-md-0">
            <div class="block-image-wrapper">
                <img src="{{ $block['data']['image'] }}" alt="{{ $block['data']['caption'] ?? 'Content image' }}" class="img-fluid rounded shadow-sm">
                @if(!empty($block['data']['caption']))
                <p class="block-image-caption">{{ $block['data']['caption'] }}</p>
                @endif
            </div>
        </div>
        <div class="col-md-7">
            <div class="content-body basic-formatting">
                {!! $block['data']['content'] ?? '' !!}
            </div>
        </div>
        @elseif($hasImage && $position === 'right')
        <div class="col-md-7">
            <div class="content-body basic-formatting">
                {!! $block['data']['content'] ?? '' !!}
            </div>
        </div>
        <div class="col-md-5 mb-3 mb-md-0">
            <div class="block-image-wrapper">
                <img src="{{ $block['data']['image'] }}" alt="{{ $block['data']['caption'] ?? 'Content image' }}" class="img-fluid rounded shadow-sm">
                @if(!empty($block['data']['caption']))
                <p class="block-image-caption">{{ $block['data']['caption'] }}</p>
                @endif
            </div>
        </div>
        @else
        <div class="col-12">
            <div class="content-body basic-formatting">
                {!! $block['data']['content'] ?? '' !!}
            </div>
        </div>
        @endif
    </div>
</div>

{{-- Callout Block: Styled info/warning/tip/danger box --}}
@php
    $style = $block['data']['style'] ?? 'info';
    $config = match($style) {
        'warning' => ['icon' => 'fas fa-exclamation-triangle', 'class' => 'block-callout--warning'],
        'tip' => ['icon' => 'fas fa-lightbulb', 'class' => 'block-callout--tip'],
        'danger' => ['icon' => 'fas fa-exclamation-circle', 'class' => 'block-callout--danger'],
        default => ['icon' => 'fas fa-info-circle', 'class' => 'block-callout--info'],
    };
@endphp

<div class="block-callout {{ $config['class'] }}">
    <div class="block-callout__icon">
        <i class="{{ $config['icon'] }}"></i>
    </div>
    <div class="block-callout__body">
        @if(!empty($block['data']['title']))
        <h5 class="block-callout__title">{{ $block['data']['title'] }}</h5>
        @endif
        @if(!empty($block['data']['content']))
        <div class="content-body basic-formatting">
            {!! $block['data']['content'] !!}
        </div>
        @endif
    </div>
</div>

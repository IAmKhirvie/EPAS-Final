{{-- Heading Block: Section heading --}}
@php
    $level = $block['data']['level'] ?? 3;
    $text = $block['data']['text'] ?? '';
    $tag = in_array($level, [2, 3, 4]) ? 'h' . $level : 'h3';
@endphp

@if(!empty($text))
<{{ $tag }} class="block-heading">{{ $text }}</{{ $tag }}>
@endif

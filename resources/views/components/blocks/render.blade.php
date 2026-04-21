{{-- Block Renderer Dispatcher
     Usage: @include('components.blocks.render', ['blocks' => $topic->blocks])
--}}
@foreach($blocks as $block)
    @php
        $blockType = str_replace('_', '-', $block['type'] ?? 'text');
        $blockView = 'components.blocks.' . $blockType;
    @endphp
    <div class="content-block content-block--{{ $blockType }}" data-block-type="{{ $block['type'] ?? 'text' }}">
        @includeIf($blockView, ['block' => $block])
    </div>
@endforeach

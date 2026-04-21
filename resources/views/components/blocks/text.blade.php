{{-- Text Block: Full-width rich HTML content --}}
@if(!empty($block['data']['content']))
<div class="content-body basic-formatting">
    {!! $block['data']['content'] !!}
</div>
@endif

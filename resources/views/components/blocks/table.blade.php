{{-- Table Block: Full-width responsive table --}}
@if(!empty($block['data']['content']))
<div class="block-table table-responsive">
    <div class="content-body basic-formatting">
        {!! $block['data']['content'] !!}
    </div>
</div>
@endif

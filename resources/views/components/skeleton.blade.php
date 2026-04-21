@for($i = 0; $i < $count; $i++)
@if($type === 'table-row')
<div class="skeleton-row d-flex align-items-center gap-3 py-2 px-3">
    <div class="skeleton-cell skeleton-pulse" style="width: 40px; height: 16px; border-radius: 4px;"></div>
    <div class="skeleton-cell skeleton-pulse" style="width: 60%; height: 16px; border-radius: 4px;"></div>
    <div class="skeleton-cell skeleton-pulse" style="width: 20%; height: 16px; border-radius: 4px;"></div>
    <div class="skeleton-cell skeleton-pulse" style="width: 80px; height: 16px; border-radius: 4px;"></div>
</div>
@elseif($type === 'card')
<div class="skeleton-card skeleton-pulse" style="width: {{ $width ?? '100%' }}; height: {{ $height ?? '120px' }}; border-radius: 8px;"></div>
@elseif($type === 'circle')
<div class="skeleton-circle skeleton-pulse" style="width: {{ $width ?? '40px' }}; height: {{ $height ?? '40px' }}; border-radius: 50%;"></div>
@else
<div class="skeleton-line skeleton-pulse" style="width: {{ $width ?? '100%' }}; height: {{ $height ?? '16px' }}; border-radius: 4px; margin-bottom: 8px;"></div>
@endif
@endfor

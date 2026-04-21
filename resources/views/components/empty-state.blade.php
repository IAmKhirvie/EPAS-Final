<div class="empty-state text-center py-5">
    <i class="{{ $icon }} fa-3x mb-3 opacity-50"></i>
    <h5 class="empty-state-title">{{ $title }}</h5>
    @if($description)
        <p class="text-muted mb-3">{{ $description }}</p>
    @endif
    @if($actionUrl && $actionLabel)
        <a href="{{ $actionUrl }}" class="btn btn-primary btn-sm">{{ $actionLabel }}</a>
    @endif
</div>

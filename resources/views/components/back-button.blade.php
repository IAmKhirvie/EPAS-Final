@props(['route' => null, 'label' => 'Back'])

<a href="{{ $route ?? url()->previous() }}" class="btn btn-outline-secondary btn-sm mb-2">
    <i class="fas fa-arrow-left me-1"></i>{{ $label }}
</a>

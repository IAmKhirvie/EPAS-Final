<nav aria-label="breadcrumb" class="mb-2">
    <ol class="breadcrumb mb-0">
        @foreach($items as $index => $item)
            @if($index === count($items) - 1)
                <li class="breadcrumb-item active" aria-current="page">{{ $item['label'] }}</li>
            @else
                <li class="breadcrumb-item">
                    @if(!empty($item['url']))
                        <a href="{{ $item['url'] }}">{{ $item['label'] }}</a>
                    @else
                        {{ $item['label'] }}
                    @endif
                </li>
            @endif
        @endforeach
    </ol>
</nav>

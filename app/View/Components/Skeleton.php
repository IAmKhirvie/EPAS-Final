<?php

namespace App\View\Components;

use Illuminate\View\Component;

class Skeleton extends Component
{
    public string $type;
    public int $count;
    public ?string $width;
    public ?string $height;

    public function __construct(
        string $type = 'line',
        int $count = 1,
        ?string $width = null,
        ?string $height = null
    ) {
        $this->type = $type;
        $this->count = $count;
        $this->width = $width;
        $this->height = $height;
    }

    public function render()
    {
        return view('components.skeleton');
    }
}

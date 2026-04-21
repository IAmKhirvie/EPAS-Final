<?php

namespace App\View\Components;

use Illuminate\View\Component;

class EmptyState extends Component
{
    public string $icon;
    public string $title;
    public string $description;
    public ?string $actionUrl;
    public ?string $actionLabel;

    public function __construct(
        string $icon = 'fas fa-inbox',
        string $title = 'No data found',
        string $description = '',
        ?string $actionUrl = null,
        ?string $actionLabel = null
    ) {
        $this->icon = $icon;
        $this->title = $title;
        $this->description = $description;
        $this->actionUrl = $actionUrl;
        $this->actionLabel = $actionLabel;
    }

    public function render()
    {
        return view('components.empty-state');
    }
}

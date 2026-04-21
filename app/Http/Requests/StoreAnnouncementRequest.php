<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAnnouncementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'content' => 'required|string|max:5000',
            'is_pinned' => 'boolean',
            'is_urgent' => 'boolean',
            'publish_at' => 'nullable|date',
            'deadline' => 'nullable|date',
            'target_roles' => 'nullable|string|max:255',
            'target_sections' => 'nullable|string|max:500',
        ];
    }
}

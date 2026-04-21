<?php

namespace App\Http\Requests;

use App\Constants\Roles;
use Illuminate\Foundation\Http\FormRequest;

class StoreTaskSheetRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        return $user && in_array($user->role, [Roles::ADMIN, Roles::INSTRUCTOR]);
    }

    public function rules(): array
    {
        return [
            'task_number' => 'required|string',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'instructions' => 'required|string',
            'objectives' => 'required|array|min:1',
            'materials' => 'required|array|min:1',
            'safety_precautions' => 'nullable|array',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|mimetypes:image/jpeg,image/png,image/gif|max:' . config('joms.uploads.max_image_size', 5120),
            'file' => 'nullable|file|mimes:pdf,xlsx,xls,doc,docx,ppt,pptx|max:' . config('joms.uploads.max_document_size', 10240),
            'items' => 'required|array|min:1',
            'items.*.part_name' => 'required|string',
            'items.*.description' => 'required|string',
            'items.*.expected_finding' => 'required|string',
            'items.*.acceptable_range' => 'required|string',
        ];
    }
}

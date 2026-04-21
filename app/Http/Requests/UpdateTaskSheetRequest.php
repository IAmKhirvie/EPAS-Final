<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTaskSheetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
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
        ];
    }
}

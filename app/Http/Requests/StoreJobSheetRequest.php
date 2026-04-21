<?php

namespace App\Http\Requests;

use App\Constants\Roles;
use Illuminate\Foundation\Http\FormRequest;

class StoreJobSheetRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        return $user && in_array($user->role, [Roles::ADMIN, Roles::INSTRUCTOR]);
    }

    public function rules(): array
    {
        return [
            'job_number' => 'required|string',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'objectives' => 'required|array|min:1',
            'tools_required' => 'required|array|min:1',
            'safety_requirements' => 'required|array|min:1',
            'reference_materials' => 'nullable|array',
            'steps' => 'required|array|min:1',
            'steps.*.step_number' => 'required|integer',
            'steps.*.instruction' => 'required|string',
            'steps.*.expected_outcome' => 'required|string',
            'steps.*.image' => 'nullable|image|mimes:jpeg,png,jpg,gif|mimetypes:image/jpeg,image/png,image/gif|max:' . config('joms.uploads.max_image_size', 5120),
            'file' => 'nullable|file|mimes:pdf,xlsx,xls,doc,docx,ppt,pptx|max:' . config('joms.uploads.max_document_size', 10240),
        ];
    }
}

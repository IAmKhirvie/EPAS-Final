<?php

namespace App\Http\Requests;

use App\Constants\Roles;
use Illuminate\Foundation\Http\FormRequest;

class UpdateJobSheetRequest extends FormRequest
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
            'file' => 'nullable|file|mimes:pdf,xlsx,xls,doc,docx,ppt,pptx|max:' . config('joms.uploads.max_document_size', 10240),
        ];
    }
}

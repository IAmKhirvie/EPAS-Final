<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDocumentAssessmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'assessment_number' => 'required|string|max:50',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'instructions' => 'required|string',
            'document' => 'nullable|file|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx|max:' . config('joms.uploads.max_document_size', 10240),
            'document_content' => 'nullable|string',
            'max_points' => 'required|integer|min:1',
            'time_limit' => 'nullable|integer|min:1',
            'due_date' => 'nullable|date',
        ];
    }
}

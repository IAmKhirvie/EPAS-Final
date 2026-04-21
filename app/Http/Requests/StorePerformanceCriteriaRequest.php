<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePerformanceCriteriaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => 'required|in:task_sheet,job_sheet',
            'related_id' => 'required|integer',
            'criteria' => 'required|array|min:1',
            'criteria.*.description' => 'required|string',
            'criteria.*.observed' => 'required|boolean',
            'criteria.*.remarks' => 'nullable|string',
        ];
    }
}

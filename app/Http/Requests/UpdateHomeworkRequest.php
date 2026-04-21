<?php

namespace App\Http\Requests;

use App\Constants\Roles;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateHomeworkRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Only admins and instructors can update homework.
     */
    public function authorize(): bool
    {
        $user = Auth::user();

        return $user && in_array($user->role, [Roles::ADMIN, Roles::INSTRUCTOR]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'homework_number'        => 'required|string',
            'title'                  => 'required|string|max:255',
            'description'            => 'nullable|string',
            'instructions'           => 'required|string',
            'requirements'           => 'required|array|min:1',
            'submission_guidelines'  => 'required|array|min:1',
            'due_date'               => 'required|date',
            'max_points'             => 'required|integer|min:1',
            'reference_images'       => 'nullable|array',
            'reference_images.*'     => 'image|mimes:jpeg,png,jpg,gif|mimetypes:image/jpeg,image/png,image/gif|max:' . config('joms.uploads.max_image_size', 5120),
        ];
    }

    /**
     * Get custom error messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'homework_number.required'       => 'The homework number is required.',
            'title.required'                 => 'The homework title is required.',
            'title.max'                      => 'The title must not exceed 255 characters.',
            'instructions.required'          => 'Please provide instructions for the homework.',
            'requirements.required'          => 'At least one requirement is needed.',
            'requirements.min'               => 'At least one requirement is needed.',
            'submission_guidelines.required' => 'At least one submission guideline is needed.',
            'submission_guidelines.min'      => 'At least one submission guideline is needed.',
            'due_date.required'              => 'Please set a due date.',
            'due_date.date'                  => 'The due date must be a valid date.',
            'max_points.required'            => 'Please specify the maximum points.',
            'max_points.integer'             => 'Maximum points must be a whole number.',
            'max_points.min'                 => 'Maximum points must be at least 1.',
            'reference_images.*.image'       => 'Each reference file must be an image.',
            'reference_images.*.mimes'       => 'Reference images must be JPEG, PNG, JPG, or GIF.',
            'reference_images.*.mimetypes'   => 'Reference image file content does not match an allowed image type (JPEG, PNG, GIF).',
            'reference_images.*.max'         => 'Each reference image must not exceed ' . (config('joms.uploads.max_image_size', 5120) / 1024) . 'MB.',
        ];
    }
}

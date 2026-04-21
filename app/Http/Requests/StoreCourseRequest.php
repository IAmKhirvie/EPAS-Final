<?php

namespace App\Http\Requests;

use App\Constants\Roles;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreCourseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Only admins can create courses.
     */
    public function authorize(): bool
    {
        $user = Auth::user();

        return $user && $user->role === Roles::ADMIN;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'course_name'   => ['required', 'string', 'max:255', 'regex:/^[\pL\s\-\'\.\,\(\)]+$/u'],
            'course_code'   => 'required|string|max:50|unique:courses',
            'description'   => 'nullable|string',
            'sector'        => 'nullable|string|max:255',
            'instructor_id' => 'nullable|exists:users,id',
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
            'course_name.required'   => 'The course name is required.',
            'course_name.max'        => 'The course name must not exceed 255 characters.',
            'course_code.required'   => 'The course code is required.',
            'course_code.max'        => 'The course code must not exceed 50 characters.',
            'course_code.unique'     => 'This course code is already in use.',
            'sector.max'             => 'The sector must not exceed 255 characters.',
            'instructor_id.exists'   => 'The selected instructor does not exist.',
        ];
    }
}

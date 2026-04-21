<?php

namespace App\Http\Requests;

use App\Constants\Roles;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateModuleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Admins can update any module. Instructors can only update modules
     * belonging to courses they are assigned to.
     */
    public function authorize(): bool
    {
        $user = Auth::user();

        if (!$user) {
            return false;
        }

        if ($user->role === Roles::ADMIN) {
            return true;
        }

        if ($user->role === Roles::INSTRUCTOR) {
            $module = $this->route('module');

            return $module
                && $module->course
                && $module->course->instructor_id === $user->id;
        }

        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'course_id'            => 'required|exists:courses,id',
            'qualification_title'  => ['required', 'string', 'max:255', 'regex:/^[\pL\s\-\'\.\,\(\)]+$/u'],
            'unit_of_competency'   => ['required', 'string', 'max:255', 'regex:/^[\pL\s\-\'\.\,\(\)]+$/u'],
            'module_title'         => ['required', 'string', 'max:255', 'regex:/^[\pL\s\-\'\.\,\(\)]+$/u'],
            'module_number'        => 'required|string|max:50',
            'module_name'          => ['required', 'string', 'max:255', 'regex:/^[\pL\s\-\'\.\,\(\)]+$/u'],
            'table_of_contents'    => 'nullable|string',
            'how_to_use_cblm'      => 'nullable|string',
            'introduction'         => 'nullable|string',
            'learning_outcomes'    => 'nullable|string',
            'is_active'            => 'boolean',
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
            'course_id.required'           => 'Please select a course.',
            'course_id.exists'             => 'The selected course does not exist.',
            'qualification_title.required' => 'The qualification title is required.',
            'qualification_title.max'      => 'The qualification title must not exceed 255 characters.',
            'unit_of_competency.required'  => 'The unit of competency is required.',
            'unit_of_competency.max'       => 'The unit of competency must not exceed 255 characters.',
            'module_title.required'        => 'The module title is required.',
            'module_title.max'             => 'The module title must not exceed 255 characters.',
            'module_number.required'       => 'The module number is required.',
            'module_number.max'            => 'The module number must not exceed 50 characters.',
            'module_name.required'         => 'The module name is required.',
            'module_name.max'              => 'The module name must not exceed 255 characters.',
            'is_active.boolean'            => 'The active status must be true or false.',
        ];
    }
}

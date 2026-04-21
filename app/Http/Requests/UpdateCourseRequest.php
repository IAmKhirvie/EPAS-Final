<?php

namespace App\Http\Requests;

use App\Constants\Roles;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateCourseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Admins can update any course. Instructors can only update courses
     * they are assigned to.
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
            $course = $this->route('course');

            return $course && $course->instructor_id === $user->id;
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
        $courseId = $this->route('course')?->id;

        return [
            'course_name'   => ['required', 'string', 'max:255', 'regex:/^[\pL\s\-\'\.\,\(\)]+$/u'],
            'course_code'   => 'required|string|max:50|unique:courses,course_code,' . $courseId,
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
            'course_code.unique'     => 'This course code is already in use by another course.',
            'sector.max'             => 'The sector must not exceed 255 characters.',
            'instructor_id.exists'   => 'The selected instructor does not exist.',
        ];
    }
}

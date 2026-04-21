<?php

namespace App\Http\Requests;

use App\Constants\Roles;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $user = $this->route('user');
        $userId = is_object($user) ? $user->id : $user;

        $roleRule = auth()->user()?->role === Roles::ADMIN
            ? 'required|string|in:' . implode(',', Roles::all())
            : 'prohibited';

        return [
            'student_id'     => 'required|string|max:25|unique:users,student_id,' . $userId,
            'first_name'     => ['required', 'string', 'max:255', 'regex:/^[\pL\s\-\'\.]+$/u'],
            'middle_name'    => ['nullable', 'string', 'max:255', 'regex:/^[\pL\s\-\'\.]+$/u'],
            'last_name'      => ['required', 'string', 'max:255', 'regex:/^[\pL\s\-\'\.]+$/u'],
            'ext_name'       => ['nullable', 'string', 'max:10', 'regex:/^[\pL\s\-\'\.]+$/u'],
            'email'          => 'required|email|unique:users,email,' . $userId,
            'role'           => $roleRule,
            'department_id'  => 'nullable|exists:departments,id',
            'stat'           => 'required|boolean',
            'password'       => ['nullable', 'string', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()->uncompromised()],
            'section'        => 'nullable|string|max:255',
            'school_year'    => 'nullable|string|max:20',
            'custom_section' => 'nullable|string|max:255',
            'room_number'    => 'nullable|string|max:255',
        ];
    }
}

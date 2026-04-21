<?php

namespace App\Http\Requests;

use App\Constants\Roles;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name'    => ['required', 'string', 'max:255', 'regex:/^[\pL\s\-\'\.]+$/u'],
            'middle_name'   => ['nullable', 'string', 'max:255', 'regex:/^[\pL\s\-\'\.]+$/u'],
            'last_name'     => ['required', 'string', 'max:255', 'regex:/^[\pL\s\-\'\.]+$/u'],
            'ext_name'      => ['nullable', 'string', 'max:10', 'regex:/^[\pL\s\-\'\.]+$/u'],
            'email'         => 'required|email:rfc,dns|unique:users,email',
            'role'          => 'required|string|in:' . implode(',', Roles::all()),
            'department_id' => 'required|exists:departments,id',
            'stat'          => 'nullable|boolean', // Checkbox: unchecked = absent = false
            'password'      => [
                'required',
                'string',
                'confirmed',
                Password::min(8)->mixedCase()->numbers()->symbols()->uncompromised(),
            ],
            'section'       => 'nullable|string|max:255',
            'school_year'   => 'nullable|string|max:20',
            'room_number'   => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.',
            'password.min' => 'Password must be at least 8 characters long.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email address is already registered.',
        ];
    }
}

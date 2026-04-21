<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->user()->id;

        return [
            'first_name' => 'required|string|max:255|regex:/^[\pL\s\-\']+$/u',
            'middle_name' => 'nullable|string|max:255|regex:/^[\pL\s\-\']+$/u',
            'last_name' => 'required|string|max:255|regex:/^[\pL\s\-\']+$/u',
            'email' => 'required|email:rfc,dns|unique:users,email,' . $userId,
            'phone' => 'nullable|string|max:20|regex:/^[\d\s\-\+\(\)]+$/',
            'bio' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'first_name.regex' => 'First name can only contain letters, spaces, hyphens, and apostrophes.',
            'middle_name.regex' => 'Middle name can only contain letters, spaces, hyphens, and apostrophes.',
            'last_name.regex' => 'Last name can only contain letters, spaces, hyphens, and apostrophes.',
            'email.email' => 'Please enter a valid email address.',
            'phone.regex' => 'Phone number can only contain digits, spaces, hyphens, plus signs, and parentheses.',
        ];
    }
}

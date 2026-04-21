<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class StrongPassword implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $minLength = config('joms.password.min_length', 8);
        $regex = config('joms.password.regex');

        if (strlen($value) < $minLength) {
            $fail("The :attribute must be at least {$minLength} characters.");
            return;
        }

        if (!preg_match($regex, $value)) {
            $fail(config('joms.password.message', 'The :attribute must contain at least one uppercase letter, one lowercase letter, one number, and one special character.'));
        }
    }

    /**
     * Get the validation rules as an array for use in form requests.
     */
    public static function rules(): array
    {
        return [
            'required',
            'string',
            'min:' . config('joms.password.min_length', 8),
            'confirmed',
            'regex:' . config('joms.password.regex'),
        ];
    }

    /**
     * Get the validation messages for the password rules.
     */
    public static function messages(): array
    {
        return [
            'password.regex' => config('joms.password.message'),
            'password.min' => 'Password must be at least ' . config('joms.password.min_length', 8) . ' characters long.',
        ];
    }
}

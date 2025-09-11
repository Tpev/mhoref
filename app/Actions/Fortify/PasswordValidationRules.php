<?php

namespace App\Actions\Fortify;

use Illuminate\Validation\Rules\Password;

trait PasswordValidationRules
{
    /**
     * Get the validation rules used to validate passwords.
     *
     * @return array<int, \Illuminate\Contracts\Validation\Rule|array<mixed>|string>
     */
    protected function passwordRules(): array
    {
        return [
            'required',
            'string',
            Password::min(8) // Minimum 8 characters
                ->mixedCase() // At least one uppercase and one lowercase letter
                ->numbers() // At least one number
                ->symbols(), // At least one special character
            'confirmed', // Ensure the password confirmation matches
        ];
    }
}

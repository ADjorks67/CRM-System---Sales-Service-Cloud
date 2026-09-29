<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PasswordComplexity implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            $fail('The :attribute must be a string.');

            return;
        }

        if (strlen($value) < 8) {
            $fail('The :attribute must be at least 8 characters.');

            return;
        }

        if (! preg_match('/[a-z]/', $value) || ! preg_match('/[A-Z]/', $value) || ! preg_match('/[0-9]/', $value)) {
            $fail('The :attribute must include upper and lower case letters and a number.');
        }
    }
}

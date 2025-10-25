<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class NubanRule implements Rule
{
    public function passes($attribute, $value)
    {
        // Implement NUBAN checksum validation
        // Or use: https://github.com/andela/laravel-nuban
        return strlen($value) === 10 && ctype_digit($value);
    }

    public function message()
    {
        return 'The :attribute must be a valid 10-digit Nigerian account number.';
    }
}
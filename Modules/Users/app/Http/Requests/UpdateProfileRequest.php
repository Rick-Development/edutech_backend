<?php

namespace Modules\Users\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'firstname' => 'sometimes|string|max:255',
            'lastname'  => 'sometimes|string|max:255',
            'phone'     => 'sometimes|string|max:20|unique:users,phone,' . $this->user()->id,
            'email'     => 'sometimes|email|unique:users,email,' . $this->user()->id,
        ];
    }
}

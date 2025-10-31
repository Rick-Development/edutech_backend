<?php

namespace Modules\Auth\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ApplyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    
public function rules()
{
    return [
        // 'application_id' => 'required|uuid|exists:partner_applications,id',
        // 'action' => 'required|in:approve,reject',
        // 'reason' => 'required_if:action,reject',
    ];
}

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validation errors',
            'errors' => $validator->errors(),
        ], 422));
    }
}

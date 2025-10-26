<?php

namespace Modules\Enrollment\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EnrollRequest extends FormRequest
{
    public function rules()
    {
        return [
            'course_id' => 'required|uuid|exists:courses,id',
            'payment_reference' => 'required|string', // Simulates payment proof
        ];
    }

    public function authorize()
    {
        return true; // In real app, check auth
    }
}
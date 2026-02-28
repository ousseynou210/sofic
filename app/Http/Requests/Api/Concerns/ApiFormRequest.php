<?php

namespace App\Http\Requests\Api\Concerns;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

abstract class ApiFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'succes' => false,
            'message' => 'Erreurs de validation.',
            'donnees' => null,
            'erreurs' => $validator->errors(),
        ], 422));
    }
}

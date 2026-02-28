<?php

namespace App\Http\Requests\Api;

use App\Http\Requests\Api\Concerns\ApiFormRequest;

class LoginRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'mot_de_passe' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Le champ email est obligatoire.',
            'email.email' => 'Le format de l\'email est invalide.',
            'mot_de_passe.required' => 'Le champ mot_de_passe est obligatoire.',
            'mot_de_passe.string' => 'Le champ mot_de_passe doit etre une chaine de caracteres.',
        ];
    }
}

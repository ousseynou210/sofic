<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEmailRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($this->user()?->id),
            ],
            'mot_de_passe' => ['required', 'string', 'current_password'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'L email est obligatoire.',
            'email.email' => 'Veuillez saisir une adresse email valide.',
            'email.max' => 'L email ne doit pas depasser 255 caracteres.',
            'email.unique' => 'Cet email est deja utilise.',
            'mot_de_passe.required' => 'Le mot de passe actuel est obligatoire.',
            'mot_de_passe.current_password' => 'Le mot de passe actuel est incorrect.',
        ];
    }
}

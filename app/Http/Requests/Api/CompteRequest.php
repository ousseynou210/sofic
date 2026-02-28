<?php

namespace App\Http\Requests\Api;

use App\Http\Requests\Api\Concerns\ApiFormRequest;
use Illuminate\Validation\Rule;

class CompteRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'nom' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(['CAISSE', 'BANQUE', 'MOBILE_MONEY', 'AUTRE'])],
            'solde_initial' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'nom.required' => 'Le champ nom est obligatoire.',
            'type.required' => 'Le champ type est obligatoire.',
            'type.in' => 'Le type est invalide.',
        ];
    }
}

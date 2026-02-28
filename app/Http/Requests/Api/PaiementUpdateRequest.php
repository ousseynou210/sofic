<?php

namespace App\Http\Requests\Api;

use App\Http\Requests\Api\Concerns\ApiFormRequest;
use Illuminate\Validation\Rule;

class PaiementUpdateRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'compte_id' => ['required', 'integer', 'exists:comptes,id'],
            'date_paiement' => ['required', 'date'],
            'montant' => ['required', 'numeric', 'gt:0'],
            'mode' => ['required', Rule::in(['ESPECES', 'WAVE', 'ORANGE_MONEY', 'VIREMENT', 'CHEQUE'])],
            'reference' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'compte_id.required' => 'Le champ compte_id est obligatoire.',
            'compte_id.exists' => 'Le compte selectionne est invalide.',
            'date_paiement.required' => 'Le champ date_paiement est obligatoire.',
            'date_paiement.date' => 'Le champ date_paiement doit etre une date valide.',
            'montant.required' => 'Le champ montant est obligatoire.',
            'montant.numeric' => 'Le champ montant doit etre numerique.',
            'montant.gt' => 'Le montant doit etre superieur a 0.',
            'mode.required' => 'Le champ mode est obligatoire.',
            'mode.in' => 'Le mode de paiement est invalide.',
        ];
    }
}

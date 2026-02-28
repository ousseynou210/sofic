<?php

namespace App\Http\Requests\Api;

use App\Http\Requests\Api\Concerns\ApiFormRequest;
use Illuminate\Validation\Rule;

class DepenseRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'compte_id' => ['required', 'integer', 'exists:comptes,id'],
            'date_depense' => ['required', 'date'],
            'categorie' => ['required', 'string', 'max:255'],
            'fournisseur' => ['nullable', 'string', 'max:255'],
            'description' => ['required', 'string'],
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
            'date_depense.required' => 'Le champ date_depense est obligatoire.',
            'date_depense.date' => 'Le champ date_depense doit etre une date valide.',
            'categorie.required' => 'Le champ categorie est obligatoire.',
            'description.required' => 'Le champ description est obligatoire.',
            'montant.required' => 'Le champ montant est obligatoire.',
            'montant.numeric' => 'Le champ montant doit etre numerique.',
            'montant.gt' => 'Le montant doit etre superieur a 0.',
            'mode.required' => 'Le champ mode est obligatoire.',
            'mode.in' => 'Le mode de depense est invalide.',
        ];
    }
}

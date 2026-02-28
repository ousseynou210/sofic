<?php

namespace App\Http\Requests\Api;

use App\Http\Requests\Api\Concerns\ApiFormRequest;
use Illuminate\Validation\Rule;

class FactureStoreRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'numero_facture' => ['required', 'string', 'max:255', Rule::unique('factures', 'numero_facture')],
            'client_id' => ['required', 'integer', 'exists:clients,id'],
            'date_emission' => ['required', 'date'],
            'date_echeance' => ['nullable', 'date', 'after_or_equal:date_emission'],
            'commercial_id' => ['nullable', 'integer', 'exists:commerciaux,id'],
            'point_vente_id' => ['nullable', 'integer', 'exists:points_vente,id'],
            'notes' => ['nullable', 'string'],
            'lignes' => ['required', 'array', 'min:1'],
            'lignes.*.produit_id' => ['nullable', 'integer', 'exists:produits,id'],
            'lignes.*.description' => ['nullable', 'string', 'max:255'],
            'lignes.*.quantite' => ['required', 'integer', 'min:1'],
            'lignes.*.prix_unitaire' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'numero_facture.required' => 'Le champ numero_facture est obligatoire.',
            'numero_facture.string' => 'Le champ numero_facture doit etre une chaine de caracteres.',
            'numero_facture.max' => 'Le champ numero_facture ne doit pas depasser 255 caracteres.',
            'numero_facture.unique' => 'Ce numero_facture existe deja.',
            'client_id.required' => 'Le champ client_id est obligatoire.',
            'client_id.exists' => 'Le client selectionne est invalide.',
            'date_emission.required' => 'Le champ date_emission est obligatoire.',
            'date_emission.date' => 'Le champ date_emission doit etre une date valide.',
            'date_echeance.after_or_equal' => 'La date_echeance doit etre posterieure ou egale a date_emission.',
            'commercial_id.exists' => 'Le commercial selectionne est invalide.',
            'point_vente_id.exists' => 'Le point de vente selectionne est invalide.',
            'lignes.required' => 'Le champ lignes est obligatoire.',
            'lignes.array' => 'Le champ lignes doit etre un tableau.',
            'lignes.min' => 'Une facture doit contenir au moins une ligne.',
            'lignes.*.produit_id.exists' => 'Le produit selectionne est invalide.',
            'lignes.*.quantite.required' => 'Le champ quantite est obligatoire pour chaque ligne.',
            'lignes.*.quantite.integer' => 'La quantite doit etre un entier.',
            'lignes.*.quantite.min' => 'La quantite doit etre au minimum 1.',
            'lignes.*.prix_unitaire.required' => 'Le champ prix_unitaire est obligatoire pour chaque ligne.',
            'lignes.*.prix_unitaire.numeric' => 'Le prix_unitaire doit etre numerique.',
            'lignes.*.prix_unitaire.min' => 'Le prix_unitaire doit etre superieur ou egal a 0.',
        ];
    }
}

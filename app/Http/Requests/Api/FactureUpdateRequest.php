<?php

namespace App\Http\Requests\Api;

use Illuminate\Validation\Rule;

class FactureUpdateRequest extends FactureStoreRequest
{
    public function rules(): array
    {
        $rules = parent::rules();
        $facture = $this->route('facture');
        $factureId = is_object($facture) ? $facture->id : $facture;

        $rules['numero_facture'] = [
            'required',
            'string',
            'max:255',
            Rule::unique('factures', 'numero_facture')->ignore($factureId),
        ];

        return $rules;
    }
}

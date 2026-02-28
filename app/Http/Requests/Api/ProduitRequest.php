<?php

namespace App\Http\Requests\Api;

use App\Http\Requests\Api\Concerns\ApiFormRequest;

class ProduitRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'nom' => ['required', 'string', 'max:255'],
            'categorie' => ['nullable', 'string', 'max:255'],
            'prix_vente' => ['required', 'numeric', 'min:0'],
            'stock_qty' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'nom.required' => 'Le champ nom est obligatoire.',
            'prix_vente.required' => 'Le champ prix_vente est obligatoire.',
            'prix_vente.numeric' => 'Le champ prix_vente doit etre numerique.',
            'stock_qty.integer' => 'Le champ stock_qty doit etre un entier.',
        ];
    }
}

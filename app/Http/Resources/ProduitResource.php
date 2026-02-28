<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProduitResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nom' => $this->nom,
            'categorie' => $this->categorie,
            'prix_vente' => $this->prix_vente,
            'stock_qty' => $this->stock_qty,
            'cree_le' => $this->created_at,
            'mis_a_jour_le' => $this->updated_at,
        ];
    }
}

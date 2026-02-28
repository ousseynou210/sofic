<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FactureLigneResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'produit_id' => $this->produit_id,
            'description' => $this->description,
            'quantite' => $this->quantite,
            'prix_unitaire' => $this->prix_unitaire,
            'total_ligne' => $this->total_ligne,
            'produit' => $this->whenLoaded('produit', function (): ?array {
                if ($this->produit === null) {
                    return null;
                }

                return [
                    'id' => $this->produit->id,
                    'nom' => $this->produit->nom,
                ];
            }),
        ];
    }
}

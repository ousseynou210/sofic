<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaiementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'facture_id' => $this->facture_id,
            'compte_id' => $this->compte_id,
            'date_paiement' => optional($this->date_paiement)?->format('Y-m-d'),
            'montant' => $this->montant,
            'mode' => $this->mode,
            'reference' => $this->reference,
            'compte' => $this->whenLoaded('compte', function (): ?array {
                if ($this->compte === null) {
                    return null;
                }

                return [
                    'id' => $this->compte->id,
                    'nom' => $this->compte->nom,
                    'type' => $this->compte->type,
                ];
            }),
            'cree_le' => $this->created_at,
            'mis_a_jour_le' => $this->updated_at,
        ];
    }
}

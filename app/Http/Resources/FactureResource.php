<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FactureResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $totalFacture = (float) $this->total_facture;
        $montantPaye = (float) ($this->montant_paye ?? $this->paiements()->sum('montant'));
        $resteAPayer = max($totalFacture - $montantPaye, 0);

        return [
            'id' => $this->id,
            'numero_facture' => $this->numero_facture,
            'date_emission' => optional($this->date_emission)?->format('Y-m-d'),
            'date_echeance' => optional($this->date_echeance)?->format('Y-m-d'),
            'client_id' => $this->client_id,
            'commercial_id' => $this->commercial_id,
            'point_vente_id' => $this->point_vente_id,
            'statut' => $this->statut,
            'total_facture' => number_format($totalFacture, 2, '.', ''),
            'montant_paye' => number_format($montantPaye, 2, '.', ''),
            'reste_a_payer' => number_format($resteAPayer, 2, '.', ''),
            'notes' => $this->notes,
            'client' => $this->whenLoaded('client', function (): ?array {
                if ($this->client === null) {
                    return null;
                }

                return [
                    'id' => $this->client->id,
                    'nom' => $this->client->nom,
                ];
            }),
            'commercial' => $this->whenLoaded('commercial', function (): ?array {
                if ($this->commercial === null) {
                    return null;
                }

                return [
                    'id' => $this->commercial->id,
                    'nom' => $this->commercial->nom,
                ];
            }),
            'point_vente' => $this->whenLoaded('pointVente', function (): ?array {
                if ($this->pointVente === null) {
                    return null;
                }

                return [
                    'id' => $this->pointVente->id,
                    'nom' => $this->pointVente->nom,
                ];
            }),
            'lignes' => FactureLigneResource::collection($this->whenLoaded('factureLignes')),
            'cree_le' => $this->created_at,
            'mis_a_jour_le' => $this->updated_at,
        ];
    }
}

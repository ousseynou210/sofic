@extends('site.layouts.app')

@section('title', 'Suivi facture - SOFIC')

@section('content')
    <h1 class="h4 mb-3">Suivi de facture</h1>

    <div class="card mb-4">
        <div class="card-body">
            <form method="POST" action="{{ route('site.factures.suivi.resultat') }}" class="row g-3">
                @csrf
                <div class="col-md-6">
                    <label class="form-label">Numero facture</label>
                    <input
                        type="text"
                        class="form-control"
                        name="numero_facture"
                        value="{{ old('numero_facture', $numeroFacture ?? '') }}"
                        placeholder="FAC-2026-000001"
                        required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email ou telephone client</label>
                    <input
                        type="text"
                        class="form-control"
                        name="identifiant_client"
                        value="{{ old('identifiant_client', $identifiantClient ?? '') }}"
                        placeholder="exemple@mail.com ou 770000000"
                        required>
                </div>
                <div class="col-12">
                    <button class="btn btn-primary" type="submit">Verifier</button>
                </div>
            </form>
        </div>
    </div>

    @isset($facture)
        @if($facture)
            @php
                $montantPaye = (float) ($facture->montant_paye ?? 0);
                $reste = max((float) $facture->total_facture - $montantPaye, 0);
            @endphp

            <div class="card">
                <div class="card-header">
                    Facture {{ $facture->numero_facture }}
                </div>
                <div class="card-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-4"><strong>Statut:</strong> {{ $facture->statut }}</div>
                        <div class="col-md-4"><strong>Total:</strong> {{ number_format((float) $facture->total_facture, 2, ',', ' ') }} FCFA</div>
                        <div class="col-md-4"><strong>Paye:</strong> {{ number_format($montantPaye, 2, ',', ' ') }} FCFA</div>
                        <div class="col-md-4"><strong>Reste:</strong> {{ number_format($reste, 2, ',', ' ') }} FCFA</div>
                        <div class="col-md-4"><strong>Date emission:</strong> {{ optional($facture->date_emission)->format('d/m/Y') }}</div>
                        <div class="col-md-4"><strong>Client:</strong> {{ $facture->client?->nom }}</div>
                    </div>

                    <h2 class="h6">Lignes</h2>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                            <tr>
                                <th>Produit/Description</th>
                                <th>Quantite</th>
                                <th>Prix unitaire</th>
                                <th>Total</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($facture->factureLignes as $ligne)
                                <tr>
                                    <td>{{ $ligne->produit?->nom ?? $ligne->description ?? '-' }}</td>
                                    <td>{{ $ligne->quantite }}</td>
                                    <td>{{ number_format((float) $ligne->prix_unitaire, 2, ',', ' ') }}</td>
                                    <td>{{ number_format((float) $ligne->total_ligne, 2, ',', ' ') }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @else
            <div class="alert alert-warning">
                Aucune facture ne correspond aux informations saisies.
            </div>
        @endif
    @endisset
@endsection

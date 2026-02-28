@extends('admin.layouts.app')
@section('title','Detail facture')

@section('content')
    @php
        $estAdmin = auth()->user()?->role === 'admin';
        $statusClass = match($facture->statut) {
            'PAYEE' => 'status-payee',
            'PARTIELLE' => 'status-partielle',
            'ANNULEE' => 'status-annulee',
            'ENVOYEE' => 'status-envoyee',
            default => 'status-brouillon',
        };
    @endphp
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h1 class="h3 mb-1">Facture {{ $facture->numero_facture }}</h1>
            <span class="status-badge {{ $statusClass }}">{{ $facture->statut }}</span>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a class="btn btn-light" href="{{ route('admin.factures.index') }}">Retour</a>
            <a class="btn btn-outline-primary" href="#"><i class="bi bi-download me-1"></i>Telecharger PDF</a>
            @if($estAdmin && $facture->statut !== 'ANNULEE')
                <a class="btn btn-outline-secondary" href="{{ route('admin.factures.edit',$facture) }}">Modifier</a>
                <form method="POST" action="{{ route('admin.factures.annuler',$facture) }}" data-confirm="Annuler cette facture ?">
                    @csrf
                    <button class="btn btn-warning" type="submit">Annuler</button>
                </form>
            @endif
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-6 col-xl-3">
            <div class="card soft-card h-100"><div class="card-body"><small class="text-muted">Client</small><h6 class="mt-2 mb-0">{{ $facture->client?->nom }}</h6></div></div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card soft-card h-100"><div class="card-body"><small class="text-muted">Total</small><h6 class="mt-2 mb-0">{{ number_format($total,2,',',' ') }} FCFA</h6></div></div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card soft-card h-100"><div class="card-body"><small class="text-muted">Paye</small><h6 class="mt-2 mb-0 text-success">{{ number_format($paye,2,',',' ') }} FCFA</h6></div></div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card soft-card h-100"><div class="card-body"><small class="text-muted">Reste</small><h6 class="mt-2 mb-0">{{ number_format($reste,2,',',' ') }} FCFA</h6></div></div>
        </div>
    </div>

    <div class="card soft-card mb-4">
        <div class="card-header bg-white border-0 pt-3"><h6 class="mb-0">Lignes produits</h6></div>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                <tr><th>Produit</th><th>Description</th><th class="text-end">Quantite</th><th class="text-end">Prix unitaire</th><th class="text-end">Total</th></tr>
                </thead>
                <tbody>
                @foreach($facture->factureLignes as $ligne)
                    <tr>
                        <td>{{ $ligne->produit?->nom ?? '-' }}</td>
                        <td>{{ $ligne->description ?: '-' }}</td>
                        <td class="text-end">{{ $ligne->quantite }}</td>
                        <td class="text-end">{{ number_format((float)$ligne->prix_unitaire,2,',',' ') }}</td>
                        <td class="text-end fw-semibold">{{ number_format((float)$ligne->total_ligne,2,',',' ') }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="row g-3">
        @if($estAdmin)
            <div class="col-xl-4">
                <div class="card soft-card">
                    <div class="card-header bg-white border-0 pt-3">
                        <h6 class="mb-0">Ajouter paiement</h6>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('admin.factures.paiements.store',$facture) }}">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">Compte</label>
                                <select class="form-select" name="compte_id" required>
                                    @foreach($comptes as $compte)
                                        <option value="{{ $compte->id }}">{{ $compte->nom }} ({{ $compte->type }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Date paiement</label>
                                <input type="date" class="form-control" name="date_paiement" value="{{ now()->format('Y-m-d') }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Montant</label>
                                <input type="number" step="0.01" class="form-control" name="montant" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Mode</label>
                                <select class="form-select" name="mode" required>
                                    @foreach(['ESPECES','WAVE','ORANGE_MONEY','VIREMENT','CHEQUE'] as $mode)
                                        <option value="{{ $mode }}">{{ $mode }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Reference</label>
                                <input class="form-control" name="reference">
                            </div>
                            <button class="btn btn-primary w-100" @disabled($facture->statut === 'ANNULEE')>Ajouter paiement</button>
                        </form>
                    </div>
                </div>
            </div>
        @endif

        <div class="{{ $estAdmin ? 'col-xl-8' : 'col-12' }}">
            <div class="card soft-card">
                <div class="card-header bg-white border-0 pt-3">
                    <h6 class="mb-0">Paiements</h6>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                        <tr>
                            <th>Date</th>
                            <th>Compte</th>
                            <th>Mode</th>
                            <th class="text-end">Montant</th>
                            <th>Reference</th>
                            @if($estAdmin)
                                <th></th>
                            @endif
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($facture->paiements as $paiement)
                            <tr>
                                <td>{{ optional($paiement->date_paiement)->format('d/m/Y') }}</td>
                                <td>{{ $paiement->compte?->nom }}</td>
                                <td>{{ $paiement->mode }}</td>
                                <td class="text-end text-success">{{ number_format((float)$paiement->montant,2,',',' ') }}</td>
                                <td>{{ $paiement->reference ?: '-' }}</td>
                                @if($estAdmin)
                                    <td class="text-end">
                                        <form method="POST" action="{{ route('admin.paiements.destroy',$paiement) }}" data-confirm="Supprimer ce paiement ?">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger" type="submit">Supprimer</button>
                                        </form>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr><td colspan="{{ $estAdmin ? '6' : '5' }}" class="text-center text-muted py-4">Aucun paiement</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

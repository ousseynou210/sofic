@extends('admin.layouts.app')
@section('title','Factures')

@section('content')
    @php
        $estAdmin = auth()->user()?->role === 'admin';
    @endphp
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h1 class="h3 mb-1">Factures</h1>
            <p class="text-muted mb-0">Gestion administrative des factures</p>
        </div>
        @if($estAdmin)
            <a class="btn btn-primary" href="{{ route('admin.factures.create') }}">
                <i class="bi bi-plus-lg me-1"></i>Ajouter
            </a>
        @endif
    </div>

    <div class="card soft-card mb-3">
        <div class="card-body">
            <form class="row g-2">
                <div class="col-12 col-lg-4">
                    <label class="form-label mb-1">Recherche</label>
                    <input class="form-control" name="recherche" value="{{ $recherche }}" placeholder="Numero facture">
                </div>
                <div class="col-12 col-lg-3">
                    <label class="form-label mb-1">Statut</label>
                    <select class="form-select" name="statut">
                        <option value="">Tous</option>
                        @foreach(['BROUILLON','ENVOYEE','PARTIELLE','PAYEE','ANNULEE'] as $s)
                            <option value="{{ $s }}" @selected(request('statut')===$s)>{{ $s }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-lg-2 d-flex align-items-end">
                    <button class="btn btn-light w-100"><i class="bi bi-funnel me-1"></i>Filtrer</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card soft-card">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                <tr>
                    <th>Numero</th>
                    <th>Client</th>
                    <th class="text-end">Total</th>
                    <th class="text-end">Paye</th>
                    <th class="text-end">Reste</th>
                    <th>Statut</th>
                    <th class="text-end">Actions</th>
                </tr>
                </thead>
                <tbody>
                @forelse($factures as $facture)
                    @php
                        $paye = (float)($facture->montant_paye ?? 0);
                        $total = (float)$facture->total_facture;
                        $reste = max($total - $paye, 0);
                        $statusClass = match($facture->statut) {
                            'PAYEE' => 'status-payee',
                            'PARTIELLE' => 'status-partielle',
                            'ANNULEE' => 'status-annulee',
                            'ENVOYEE' => 'status-envoyee',
                            default => 'status-brouillon',
                        };
                    @endphp
                    <tr>
                        <td class="fw-semibold">{{ $facture->numero_facture }}</td>
                        <td>{{ $facture->client?->nom }}</td>
                        <td class="text-end">{{ number_format($total,2,',',' ') }}</td>
                        <td class="text-end text-success">{{ number_format($paye,2,',',' ') }}</td>
                        <td class="text-end">{{ number_format($reste,2,',',' ') }}</td>
                        <td><span class="status-badge {{ $statusClass }}">{{ $facture->statut }}</span></td>
                        <td class="text-end">
                            <a class="btn btn-sm btn-light" href="{{ route('admin.factures.show',$facture) }}">Voir</a>
                            @if($estAdmin && $facture->statut !== 'ANNULEE')
                                <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.factures.edit',$facture) }}">Modifier</a>
                            @endif
                            @if($estAdmin && $facture->statut === 'BROUILLON' && ((float)($facture->montant_paye ?? 0) <= 0))
                                <form class="d-inline" method="POST" action="{{ route('admin.factures.destroy',$facture) }}" data-confirm="Supprimer cette facture ?">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" type="submit">Supprimer</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">Aucune facture trouvee</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white border-0">
            {{ $factures->links() }}
        </div>
    </div>
@endsection

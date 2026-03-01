@extends('admin.layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Dashboard</h1>
            <p class="text-muted mb-0">Periode: {{ $resume['intervalle']['du'] }} au {{ $resume['intervalle']['au'] }}</p>
        </div>
        <form method="GET" action="{{ route('admin.dashboard') }}" class="row g-2">
            <div class="col-12 col-sm-auto">
            <select name="periode" class="form-select">
                <option value="mois" @selected($periode==='mois')>Mois</option>
                <option value="trimestre" @selected($periode==='trimestre')>Trimestre</option>
                <option value="annee" @selected($periode==='annee')>Annee</option>
            </select>
            </div>
            <div class="col-12 col-sm-auto">
            <button class="btn btn-primary w-100"><i class="bi bi-funnel me-1"></i>Filtrer</button>
            </div>
        </form>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card soft-card h-100">
                <div class="card-body">
                    <small class="text-muted">Chiffre d'affaires</small>
                    <h4 class="mt-2 mb-0">{{ number_format($resume['chiffre_affaires'],2,',',' ') }} FCFA</h4>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card soft-card h-100">
                <div class="card-body">
                    <small class="text-muted">Encaissements</small>
                    <h4 class="mt-2 mb-0 text-success">{{ number_format($resume['encaisse'],2,',',' ') }} FCFA</h4>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card soft-card h-100">
                <div class="card-body">
                    <small class="text-muted">Depenses</small>
                    <h4 class="mt-2 mb-0">{{ number_format($resume['depenses'],2,',',' ') }} FCFA</h4>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card soft-card h-100">
                <div class="card-body">
                    <small class="text-muted">Impayes</small>
                    <h4 class="mt-2 mb-0">{{ number_format($resume['impayes'],2,',',' ') }} FCFA</h4>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-xl-8">
            <div class="card soft-card h-100">
                <div class="card-header bg-white border-0 pt-3">
                    <h6 class="mb-0">CA par mois</h6>
                </div>
                <div class="card-body">
                    <canvas id="caParMois" height="110"></canvas>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card soft-card h-100">
                <div class="card-header bg-white border-0 pt-3">
                    <h6 class="mb-0">Depenses par categorie</h6>
                </div>
                <div class="card-body">
                    <canvas id="depensesParCategorie" height="180"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-xl-6">
            <div class="card soft-card">
                <div class="card-header bg-white border-0 pt-3">
                    <h6 class="mb-0">Top clients</h6>
                </div>
                <ul class="list-group list-group-flush">
                    @forelse($resume['graphiques']['top_clients'] as $row)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>{{ $row['nom'] }}</span>
                            <strong>{{ number_format($row['chiffre_affaires'],2,',',' ') }}</strong>
                        </li>
                    @empty
                        <li class="list-group-item text-muted">Aucune donnee</li>
                    @endforelse
                </ul>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card soft-card">
                <div class="card-header bg-white border-0 pt-3">
                    <h6 class="mb-0">Top produits</h6>
                </div>
                <ul class="list-group list-group-flush">
                    @forelse($resume['graphiques']['top_produits'] as $row)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>{{ $row['nom'] }}</span>
                            <strong>{{ $row['quantite_vendue'] }}</strong>
                        </li>
                    @empty
                        <li class="list-group-item text-muted">Aucune donnee</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>

    <div class="card soft-card">
        <div class="card-header bg-white border-0 pt-3 d-flex justify-content-between">
            <h6 class="mb-0">Dernieres factures</h6>
            <a href="{{ route('admin.factures.index') }}" class="btn btn-sm btn-light">Voir tout</a>
        </div>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                <tr>
                    <th>Numero</th>
                    <th>Client</th>
                    <th>Total</th>
                    <th>Paye</th>
                    <th>Reste</th>
                    <th>Statut</th>
                </tr>
                </thead>
                <tbody>
                @forelse($dernieresFactures as $facture)
                    @php
                        $paye = (float)($facture->montant_paye ?? 0);
                        $reste = max((float)$facture->total_facture - $paye, 0);
                        $statusClass = match($facture->statut) {
                            'PAYEE' => 'status-payee',
                            'PARTIELLE' => 'status-partielle',
                            'ANNULEE' => 'status-annulee',
                            'ENVOYEE' => 'status-envoyee',
                            default => 'status-brouillon',
                        };
                    @endphp
                    <tr>
                        <td><a href="{{ route('admin.factures.show', $facture) }}">{{ $facture->numero_facture }}</a></td>
                        <td>{{ $facture->client?->nom }}</td>
                        <td>{{ number_format((float)$facture->total_facture,2,',',' ') }}</td>
                        <td>{{ number_format($paye,2,',',' ') }}</td>
                        <td>{{ number_format($reste,2,',',' ') }}</td>
                        <td><span class="status-badge {{ $statusClass }}">{{ $facture->statut }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">Aucune facture recente</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const caParMois = @json($resume['graphiques']['ca_par_mois']);
        const depensesParCategorie = @json($resume['graphiques']['depenses_par_categorie']);

        new Chart(document.getElementById('caParMois'), {
            type: 'line',
            data: {
                labels: caParMois.labels,
                datasets: [{
                    label: 'CA',
                    data: caParMois.valeurs,
                    borderColor: '#1f6feb',
                    backgroundColor: 'rgba(31, 111, 235, 0.16)',
                    fill: true,
                    tension: .35
                }]
            },
            options: { plugins: { legend: { display: false } }, maintainAspectRatio: false }
        });

        new Chart(document.getElementById('depensesParCategorie'), {
            type: 'doughnut',
            data: {
                labels: depensesParCategorie.labels,
                datasets: [{ data: depensesParCategorie.valeurs, backgroundColor: ['#1f6feb','#15a46f','#f59e0b','#ef4444','#0ea5e9','#64748b'] }]
            },
            options: { plugins: { legend: { position: 'bottom' } }, maintainAspectRatio: false }
        });
    </script>
@endpush

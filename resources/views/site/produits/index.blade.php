@extends('site.layouts.app')

@section('title', 'Produits - SOFIC')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h1 class="h4 mb-0">Catalogue produits</h1>
    </div>

    <form method="GET" action="{{ route('site.produits') }}" class="row g-2 mb-3">
        <div class="col-12 col-md-9">
            <input
                type="text"
                class="form-control"
                name="recherche"
                value="{{ $recherche }}"
                placeholder="Rechercher un produit ou une categorie">
        </div>
        <div class="col-12 col-md-3 d-grid">
            <button class="btn btn-primary" type="submit">Rechercher</button>
        </div>
    </form>

    <div class="row g-3">
        @forelse($produits as $produit)
            <div class="col-md-6 col-lg-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h2 class="h6">{{ $produit->nom }}</h2>
                        <p class="text-muted mb-2">{{ $produit->categorie ?: 'Non classe' }}</p>
                        <div class="fw-bold">{{ number_format((float) $produit->prix_vente, 2, ',', ' ') }} FCFA</div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-info mb-0">Aucun produit trouve.</div>
            </div>
        @endforelse
    </div>

    <div class="mt-3">
        {{ $produits->links() }}
    </div>
@endsection

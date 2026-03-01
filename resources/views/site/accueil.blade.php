@extends('site.layouts.app')

@section('title', 'Accueil - SOFIC')

@section('content')
    <div class="p-3 p-md-5 mb-4 bg-light rounded-3 border">
        <div class="container-fluid py-2">
            <h1 class="display-6 fw-bold">Bienvenue sur SOFIC</h1>
            <p class="col-lg-8 fs-5 mb-4">
                Consultez nos produits et suivez vos factures directement en ligne.
            </p>
            <a href="{{ route('site.produits') }}" class="btn btn-primary btn-lg d-block d-sm-inline-block">Voir les produits</a>
        </div>
    </div>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h2 class="h4 mb-0">Produits recents</h2>
        <a href="{{ route('site.produits') }}" class="btn btn-outline-secondary btn-sm">Tout voir</a>
    </div>

    <div class="row g-3">
        @forelse($produits as $produit)
            <div class="col-md-6 col-lg-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h3 class="h6">{{ $produit->nom }}</h3>
                        <p class="text-muted mb-2">{{ $produit->categorie ?: 'Non classe' }}</p>
                        <div class="fw-bold">{{ number_format((float) $produit->prix_vente, 2, ',', ' ') }} FCFA</div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-info mb-0">Aucun produit disponible pour le moment.</div>
            </div>
        @endforelse
    </div>
@endsection

@extends('admin.layouts.app')
@section('title', $produit->exists ? 'Modifier produit' : 'Nouveau produit')
@section('content')
    <h1 class="h4 mb-3">{{ $produit->exists ? 'Modifier produit' : 'Nouveau produit' }}</h1>
    <form method="POST" action="{{ $produit->exists ? route('admin.produits.update',$produit) : route('admin.produits.store') }}">
        @csrf @if($produit->exists) @method('PUT') @endif
        <div class="row g-3">
            <div class="col-md-6"><label class="form-label">Nom</label><input class="form-control" name="nom" value="{{ old('nom',$produit->nom) }}" required></div>
            <div class="col-md-6"><label class="form-label">Categorie</label><input class="form-control" name="categorie" value="{{ old('categorie',$produit->categorie) }}"></div>
            <div class="col-md-6"><label class="form-label">Prix vente</label><input type="number" step="0.01" class="form-control" name="prix_vente" value="{{ old('prix_vente',$produit->prix_vente) }}" required></div>
            <div class="col-md-6"><label class="form-label">Stock</label><input type="number" class="form-control" name="stock_qty" value="{{ old('stock_qty',$produit->stock_qty ?? 0) }}" required></div>
        </div>
        <div class="mt-3">
            <button class="btn btn-primary">Enregistrer</button>
            <a class="btn btn-secondary" href="{{ route('admin.produits.index') }}">Retour</a>
        </div>
    </form>
@endsection

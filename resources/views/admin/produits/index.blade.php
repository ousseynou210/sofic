@extends('admin.layouts.app')
@section('title','Produits')
@section('content')
    @php
        $estAdmin = auth()->user()?->role === 'admin';
    @endphp
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h1 class="h4 mb-0">Produits</h1>
        @if($estAdmin)
            <a class="btn btn-primary" href="{{ route('admin.produits.create') }}">Nouveau produit</a>
        @endif
    </div>
    <form class="row g-2 mb-3">
        <div class="col-12 col-md-6"><input class="form-control" name="recherche" value="{{ $recherche }}" placeholder="Recherche"></div>
        <div class="col-12 col-md-auto"><button class="btn btn-outline-secondary w-100">Filtrer</button></div>
    </form>
    <div class="table-responsive">
        <table class="table table-striped">
            <thead><tr><th>Nom</th><th>Categorie</th><th class="text-end">Prix</th><th class="text-end">Stock</th><th></th></tr></thead>
            <tbody>
            @foreach($produits as $produit)
                <tr>
                    <td>{{ $produit->nom }}</td>
                    <td>{{ $produit->categorie }}</td>
                    <td class="text-end">{{ number_format((float)$produit->prix_vente,2,',',' ') }}</td>
                    <td class="text-end">{{ $produit->stock_qty }}</td>
                    <td class="text-end">
                        @if($estAdmin)
                            <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.produits.edit',$produit) }}">Modifier</a>
                            <form class="d-inline" method="POST" action="{{ route('admin.produits.destroy',$produit) }}">@csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Supprimer ?')">Supprimer</button>
                            </form>
                        @else
                            <span class="text-muted small">Lecture seule</span>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    {{ $produits->links() }}
@endsection

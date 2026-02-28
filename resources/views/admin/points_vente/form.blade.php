@extends('admin.layouts.app')
@section('title', $pointVente->exists ? 'Modifier point de vente' : 'Nouveau point de vente')
@section('content')
    <h1 class="h4 mb-3">{{ $pointVente->exists ? 'Modifier point de vente' : 'Nouveau point de vente' }}</h1>
    <form method="POST" action="{{ $pointVente->exists ? route('admin.points-vente.update',$pointVente) : route('admin.points-vente.store') }}">
        @csrf @if($pointVente->exists) @method('PUT') @endif
        <div class="row g-3">
            <div class="col-md-6"><label class="form-label">Nom</label><input class="form-control" name="nom" value="{{ old('nom',$pointVente->nom) }}" required></div>
            <div class="col-md-6"><label class="form-label">Telephone</label><input class="form-control" name="telephone" value="{{ old('telephone',$pointVente->telephone) }}"></div>
            <div class="col-md-12"><label class="form-label">Adresse</label><input class="form-control" name="adresse" value="{{ old('adresse',$pointVente->adresse) }}"></div>
        </div>
        <div class="mt-3">
            <button class="btn btn-primary">Enregistrer</button>
            <a class="btn btn-secondary" href="{{ route('admin.points-vente.index') }}">Retour</a>
        </div>
    </form>
@endsection

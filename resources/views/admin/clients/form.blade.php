@extends('admin.layouts.app')
@section('title', $client->exists ? 'Modifier client' : 'Nouveau client')
@section('content')
    <h1 class="h4 mb-3">{{ $client->exists ? 'Modifier client' : 'Nouveau client' }}</h1>
    <form method="POST" action="{{ $client->exists ? route('admin.clients.update',$client) : route('admin.clients.store') }}">
        @csrf
        @if($client->exists) @method('PUT') @endif
        <div class="row g-3">
            <div class="col-md-6"><label class="form-label">Nom</label><input class="form-control" name="nom" value="{{ old('nom',$client->nom) }}" required></div>
            <div class="col-md-6"><label class="form-label">Telephone</label><input class="form-control" name="telephone" value="{{ old('telephone',$client->telephone) }}"></div>
            <div class="col-md-6"><label class="form-label">Email</label><input type="email" class="form-control" name="email" value="{{ old('email',$client->email) }}"></div>
            <div class="col-md-6"><label class="form-label">Adresse</label><input class="form-control" name="adresse" value="{{ old('adresse',$client->adresse) }}"></div>
        </div>
        <div class="mt-3">
            <button class="btn btn-primary">Enregistrer</button>
            <a class="btn btn-secondary" href="{{ route('admin.clients.index') }}">Retour</a>
        </div>
    </form>
@endsection

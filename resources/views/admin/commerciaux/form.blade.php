@extends('admin.layouts.app')
@section('title', $commercial->exists ? 'Modifier commercial' : 'Nouveau commercial')
@section('content')
    <h1 class="h4 mb-3">{{ $commercial->exists ? 'Modifier commercial' : 'Nouveau commercial' }}</h1>
    <form method="POST" action="{{ $commercial->exists ? route('admin.commerciaux.update',$commercial) : route('admin.commerciaux.store') }}">
        @csrf @if($commercial->exists) @method('PUT') @endif
        <div class="row g-3">
            <div class="col-md-6"><label class="form-label">Nom</label><input class="form-control" name="nom" value="{{ old('nom',$commercial->nom) }}" required></div>
            <div class="col-md-6"><label class="form-label">Telephone</label><input class="form-control" name="telephone" value="{{ old('telephone',$commercial->telephone) }}"></div>
            <div class="col-md-6"><label class="form-label">Email</label><input type="email" class="form-control" name="email" value="{{ old('email',$commercial->email) }}"></div>
        </div>
        <div class="mt-3">
            <button class="btn btn-primary">Enregistrer</button>
            <a class="btn btn-secondary" href="{{ route('admin.commerciaux.index') }}">Retour</a>
        </div>
    </form>
@endsection

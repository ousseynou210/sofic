@extends('admin.layouts.app')
@section('title', $compte->exists ? 'Modifier compte' : 'Nouveau compte')
@section('content')
    <h1 class="h4 mb-3">{{ $compte->exists ? 'Modifier compte' : 'Nouveau compte' }}</h1>
    <form method="POST" action="{{ $compte->exists ? route('admin.comptes.update',$compte) : route('admin.comptes.store') }}">
        @csrf @if($compte->exists) @method('PUT') @endif
        <div class="row g-3">
            <div class="col-md-6"><label class="form-label">Nom</label><input class="form-control" name="nom" value="{{ old('nom',$compte->nom) }}" required></div>
            <div class="col-md-6">
                <label class="form-label">Type</label>
                <select class="form-select" name="type" required>
                    @foreach(['CAISSE','BANQUE','MOBILE_MONEY','AUTRE'] as $type)
                        <option value="{{ $type }}" @selected(old('type',$compte->type)===$type)>{{ $type }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6"><label class="form-label">Solde initial</label><input type="number" step="0.01" class="form-control" name="solde_initial" value="{{ old('solde_initial',$compte->solde_initial ?? 0) }}" required></div>
        </div>
        <div class="mt-3">
            <button class="btn btn-primary">Enregistrer</button>
            <a class="btn btn-secondary" href="{{ route('admin.comptes.index') }}">Retour</a>
        </div>
    </form>
@endsection

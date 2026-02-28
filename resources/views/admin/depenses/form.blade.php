@extends('admin.layouts.app')
@section('title', $depense->exists ? 'Modifier depense' : 'Nouvelle depense')
@section('content')
    <h1 class="h4 mb-3">{{ $depense->exists ? 'Modifier depense' : 'Nouvelle depense' }}</h1>
    <form method="POST" action="{{ $depense->exists ? route('admin.depenses.update',$depense) : route('admin.depenses.store') }}">
        @csrf @if($depense->exists) @method('PUT') @endif
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Compte</label>
                <select class="form-select" name="compte_id" required>
                    @foreach($comptes as $compte)
                        <option value="{{ $compte->id }}" @selected((string)old('compte_id',$depense->compte_id)===(string)$compte->id)>{{ $compte->nom }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4"><label class="form-label">Date</label><input type="date" class="form-control" name="date_depense" value="{{ old('date_depense',optional($depense->date_depense)->format('Y-m-d')) }}" required></div>
            <div class="col-md-4"><label class="form-label">Categorie</label><input class="form-control" name="categorie" value="{{ old('categorie',$depense->categorie) }}" required></div>
            <div class="col-md-6"><label class="form-label">Fournisseur</label><input class="form-control" name="fournisseur" value="{{ old('fournisseur',$depense->fournisseur) }}"></div>
            <div class="col-md-3"><label class="form-label">Montant</label><input type="number" step="0.01" class="form-control" name="montant" value="{{ old('montant',$depense->montant) }}" required></div>
            <div class="col-md-3">
                <label class="form-label">Mode</label>
                <select class="form-select" name="mode" required>
                    @foreach(['ESPECES','WAVE','ORANGE_MONEY','VIREMENT','CHEQUE'] as $mode)
                        <option value="{{ $mode }}" @selected(old('mode',$depense->mode)===$mode)>{{ $mode }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6"><label class="form-label">Reference</label><input class="form-control" name="reference" value="{{ old('reference',$depense->reference) }}"></div>
            <div class="col-md-12"><label class="form-label">Description</label><textarea class="form-control" name="description" rows="3" required>{{ old('description',$depense->description) }}</textarea></div>
        </div>
        <div class="mt-3">
            <button class="btn btn-primary">Enregistrer</button>
            <a class="btn btn-secondary" href="{{ route('admin.depenses.index') }}">Retour</a>
        </div>
    </form>
@endsection

@extends('admin.layouts.app')
@section('title','Depenses')
@section('content')
    @php
        $estAdmin = auth()->user()?->role === 'admin';
    @endphp
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">Depenses</h1>
        @if($estAdmin)
            <a class="btn btn-primary" href="{{ route('admin.depenses.create') }}">Nouvelle depense</a>
        @endif
    </div>
    <form class="row g-2 mb-3">
        <div class="col-md-2"><input type="date" class="form-control" name="du" value="{{ $du }}"></div>
        <div class="col-md-2"><input type="date" class="form-control" name="au" value="{{ $au }}"></div>
        <div class="col-md-3"><input class="form-control" name="categorie" value="{{ $categorie }}" placeholder="Categorie"></div>
        <div class="col-md-3">
            <select name="compte_id" class="form-select">
                <option value="">Tous les comptes</option>
                @foreach($comptes as $compte)
                    <option value="{{ $compte->id }}" @selected((string)$compteId === (string)$compte->id)>{{ $compte->nom }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-auto"><button class="btn btn-outline-secondary">Filtrer</button></div>
    </form>
    <table class="table table-striped">
        <thead><tr><th>Date</th><th>Compte</th><th>Categorie</th><th class="text-end">Montant</th><th></th></tr></thead>
        <tbody>
        @foreach($depenses as $depense)
            <tr>
                <td>{{ optional($depense->date_depense)->format('Y-m-d') }}</td>
                <td>{{ $depense->compte?->nom }}</td>
                <td>{{ $depense->categorie }}</td>
                <td class="text-end">{{ number_format((float)$depense->montant,2,',',' ') }}</td>
                <td class="text-end">
                    @if($estAdmin)
                        <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.depenses.edit',$depense) }}">Modifier</a>
                        <form class="d-inline" method="POST" action="{{ route('admin.depenses.destroy',$depense) }}">@csrf @method('DELETE')
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
    {{ $depenses->links() }}
@endsection

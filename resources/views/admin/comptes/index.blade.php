@extends('admin.layouts.app')
@section('title','Comptes')
@section('content')
    @php
        $estAdmin = auth()->user()?->role === 'admin';
    @endphp
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">Comptes</h1>
        @if($estAdmin)
            <a class="btn btn-primary" href="{{ route('admin.comptes.create') }}">Nouveau compte</a>
        @endif
    </div>
    <form class="row g-2 mb-3">
        <div class="col-md-4"><input class="form-control" name="recherche" value="{{ $recherche }}" placeholder="Recherche"></div>
        <div class="col-auto"><button class="btn btn-outline-secondary">Filtrer</button></div>
    </form>
    <table class="table table-striped">
        <thead><tr><th>Nom</th><th>Type</th><th class="text-end">Solde initial</th><th class="text-end">Entrees paiements</th><th class="text-end">Sorties depenses</th><th class="text-end">Solde actuel</th><th></th></tr></thead>
        <tbody>
        @foreach($comptes as $compte)
            @php
                $soldeInitial = (float) $compte->solde_initial;
                $entrees = (float) ($compte->total_entrees ?? 0);
                $sorties = (float) ($compte->total_sorties ?? 0);
                $soldeActuel = $soldeInitial + $entrees - $sorties;
            @endphp
            <tr>
                <td>{{ $compte->nom }}</td>
                <td>{{ $compte->type }}</td>
                <td class="text-end">{{ number_format($soldeInitial,2,',',' ') }}</td>
                <td class="text-end text-success">+ {{ number_format($entrees,2,',',' ') }}</td>
                <td class="text-end text-danger">- {{ number_format($sorties,2,',',' ') }}</td>
                <td class="text-end fw-semibold">{{ number_format($soldeActuel,2,',',' ') }}</td>
                <td class="text-end">
                    @if($estAdmin)
                        <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.comptes.edit',$compte) }}">Modifier</a>
                        <form class="d-inline" method="POST" action="{{ route('admin.comptes.destroy',$compte) }}">@csrf @method('DELETE')
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
    {{ $comptes->links() }}
@endsection

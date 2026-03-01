@extends('admin.layouts.app')
@section('title','Points de vente')
@section('content')
    @php
        $estAdmin = auth()->user()?->role === 'admin';
    @endphp
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h1 class="h4 mb-0">Points de vente</h1>
        @if($estAdmin)
            <a class="btn btn-primary" href="{{ route('admin.points-vente.create') }}">Nouveau point de vente</a>
        @endif
    </div>
    <form class="row g-2 mb-3">
        <div class="col-12 col-md-6"><input class="form-control" name="recherche" value="{{ $recherche }}" placeholder="Recherche"></div>
        <div class="col-12 col-md-auto"><button class="btn btn-outline-secondary w-100">Filtrer</button></div>
    </form>
    <div class="table-responsive">
        <table class="table table-striped">
            <thead><tr><th>Nom</th><th>Adresse</th><th>Telephone</th><th></th></tr></thead>
            <tbody>
            @foreach($pointsVente as $point)
                <tr>
                    <td>{{ $point->nom }}</td>
                    <td>{{ $point->adresse }}</td>
                    <td>{{ $point->telephone }}</td>
                    <td class="text-end">
                        @if($estAdmin)
                            <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.points-vente.edit',$point) }}">Modifier</a>
                            <form class="d-inline" method="POST" action="{{ route('admin.points-vente.destroy',$point) }}">@csrf @method('DELETE')
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
    {{ $pointsVente->links() }}
@endsection

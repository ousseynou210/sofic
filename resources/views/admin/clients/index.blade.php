@extends('admin.layouts.app')
@section('title','Clients')
@section('content')
    @php
        $estAdmin = auth()->user()?->role === 'admin';
    @endphp
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">Clients</h1>
        @if($estAdmin)
            <a class="btn btn-primary" href="{{ route('admin.clients.create') }}">Nouveau client</a>
        @endif
    </div>
    <form class="row g-2 mb-3">
        <div class="col-md-4"><input class="form-control" name="recherche" value="{{ $recherche }}" placeholder="Recherche"></div>
        <div class="col-auto"><button class="btn btn-outline-secondary">Filtrer</button></div>
    </form>
    <div class="table-responsive">
        <table class="table table-striped">
            <thead><tr><th>Nom</th><th>Telephone</th><th>Email</th><th></th></tr></thead>
            <tbody>
            @foreach($clients as $client)
                <tr>
                    <td>{{ $client->nom }}</td>
                    <td>{{ $client->telephone }}</td>
                    <td>{{ $client->email }}</td>
                    <td class="text-end">
                        @if($estAdmin)
                            <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.clients.edit',$client) }}">Modifier</a>
                            <form class="d-inline" method="POST" action="{{ route('admin.clients.destroy',$client) }}">
                                @csrf @method('DELETE')
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
    {{ $clients->links() }}
@endsection

@extends('admin.layouts.app')

@section('title', 'Parametres du compte')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Parametres du compte</h1>
            <p class="text-muted mb-0">Modifiez votre email et votre mot de passe.</p>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-xl-6">
            <div class="card soft-card h-100">
                <div class="card-header bg-white border-0 pt-3">
                    <h6 class="mb-0">Modifier l email</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.parametres.email.update') }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label">Nouvel email</label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', auth()->user()?->email) }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Mot de passe actuel</label>
                            <input type="password" name="mot_de_passe" class="form-control @error('mot_de_passe') is-invalid @enderror" required>
                            @error('mot_de_passe')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <button class="btn btn-primary" type="submit">Mettre a jour l email</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-xl-6">
            <div class="card soft-card h-100">
                <div class="card-header bg-white border-0 pt-3">
                    <h6 class="mb-0">Modifier le mot de passe</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.parametres.password.update') }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label">Mot de passe actuel</label>
                            <input type="password" name="mot_de_passe_actuel" class="form-control @error('mot_de_passe_actuel') is-invalid @enderror" required>
                            @error('mot_de_passe_actuel')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nouveau mot de passe</label>
                            <input type="password" name="nouveau_mot_de_passe" class="form-control @error('nouveau_mot_de_passe') is-invalid @enderror" required>
                            @error('nouveau_mot_de_passe')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Confirmation du nouveau mot de passe</label>
                            <input type="password" name="nouveau_mot_de_passe_confirmation" class="form-control" required>
                        </div>

                        <button class="btn btn-primary" type="submit">Mettre a jour le mot de passe</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

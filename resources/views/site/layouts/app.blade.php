<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'SOFIC')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f5f7fb;
            color: #1f2937;
        }
        .site-shell {
            min-height: 100vh;
        }
        .site-navbar {
            background: #fff;
            border-bottom: 1px solid #e6ebf2;
        }
        .site-main {
            padding-top: 1rem;
            padding-bottom: 1.25rem;
        }
        @media (max-width: 991.98px) {
            .site-main {
                padding-top: .75rem;
                padding-bottom: 1rem;
            }
            .table {
                min-width: 640px;
            }
            .table th,
            .table td {
                white-space: nowrap;
            }
        }
    </style>
</head>
<body>
<div class="site-shell">
<nav class="navbar navbar-expand-lg navbar-light site-navbar">
    <div class="container">
        <a class="navbar-brand fw-bold" href="{{ route('site.accueil') }}">SOFIC</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#siteMenu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="siteMenu">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="{{ route('site.accueil') }}">Accueil</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('site.produits') }}">Produits</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('site.factures.suivi.form') }}">Suivi facture</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('admin.login') }}">Admin</a></li>
            </ul>
        </div>
    </div>
</nav>

<main class="container site-main">
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @yield('content')
</main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

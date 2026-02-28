<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'SOFIC Admin')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        :root {
            --app-bg: #f3f6fb;
            --surface: #ffffff;
            --text-main: #1f2937;
            --muted: #6b7280;
            --brand: #1f6feb;
            --brand-soft: rgba(31, 111, 235, 0.12);
            --ok: #15a46f;
            --border: #e6ebf2;
        }
        body { font-family: "Plus Jakarta Sans", sans-serif; background: var(--app-bg); color: var(--text-main); }
        .app-shell { min-height: 100vh; display: flex; }
        .app-sidebar {
            width: 260px;
            background: var(--surface);
            border-right: 1px solid var(--border);
            padding: 1rem;
            position: fixed;
            inset: 0 auto 0 0;
            z-index: 1030;
            transform: translateX(-110%);
            transition: transform .25s ease;
        }
        .app-sidebar.is-open { transform: translateX(0); }
        .sidebar-brand { display: flex; align-items: center; gap: .75rem; margin-bottom: 1.5rem; }
        .brand-mark {
            width: 2rem; height: 2rem; border-radius: .65rem;
            background: linear-gradient(135deg, #1f6feb 0%, #0ea5e9 100%);
            color: #fff; display: grid; place-items: center; font-weight: 700;
        }
        .brand-title { font-weight: 700; letter-spacing: .2px; }
        .sidebar-nav { display: grid; gap: .35rem; }
        .sidebar-link {
            display: flex; align-items: center; gap: .65rem;
            padding: .65rem .75rem; border-radius: .75rem;
            text-decoration: none; color: #334155; font-weight: 500;
        }
        .sidebar-link:hover { background: #f1f5f9; color: #0f172a; }
        .sidebar-link.active { background: var(--brand-soft); color: var(--brand); }
        .sidebar-link i { font-size: 1.05rem; }
        .app-main { flex: 1; margin-left: 0; width: 100%; }
        .app-header {
            height: 72px; padding: 0 1.25rem;
            display: flex; align-items: center;
            background: var(--surface); border-bottom: 1px solid var(--border);
            position: sticky; top: 0; z-index: 1020;
        }
        .app-content { padding: 1.25rem; }
        .soft-card { border: 1px solid var(--border); border-radius: 1rem; box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06); }
        .btn { border-radius: .65rem; font-weight: 600; }
        .shadow-soft { box-shadow: 0 6px 20px rgba(15, 23, 42, 0.06); }
        .status-badge { padding: .35rem .6rem; border-radius: 999px; font-size: .76rem; font-weight: 700; letter-spacing: .2px; }
        .status-payee { background: rgba(21, 164, 111, .16); color: #0f8a5c; }
        .status-partielle { background: rgba(245, 158, 11, .18); color: #b56f00; }
        .status-envoyee, .status-brouillon { background: rgba(31, 111, 235, .14); color: #1f6feb; }
        .status-annulee { background: rgba(239, 68, 68, .15); color: #d12f2f; }
        @media (min-width: 992px) {
            .app-sidebar { transform: translateX(0); }
            .app-main { margin-left: 260px; width: calc(100% - 260px); }
        }
    </style>
    @stack('styles')
</head>
<body x-data="{ sidebarOpen: false }">
@auth
    <div class="app-shell">
        @include('admin.layouts.sidebar')
        <div class="app-main">
            @include('admin.layouts.header')
            <main class="app-content">
                @yield('content')
            </main>
        </div>
    </div>
@else
    <main class="container py-5">
        @yield('content')
    </main>
@endauth

<div class="toast-container position-fixed top-0 end-0 p-3">
    @if(session('succes'))
        <div class="toast text-bg-success border-0" role="alert" data-bs-delay="3500" data-autoshow="true">
            <div class="d-flex">
                <div class="toast-body">{{ session('succes') }}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    @endif
    @if($errors->any())
        <div class="toast text-bg-danger border-0" role="alert" data-bs-delay="4500" data-autoshow="true">
            <div class="d-flex">
                <div class="toast-body">{{ $errors->first() }}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    @endif
</div>

<div class="modal fade" id="confirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content soft-card">
            <div class="modal-header border-0">
                <h5 class="modal-title">Confirmer l'action</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-0">
                <p class="text-muted mb-0" id="confirmMessage">Voulez-vous vraiment continuer ?</p>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-danger" id="confirmSubmit">Oui, confirmer</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.querySelectorAll('.toast[data-autoshow="true"]').forEach((toastEl) => {
        bootstrap.Toast.getOrCreateInstance(toastEl).show();
    });

    const confirmModalEl = document.getElementById('confirmModal');
    let pendingForm = null;
    if (confirmModalEl) {
        const modal = bootstrap.Modal.getOrCreateInstance(confirmModalEl);
        document.addEventListener('submit', function (event) {
            const form = event.target;
            if (!form.matches('[data-confirm]')) return;
            event.preventDefault();
            pendingForm = form;
            document.getElementById('confirmMessage').textContent = form.dataset.confirm || 'Confirmer cette action ?';
            modal.show();
        });
        document.getElementById('confirmSubmit').addEventListener('click', function () {
            if (pendingForm) pendingForm.submit();
        });
    }
</script>
@stack('scripts')
@yield('scripts')
</body>
</html>

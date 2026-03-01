<header class="app-header shadow-soft">
    <button class="btn btn-light d-lg-none" @click="sidebarOpen = !sidebarOpen">
        <i class="bi bi-list"></i>
    </button>

    <div class="ms-auto d-flex align-items-center gap-2 flex-wrap justify-content-end">
        <button class="btn btn-light position-relative" type="button">
            <i class="bi bi-bell"></i>
            <span class="badge rounded-pill bg-danger position-absolute top-0 start-100 translate-middle p-1">3</span>
        </button>
        <div class="dropdown">
            <button class="btn btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-person-circle me-1"></i>{{ auth()->user()?->name ?? 'Admin' }}
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><span class="dropdown-item-text text-muted small">Connecte en {{ auth()->user()?->role ?? 'utilisateur' }}</span></li>
                <li>
                    <a class="dropdown-item" href="{{ route('admin.parametres.edit') }}">Parametres du compte</a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form method="POST" action="{{ route('admin.logout') }}">
                        @csrf
                        <button class="dropdown-item text-danger" type="submit">Deconnexion</button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</header>

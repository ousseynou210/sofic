<aside class="app-sidebar shadow-soft" :class="{ 'is-open': sidebarOpen }">
    <div class="sidebar-brand">
        <div class="brand-mark">S</div>
        <div>
            <div class="brand-title">SOFIC Admin</div>
            <small class="text-muted">Back-office</small>
        </div>
    </div>

    <nav class="sidebar-nav">
        <a href="{{ route('admin.dashboard') }}" class="sidebar-link @if(request()->routeIs('admin.dashboard')) active @endif">
            <i class="bi bi-grid"></i><span>Dashboard</span>
        </a>
        <a href="{{ route('admin.parametres.edit') }}" class="sidebar-link @if(request()->routeIs('admin.parametres.*')) active @endif">
            <i class="bi bi-gear"></i><span>Parametres</span>
        </a>
        <a href="{{ route('admin.client.index') }}" class="sidebar-link @if(request()->routeIs('admin.clients.*') || request()->routeIs('admin.client.index')) active @endif">
            <i class="bi bi-people"></i><span>Clients</span>
        </a>
        <a href="{{ route('admin.produit.index') }}" class="sidebar-link @if(request()->routeIs('admin.produits.*') || request()->routeIs('admin.produit.index')) active @endif">
            <i class="bi bi-box-seam"></i><span>Produits</span>
        </a>
        <a href="{{ route('admin.commercial.index') }}" class="sidebar-link @if(request()->routeIs('admin.commerciaux.*') || request()->routeIs('admin.commercial.index')) active @endif">
            <i class="bi bi-person-badge"></i><span>Commerciaux</span>
        </a>
        <a href="{{ route('admin.factures.index') }}" class="sidebar-link @if(request()->routeIs('admin.factures.*')) active @endif">
            <i class="bi bi-receipt"></i><span>Factures</span>
        </a>
        <a href="{{ route('admin.depense.index') }}" class="sidebar-link @if(request()->routeIs('admin.depenses.*') || request()->routeIs('admin.depense.index')) active @endif">
            <i class="bi bi-wallet2"></i><span>Depenses</span>
        </a>
        <a href="{{ route('admin.compte.index') }}" class="sidebar-link @if(request()->routeIs('admin.comptes.*') || request()->routeIs('admin.compte.index')) active @endif">
            <i class="bi bi-bank"></i><span>Comptes</span>
        </a>
    </nav>
</aside>

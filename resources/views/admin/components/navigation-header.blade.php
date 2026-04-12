<nav class="sb-topnav navbar navbar-expand navbar-dark bg-black border-bottom border-secondary shadow-sm">
    <!-- Navbar Brand -->
    @auth
        @php
            $user = auth()->user();
            if ($user->can('ver-panel')) {
                $route = route('panel');
            } elseif ($user->can('ver-producto')) {
                $route = route('productos.index');
            } else {
                $route = route('profile.index');
            }
        @endphp
        <a class="navbar-brand ps-3 fw-semibold text-light" href="{{ $route }}">MARA-DOORS</a>
    @else
        <a class="navbar-brand ps-3 fw-semibold text-light" href="{{ route('welcome') }}">MARA-DOORS</a>
    @endauth

    <!-- Sidebar Toggle -->
    <button class="btn btn-outline-light btn-sm order-1 order-lg-0 ms-2" id="sidebarToggle">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Spacer -->
    <div class="ms-auto"></div>

    <!-- Navbar -->
    <ul class="navbar-nav me-3">
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle text-light" id="navbarDropdown" href="#" role="button"
               data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-user fa-fw"></i>
            </a>

            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="{{ route('profile.index') }}">Configuraciones</a></li>
                <li><a class="dropdown-item" href="#!">Registro de actividad</a></li>
                <li><hr class="dropdown-divider" /></li>
                <li><a class="dropdown-item" href="{{ route('logout') }}">Cerrar sesión</a></li>
            </ul>
        </li>
    </ul>
</nav>


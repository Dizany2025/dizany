<aside id="sidebar">
    <div class="sidebar-content">
        @auth
            @if(auth()->user()->rol->nombre == 'Administrador')
                <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-home"></i> Inicio
                </a>
                <a href="{{ route('usuarios.index') }}" class="{{ request()->routeIs('usuarios.index') ? 'active' : '' }}">
                    <i class="fas fa-users"></i> Usuarios
                </a>
                <a href="{{ route('clientes.index') }}" class="{{ request()->routeIs('clientes.index') ? 'active' : '' }}">
                    <i class="fas fa-user-friends"></i> Clientes
                </a>

                <!-- Menú desplegable Productos -->
                <div class="submenu">
                    <button class="submenu-toggle d-flex justify-content-between align-items-center {{ request()->is('productos*') ? 'active' : '' }}">
                        <span><i class="fas fa-box me-2"></i> Productos</span>
                        <i class="fas fa-caret-down toggle-icon transition" style="transition: transform 0.3s ease;"></i>
                    </button>
                    <div class="submenu-items {{ request()->is('productos*') ? 'show' : '' }}">
                        <a href="{{ route('productos.index') }}" class="{{ request()->routeIs('productos.index') ? 'active' : '' }}">
                            <i class="fas fa-box-open me-1"></i> Ver Productos
                        </a>
                        <a href="{{ route('productos.parametros') }}" class="{{ request()->routeIs('productos.parametros') ? 'active' : '' }}">
                            <i class="fas fa-cogs"></i> Parámetros
                        </a>
                    </div>
                </div>
                <div class="submenu"> 
                    <button class="submenu-toggle d-flex justify-content-between align-items-center {{ request()->is('inventario*') ? 'active' : '' }}">
                        <span><i class="fas fa-warehouse me-2"></i> Inventario</span>
                        <i class="fas fa-caret-down toggle-icon transition" style="transition: transform 0.3s ease;"></i>
                    </button>
                    <div class="submenu-items {{ request()->is('inventario*') ? 'show' : '' }}">
                        <a href="{{ route('inventario.stock') }}" class="{{ request()->routeIs('inventario.stock') ? 'active' : '' }}">
                            <i class="fas fa-boxes-stacked me-1"></i> Stock
                        </a>
                    </div>
                </div>

                <a href="{{ route('ventas.index') }}" class="{{ request()->routeIs('ventas.index') ? 'active' : '' }}">
                    <i class="fas fa-shopping-cart"></i> Ventas
                </a>
                <a href="{{ route('gastos.index') }}" class="{{ request()->routeIs('gastos.index') ? 'active' : '' }}">
                    <i class="fas fa-money-bill-wave"></i> Gastos
                </a>
                <a href="{{ route('reportes.index') }}" class="{{ request()->routeIs('reportes.index') ? 'active' : '' }}">
                    <i class="fas fa-chart-line"></i> Reportes
                </a>
                <a href="{{ route('configuracion.index') }}" class="{{ request()->routeIs('configuracion.index') ? 'active' : '' }}">
                    <i class="fas fa-cog"></i> Configuración
                </a>
            @elseif(auth()->user()->rol->nombre == 'Empleado')
                <a href="{{ route('empleado.dashboard') }}" class="{{ request()->routeIs('empleado.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-home"></i> Inicio
                </a>
                <a href="{{ route('ventas.index') }}" class="{{ request()->routeIs('ventas.index') ? 'active' : '' }}">
                    <i class="fas fa-shopping-cart"></i> Ventas
                </a>
                <a href="{{ route('gastos.index') }}" class="{{ request()->routeIs('gastos.index') ? 'active' : '' }}">
                    <i class="fas fa-money-bill-wave"></i> Gastos
                </a>
            @endif
        @endauth
    </div>

    <!-- Botón Cerrar sesión -->
    <a href="#" id="btn-logout" class="sidebar-footer">
        <i class="fas fa-sign-out-alt"></i> Cerrar sesión
    </a>

    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
        @csrf
    </form>
</aside>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.getElementById('btn-logout').addEventListener('click', function (e) {
        e.preventDefault();
        Swal.fire({
            title: '¿Cerrar sesión?',
            text: "Tu sesión se cerrará.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, cerrar sesión',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('logout-form').submit();
            }
        });
    });
</script>

<!-- Estilos internos aplicables al modo claro/oscuro -->
<style>
    .sidebar-content a {
        display: flex;
        align-items: center;
        padding: 10px 15px;
        margin-bottom: 12px;
        color: var(--sidebar-link-color, #fff);
        text-decoration: none;
        border-radius: 8px;
        border-bottom: 2px solid transparent;
        transition: all 0.3s ease;
    }

    .sidebar-content a i {
        margin-right: 8px;
        transition: color 0.3s ease;
    }

    #sidebar a.active {
        color: var(--sidebar-active-color, #4ea5ff);
        font-weight: bold;
        border-bottom: 2px solid var(--sidebar-active-border, #0d6efd);
        background-color: transparent;
    }

    .sidebar-content a.active i {
        color: var(--sidebar-active-icon, #0d6efd);
    }

    .theme-dark .sidebar-content a {
        color: #ccc;
    }

    .theme-dark #sidebar a.active {
        color: #4ea5ff;
        border-bottom-color: #4ea5ff;
    }

    .theme-dark .sidebar-footer {
        background-color: #0c2b4bf6;
        border-top: 1px solid #1e4c7c;
    }
</style>

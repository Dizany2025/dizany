@php
    use Illuminate\Support\Carbon;

    $hora = Carbon::now()->format('H');
    $saludo = '';
    $emoji = '';

    if ($hora >= 0 && $hora < 12) {
        $saludo = '¡Buenos días!';
        $emoji = '🌞'; // sol
    } elseif ($hora >= 12 && $hora < 19) {
        $saludo = '¡Buenas tardes!';
        $emoji = '☀️'; // sol con nubes
    } else {
        $saludo = '¡Buenas noches!';
        $emoji = '🌙'; // luna
    }
@endphp

<header id="header" class="d-flex align-items-center p-2 text-white">
    <button id="btn-toggle-sidebar" class="btn btn-primary me-3" aria-label="Toggle sidebar">&#9776;</button>

    @php
        use App\Models\Configuracion;
        $config = Configuracion::first();
    @endphp

<!-- Logo y nombre dinámico -->
<img src="{{ $config && $config->logo ? asset($config->logo) : asset('images/LOGO.png') }}"
     alt="Logo" width="40" height="40" class="me-2 rounded" style="object-fit: contain;">
  
    <span class="fw-bold text-white" style="font-size: 1rem;">
        <h3 >{{ $config->nombre_empresa ?? 'Dizany' }}</h3>
    </span>
</div>


    <div class="header-actions flex-grow-1">
        @yield('header-actions')
    </div>

    <!-- 🔔 Campanita de notificaciones -->
    <!-- DERECHA: campana + saludo -->
    <div class="d-flex align-items-center">
        <!-- Campanita -->
        <div class="position-relative me-4">
            <a class="nav-link position-relative text-white" href="#" id="notificacionesDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-bell fa-lg"></i>
                <span id="contadorTotal" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger d-none">
                    0
                </span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="notificacionesDropdown" style="min-width: 250px;">
                <li>
                    <a class="dropdown-item d-flex justify-content-between align-items-center" href="{{ route('inventario.stock') }}#stock">
                        <span><i class="fas fa-boxes text-danger me-2"></i> Bajo stock</span>
                        <span class="badge bg-danger" id="contadorStock">0</span>
                    </a>
                </li>
                <li>
                    <a class="dropdown-item d-flex justify-content-between align-items-center" href="{{ route('inventario.stock') }}#vencimiento">
                        <span><i class="fas fa-calendar-alt text-warning me-2"></i> Por vencer</span>
                        <span class="badge bg-warning text-dark" id="contadorVencimiento">0</span>
                    </a>
                </li>
            </ul>
        </div>

    <!-- Saludo y nombre de usuario -->
    <div class="header-actions flex-grow-1 d-flex justify-content-end align-items-center text-end">
        <div class="d-flex flex-column align-items-end">
            <span>{!! $emoji !!} {{ $saludo }}</span>
            <strong>{{ ucfirst(Auth::user()->nombre) }}</strong>
        </div>
    </div>
</header>
@push('scripts')
<script>
function cargarNotificaciones() {
    fetch("/notificaciones/inventario")
        .then(res => res.json())
        .then(data => {
            const total = data.stock_bajo + data.por_vencer;

            // Mostrar u ocultar el contador total
            const contadorTotal = document.getElementById("contadorTotal");
            contadorTotal.textContent = total;
            contadorTotal.classList.toggle("d-none", total === 0);

            // Actualizar contadores individuales
            document.getElementById("contadorStock").textContent = data.stock_bajo;
            document.getElementById("contadorVencimiento").textContent = data.por_vencer;
        });
}

document.addEventListener("DOMContentLoaded", cargarNotificaciones);
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const hash = window.location.hash;
    if (hash) {
        const tabTrigger = document.querySelector(`button[data-bs-target="${hash}"]`);
        if (tabTrigger) {
            new bootstrap.Tab(tabTrigger).show();
        }
    }
});
</script>

@endpush
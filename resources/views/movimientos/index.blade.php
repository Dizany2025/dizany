@extends('layouts.app')

{{-- Activa el sistema de header-actions --}}
@section('header-back')
<button class="btn-header-back" onclick="history.back()">
    <i class="fas fa-arrow-left"></i>
</button>
@endsection

@section('header-title')
Movimientos
@endsection

@section('header-buttons')

<button class="btn-gasto" onclick="abrirCaja()">
    <i class="fas fa-cash-register"></i>
    <span class="btn-text">Abrir caja</span>
</button>

<a href="{{ route('movimientos.reporte') }}"
   class="btn-gasto">
    <i class="fas fa-file-download"></i>
    <span class="btn-text">Reporte</span>
</a>

@endsection

@section('content')

<div class="container-fluid">

    {{-- ================= TABS PRINCIPALES ================= --}}
    <div class="card mb-3">
        <div class="card-body p-2 d-flex gap-2">
            <a href="{{ route('movimientos.index', array_merge(request()->query(), ['tipo' => 'transacciones'])) }}"
               class="btn {{ request('tipo','transacciones') === 'transacciones' ? 'btn-dark' : 'btn-light' }} flex-fill">
                Transacciones
            </a>

            <a href="{{ route('movimientos.index', array_merge(request()->query(), ['tipo' => 'cierres'])) }}"
               class="btn {{ request('tipo') === 'cierres' ? 'btn-dark' : 'btn-light' }} flex-fill">
                Cierres de caja
            </a>
        </div>
    </div>

    {{-- ================= FILTROS ================= --}}
    <form method="GET"
          action="{{ route('movimientos.index') }}"
          class="row g-2 mb-3">

        <input type="hidden" name="tipo" value="{{ request('tipo','transacciones') }}">
        <input type="hidden" name="tab" value="{{ $tab }}">

        <div class="col-md-2">
            <select name="rango"
                    class="form-select"
                    onchange="this.form.submit()">
                <option value="diario" {{ $rango === 'diario' ? 'selected' : '' }}>Diario</option>
                <option value="semanal" {{ $rango === 'semanal' ? 'selected' : '' }}>Semanal</option>
                <option value="mensual" {{ $rango === 'mensual' ? 'selected' : '' }}>Mensual</option>
                <option value="anual" {{ $rango === 'anual' ? 'selected' : '' }}>Anual</option>
                <option value="personalizado" {{ $rango === 'personalizado' ? 'selected' : '' }}>Personalizado</option>

            </select>
        </div>

        <div class="col-md-2">
            <!-- Wrapper relativo (CLAVE) -->
            <div class="position-relative" id="year-picker-wrapper">

                <!-- Tu input-group original (NO se rompe) -->
                <div class="input-group" id="picker-wrapper">
                    <input
                        id="filter-date"
                        name="fecha"
                        class="form-control"
                        value="{{ $rango === 'anual' ? substr($fecha, 0, 4) : $fecha }}"
                        autocomplete="off"
                        readonly
                        data-input
                    >
                    <span class="input-group-text" data-toggle>
                        <i class="fa fa-calendar"></i>
                    </span>
                </div>

                @php
                    $yearActivo = $rango === 'anual'
                        ? substr($fecha, 0, 4)
                        : now()->year;
                @endphp

                <div id="year-picker" class="year-picker d-none">
                    @for ($y = now()->year - 10; $y <= now()->year + 10; $y++)
                        <button
                            type="button"
                            class="year-btn {{ (string)$yearActivo === (string)$y ? 'active' : '' }}"
                            data-year="{{ $y }}"
                        >
                            {{ $y }}
                        </button>
                    @endfor
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <input type="text"
                   name="buscar"
                   value="{{ request('buscar') }}"
                   class="form-control"
                   placeholder="Buscar concepto..."
                   onkeydown="if(event.key==='Enter'){ this.form.submit(); }">
        </div>
    </form>

    {{-- ================= KPIs ================= --}}
    <div class="row mb-4">

        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-success bg-opacity-10 p-3">📈</div>
                    <div>
                        <small class="text-muted">Balance</small>
                        <h5 class="fw-bold mb-0">
                            S/ {{ number_format($balance ?? 0, 2) }}
                        </h5>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-success bg-opacity-10 p-3">💵</div>
                    <div>
                        <small class="text-muted">Ventas totales</small>
                        <h5 class="fw-bold text-success mb-0">
                            S/ {{ number_format($ventas ?? 0, 2) }}
                        </h5>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-danger bg-opacity-10 p-3">💸</div>
                    <div>
                        <small class="text-muted">Gastos totales</small>
                        <h5 class="fw-bold text-danger mb-0">
                            S/ {{ number_format($gastos ?? 0, 2) }}
                        </h5>
                    </div>
                </div>
            </div>
        </div>

        {{-- Ganancias --}}
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-success bg-opacity-10 p-3">💰</div>
                    <div>
                        <small class="text-muted">Ganancia</small>
                        <h5 class="fw-bold {{ $ganancias >= 0 ? 'text-success' : 'text-danger' }}">
                            S/ {{ number_format($ganancias ?? 0, 2) }}
                        </h5>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ================= SUB TABS ================= --}}
    <ul class="nav nav-tabs mb-3">
        @php
            $tabs = [
                'ingresos'   => 'Ingresos',
                'egresos'    => 'Egresos',
                'por_cobrar' => 'Por cobrar',
                'por_pagar'  => 'Por pagar',
            ];
        @endphp

        @foreach($tabs as $key => $label)
            <li class="nav-item">
                <a class="nav-link {{ $tab === $key ? 'active' : '' }}"
                   href="{{ route('movimientos.index', array_merge(request()->query(), ['tab' => $key])) }}">
                    {{ $label }}
                </a>
            </li>
        @endforeach
    </ul>

    {{-- ================= TABLA ================= --}}
    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Fecha</th>
                        <th>Concepto</th>
                        <th>Método</th>
                        <th>Estado</th>
                        <th class="text-end">Monto</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>

                @forelse ($movimientos as $movimiento)
                    <tr class="mov-row"
                        style="cursor:pointer"
                        data-ref-id="{{ $movimiento->referencia_id }}"
                        data-ref-tipo="{{ $movimiento->referencia_tipo }}"
                        data-mov-id="{{ $movimiento->id }}">

                        <td>{{ \Carbon\Carbon::parse($movimiento->fecha)->format('d/m/Y') }}</td>

                        <td>{{ $movimiento->concepto }}</td>

                        <td>{{ ucfirst($movimiento->metodo_pago) }}</td>

                        <td>
                            @if($movimiento->estado === 'pagado')
                                <span class="badge bg-success">Pagado</span>
                            @else
                                <span class="badge bg-warning text-dark">Pendiente</span>
                            @endif
                        </td>

                        <td class="text-end fw-bold
                            {{ $movimiento->tipo === 'ingreso' ? 'text-success' : 'text-danger' }}">
                            {{ $movimiento->tipo === 'ingreso' ? '+' : '-' }}
                            S/ {{ number_format($movimiento->monto, 2) }}
                        </td>

                        <td class="text-center">
                            <button class="btn btn-sm btn-outline-secondary">
                                👁
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6"
                            class="text-center text-muted py-4">
                            No hay movimientos para mostrar
                        </td>
                    </tr>
                @endforelse

                </tbody>
            </table>
        </div>

        {{-- ================= PAGINACIÓN ================= --}}
        @if($movimientos instanceof \Illuminate\Pagination\LengthAwarePaginator)
            <div class="card-footer d-flex justify-content-end">
                {{ $movimientos->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
</div>

{{-- ================= OFFCANVAS DETALLE ================= --}}
<div class="offcanvas offcanvas-end detalle-venta-panel"
     tabindex="-1"
     id="offcanvasDetalle">

    <div class="offcanvas-header pb-2">
        <h5 class="offcanvas-title mb-0">
            Detalle de la venta
        </h5>
        <button type="button"
                class="btn-close"
                data-bs-dismiss="offcanvas"></button>
    </div>

    <div class="divider-green"></div>

    <div class="offcanvas-body" id="detalleContenido">
        {{-- JS inyecta aquí --}}
    </div>
</div>

@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/movimientos.css') }}">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/style.css">
<style>
.range-selected{
    background:#16a34a !important;
    color:white !important;
    border-radius:50% !important;
}
</style>

@endpush

@push('scripts')
<script src="{{ asset('js/movimientos.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/index.js"></script>

<script>
flatpickr.localize(flatpickr.l10ns.es);

(function () {
    const rango = "{{ $rango }}";

    // ✅ importante: apunta al form correcto
    const form = document.querySelector('form[action="{{ route('movimientos.index') }}"]');

    // Si no encuentra el form, no hagas nada (evita errores raros)
    if (!form) return;

    // ✅ esta función EXISTE para todos los rangos
    function submitFormDelayed() {
        clearTimeout(window.__mov_submit_timer);
        window.__mov_submit_timer = setTimeout(() => form.submit(), 200);
    }

    // Helper: date válida YYYY-MM-DD
    function isYmd(str){
        return /^\d{4}-\d{2}-\d{2}$/.test(str);
    }

    // Helper: año válido YYYY
    function isYear(str){
        return /^\d{4}$/.test(str);
    }

    // Normaliza default según rango
    let defaultFecha = "{{ $fecha }}";
    if (rango === "diario" && !isYmd(defaultFecha)) {
        defaultFecha = "{{ now()->format('Y-m-d') }}";
    }
    if (rango === "mensual") {
        // mensual: trabajaremos con YYYY-MM
        if (!/^\d{4}-\d{2}$/.test(defaultFecha)) {
            defaultFecha = "{{ now()->format('Y-m') }}";
        } else {
            defaultFecha = defaultFecha.substring(0,7);
        }
    }
    if (rango === "anual") {
        // anual: YYYY
        const y = defaultFecha.substring(0,4);
        defaultFecha = isYear(y) ? y : "{{ now()->format('Y') }}";
    }

    // Destruir instancia anterior si existe (evita bugs al recargar con cache)
    if (window.__mov_fp) {
        try { window.__mov_fp.destroy(); } catch(e){}
        window.__mov_fp = null;
    }

    // ===================== DIARIO =====================
    if (rango === "diario") {
        window.__mov_fp = flatpickr("#picker-wrapper", {
            wrap: true,
            dateFormat: "Y-m-d",
            altInput: true,
            altFormat: "j M Y",
            defaultDate: defaultFecha,
            allowInput: false,
            clickOpens: true,
            onChange: submitFormDelayed
        });
    }

    // ===================== SEMANAL (Lun-Dom) =====================
    if (rango === "semanal") {
    let initialized = false; // 👈 CLAVE para evitar loop

    window.__mov_fp = flatpickr("#picker-wrapper", {
        wrap: true,
        mode: "range",
        locale: "es",

        dateFormat: "Y-m-d",
        altInput: true,
        altFormat: "j M",
        conjunction: " a ",

        defaultDate: "{{ $fecha ?: now()->format('Y-m-d') }}",
        allowInput: false,

        // 🔹 SOLO SELECCIÓN VISUAL (NO SUBMIT)
        onReady(selectedDates, str, fp) {

            const base = selectedDates[0] || new Date();

            const day = base.getDay(); // 0=Dom, 1=Lun
            const diffToMonday = day === 0 ? -6 : 1 - day;

            const start = new Date(base);
            start.setDate(base.getDate() + diffToMonday);

            const end = new Date(start);
            end.setDate(start.getDate() + 6);

            // Seleccionar semana completa (visual)
            fp.setDate([start, end], true);

            // Marcar que ya inicializó
            initialized = true;
        },

        onChange(dates, str, fp) {

            // Ignorar el primer cambio disparado por setDate del onReady
            if (!initialized) return;

            // Si elige un solo día → completar semana
            if (dates.length === 1) {

                const base = dates[0];
                const day = base.getDay();
                const diffToMonday = day === 0 ? -6 : 1 - day;

                const start = new Date(base);
                start.setDate(base.getDate() + diffToMonday);

                const end = new Date(start);
                end.setDate(start.getDate() + 6);

                fp.setDate([start, end], true);
                return;
            }

            // Cuando ya hay rango completo → submit
            if (dates.length === 2) {
                submitFormDelayed();
            }
        }
    });
}

    // ===================== MENSUAL =====================
    if (rango === "mensual") {
        window.__mov_fp = flatpickr("#picker-wrapper", {
            wrap: true,
            plugins: [
                new monthSelectPlugin({
                    shorthand: true,
                    dateFormat: "Y-m",   // 👈 enviamos YYYY-MM al backend
                    altFormat: "M Y"
                })
            ],
            altInput: true,
            defaultDate: defaultFecha,
            allowInput: false,
            clickOpens: true,
            onChange: submitFormDelayed
        });
    }

    // ===================== ANUAL (solo año) =====================
    // Sin plugin raro: usamos un input de año
    if (rango === "anual") {
        const input  = document.getElementById("filter-date");
        const picker = document.getElementById("year-picker");

        // Normalizar valor inicial (solo año)
        if (input.value.length > 4) {
            input.value = input.value.substring(0, 4);
        }

        // Abrir / cerrar selector
        input.addEventListener("click", (e) => {
            e.stopPropagation();
            picker.classList.toggle("d-none");
        });

        // Click en un año
        picker.querySelectorAll(".year-btn").forEach(btn => {
            btn.addEventListener("click", () => {

                const year = btn.dataset.year;

                // actualizar input
                input.value = year;

                // marcar activo
                picker.querySelectorAll(".year-btn")
                    .forEach(b => b.classList.remove("active"));
                btn.classList.add("active");

                // cerrar picker
                picker.classList.add("d-none");

                // ✅ AQUÍ ESTABA EL ERROR
                submitFormDelayed();
            });
        });

        // cerrar si clic fuera
        document.addEventListener("click", (e) => {
            if (!picker.contains(e.target) && e.target !== input) {
                picker.classList.add("d-none");
            }
        });
    }

    // ===================== PERSONALIZADO (DOBLE) =====================
    if (rango === "personalizado") {

    if (window.__mov_fp) {
        window.__mov_fp.destroy();
    }

    // Detectar rango por estructura, no por símbolo
    const fechaBackend = "{{ $fecha }}";
    const partes = fechaBackend.split(" a ");
    const tieneRangoPrevio = partes.length === 2;

    window.__mov_fp = flatpickr("#picker-wrapper", {
        wrap: true,
        mode: "range",

        locale: {
            ...flatpickr.l10ns.es,
            rangeSeparator: " → "
        },

        dateFormat: "Y-m-d",
        altInput: true,
        altFormat: "j M",

        showMonths: 2,
        allowInput: false,

        // 🔑 USAR EL RANGO REAL QUE VIENE DEL BACKEND
        defaultDate: tieneRangoPrevio ? partes : null,

        // 🔑 SOLO limpiar si NO hay rango previo
        onOpen(selectedDates, dateStr, fp) {
            if (!tieneRangoPrevio) {
                fp.clear();
                fp.jumpToDate(new Date());
            }
        },

        onChange(dates) {
            if (dates.length === 2) {
                submitFormDelayed();
            }
        }
    });
}

})();
</script>

@endpush

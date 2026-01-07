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
            </select>
        </div>

        <div class="col-md-2">
            <div class="input-group" id="picker-wrapper">
                <input 
                    id="filter-date"
                    name="fecha"
                    class="form-control"
                    value="{{ $fecha }}"
                    autocomplete="off"
                    readonly
                >
                <span class="input-group-text">
                    <i class="fa fa-calendar"></i>
                </span>
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
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/yearSelect/style.css">
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
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/yearSelect/yearSelect.js"></script>
<script>
flatpickr.localize(flatpickr.l10ns.es);

const rango = "{{ $rango }}";

//
// ============ DIARIO ============
//
if (rango === "diario") {
    flatpickr("#picker-wrapper", {
        wrap: true,
        dateFormat: "Y-m-d",
        altInput: true,
        altFormat: "j M Y",
        defaultDate: "{{ $fecha }}",
        allowInput: false,
        onChange(){ this._input.form.submit(); }
    });
}

//
// ============ SEMANAL ============
//
if (rango === "semanal") {

    flatpickr("#picker-wrapper", {
        wrap: true,
        mode: "range",

        dateFormat: "Y-m-d",
        conjunction: " a ",

        altInput: true,
        altFormat: "j M",
        
        defaultDate: "{{ $fecha }}",

        // cuando eliges un día → completa la semana
        onChange(dates, str, fp){

            if (dates.length === 1) {

                const start = new Date(dates[0]);
                const end   = new Date(dates[0]);

                end.setDate(end.getDate() + 6);

                fp.setDate([start, end], true);
            }

            fp.input.form.submit();
        },

        // cuando carga el calendario → también completa semana
        onReady(dates, str, fp){

            if (fp.selectedDates.length === 1) {

                const start = new Date(fp.selectedDates[0]);
                const end   = new Date(fp.selectedDates[0]);

                end.setDate(end.getDate() + 6);

                fp.setDate([start, end], true);
            }
        }
    });
}

//
// ============ MENSUAL ============
//
if (rango === "mensual") {
    flatpickr("#picker-wrapper", {
        wrap: true,
        plugins: [
            new monthSelectPlugin({
                shorthand: true,
                dateFormat: "Y-m-d",
                altFormat: "M Y"
            })
        ],
        altInput: true,
        defaultDate: "{{ $fecha }}",
        allowInput: false,
        onChange(){ this._input.form.submit(); }
    });
}

//
// ============ ANUAL SOLO AÑO ============
//
if (rango === "anual") {

    flatpickr("#picker-wrapper", {
        wrap: true,

        dateFormat: "Y",
        altInput: true,
        altFormat: "Y",

        allowInput: false,

        // toma solo el año, aunque venga rango
        defaultDate: "{{ substr($fecha,0,4) }}",

        onChange() { this._input.form.submit(); }
    });
}

</script>


@endpush

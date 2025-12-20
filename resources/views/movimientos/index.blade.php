@extends('layouts.app')

@section('content')
<div class="container-fluid">

    {{-- ================= HEADER ================= --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">Movimientos</h4>

        <div class="d-flex gap-2">
            <button class="btn btn-dark">
                👑 Abrir caja
            </button>

            <button class="btn btn-outline-dark">
                ⬇️ Descargar reporte
            </button>
        </div>
    </div>

    {{-- ================= TABS PRINCIPALES ================= --}}
    <div class="card mb-3">
        <div class="card-body p-2 d-flex">
            <button class="btn btn-dark flex-fill">Transacciones</button>
            <button class="btn btn-light flex-fill">Cierres de caja</button>
        </div>
    </div>

    {{-- ================= FILTROS ================= --}}
    <form method="GET" action="{{ route('movimientos.index') }}" class="row g-2 mb-3">

        <input type="hidden" name="tab" value="{{ $tab }}">

        <div class="col-md-2">
            <select name="rango" class="form-select" onchange="this.form.submit()">
                <option value="diario" {{ $rango === 'diario' ? 'selected' : '' }}>Diario</option>
                <option value="mensual" {{ $rango === 'mensual' ? 'selected' : '' }}>Mensual</option>
            </select>
        </div>

        <div class="col-md-2">
            <input type="date"
                name="fecha"
                value="{{ $fecha }}"
                class="form-control"
                onchange="this.form.submit()">
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

        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-success bg-opacity-10 p-3">
                        📈
                    </div>
                    <div>
                        <small class="text-muted">Balance</small>
                        <h5 class="fw-bold mb-0">S/ {{ number_format($balance ?? 0, 2) }}</h5>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-success bg-opacity-10 p-3">
                        💵
                    </div>
                    <div>
                        <small class="text-muted">Ventas totales</small>
                        <h5 class="fw-bold text-success mb-0">S/ {{ number_format($ventas ?? 0, 2) }}</h5>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-danger bg-opacity-10 p-3">
                        💸
                    </div>
                    <div>
                        <small class="text-muted">Gastos totales</small>
                        <h5 class="fw-bold text-danger mb-0">S/ {{ number_format($gastos ?? 0, 2) }}</h5>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- ================= SUB TABS ================= --}}
    <ul class="nav nav-tabs mb-3">
        <li class="nav-item">
            <a class="nav-link {{ $tab === 'ingresos' ? 'active' : '' }}"
            href="{{ route('movimientos.index', array_merge(request()->query(), ['tab' => 'ingresos'])) }}">
                Ingresos
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link {{ $tab === 'egresos' ? 'active' : '' }}"
            href="{{ route('movimientos.index', array_merge(request()->query(), ['tab' => 'egresos'])) }}">
                Egresos
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link {{ $tab === 'por_cobrar' ? 'active' : '' }}"
            href="{{ route('movimientos.index', array_merge(request()->query(), ['tab' => 'por_cobrar'])) }}">
                Por cobrar
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link {{ $tab === 'por_pagar' ? 'active' : '' }}"
            href="{{ route('movimientos.index', array_merge(request()->query(), ['tab' => 'por_pagar'])) }}">
                Por pagar
            </a>
        </li>
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
                            <button class="btn btn-sm btn-outline-secondary">👁</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">
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
{{-- =============panel derecho ================= --}}
<div class="offcanvas offcanvas-end detalle-venta-panel"
     tabindex="-1"
     id="offcanvasDetalle"
     aria-labelledby="offcanvasDetalleLabel">

    <div class="offcanvas-header pb-2">
        <div class="d-flex align-items-center gap-2">
            <div class="icon-circle">
                <i class="fas fa-store"></i>
            </div>
            <h5 class="offcanvas-title mb-0">Detalle de la venta</h5>
        </div>

        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>

    <div class="divider-green"></div>

    <div class="offcanvas-body" id="detalleContenido">
        {{-- JS inyecta aquí --}}
    </div>
</div>


@endsection


@push('styles')
<link rel="stylesheet" href="{{ asset('css/movimientos.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('js/movimientos.js') }}"></script>
@endpush




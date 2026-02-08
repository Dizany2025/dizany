@extends('layouts.app')

{{-- BOTÓN ATRÁS --}}
@section('header-back')
<button class="btn-header-back" onclick="history.back()">
    <i class="fas fa-arrow-left"></i>
</button>
@endsection
@section('header-title', 'Lotes de Productos')

@section('content')
<div class="container-fluid px-3 mt-4">

    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">
                <i class="fas fa-layer-group me-2"></i>
                Lotes registrados
            </h5>
        </div>

        <div class="card-body p-0">

            <div class="table-responsive">
                {{-- FILTROS (van arriba de la tabla) --}}
                <div class="filtros-lotes">
                    <div class="row mb-3 g-2">
                        <div class="col-md-2">
                            <select id="filtroEstado" class="form-select">
                                <option value="">Estado</option>
                                <option value="vencido">Vencidos</option>
                                <option value="10">Vence ≤ 10 días</option>
                                <option value="30">Vence ≤ 30 días</option>
                                <option value="ok">Vigentes</option>
                                <option value="sin">Sin vencimiento</option>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <select id="filtroProducto" class="form-select">
                                <option value="">Producto</option>
                                @foreach ($productos as $producto)
                                    <option value="{{ strtolower($producto->nombre) }}">
                                        {{ $producto->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <select id="filtroStock" class="form-select">
                                <option value="">Stock</option>
                                <option value="con">Con stock</option>
                                <option value="sin">Sin stock</option>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <select id="filtroFefo" class="form-select">
                                <option value="">FEFO</option>
                                <option value="1">Prioridad FEFO</option>
                            </select>
                        </div>

                        <div class="col-auto">
                            <div class="dropdown">
                                <button
                                    id="filtroMovimientosBtn"
                                    class="btn btn-outline-secondary btn-icon"
                                    type="button"
                                    data-bs-toggle="dropdown"
                                    aria-expanded="false"
                                    title="Filtrar por movimientos"
                                >
                                    <i class="fas fa-list-alt"></i>
                                </button>

                                <ul class="dropdown-menu">
                                    <li>
                                        <a class="dropdown-item" href="#" data-mov="">
                                            Todos
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="#" data-mov="1">
                                            Con movimientos
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="#" data-mov="0">
                                            Sin movimientos
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div class="col-md-3 d-flex align-items-center gap-1">
                            <input type="text" id="filtroBuscar" class="form-control"
                                placeholder="Buscar lote o producto…">

                                <button type="button"
                                        id="btnLimpiarFiltros"
                                        class="btn btn-outline-secondary"
                                        title="Limpiar filtros">
                                    <i class="fas fa-times"></i>
                                </button>
                        </div>
                    </div>
                </div>
                <div class="tabla-scroll">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Cód. Comprobante</th>
                            <th class="text-center" style="width:60px;">FEFO</th>
                            <th class="text-center" style="width:100px;">N° Lote</th>
                            <th>Producto</th>
                            <th style="width:100px;">Proveedor</th>
                            <th class="text-center">Stock</th>
                            <th>Ingreso</th>
                            <th>Vencimiento</th>
                            <th class="text-center">Estado</th>
                            <th class="text-center" style="width:120px;">Acciones</th>
                        </tr>
                    </thead>

                    <tbody>
                        @php
                            $fefoIndex = [];
                        @endphp

                        @forelse ($lotes as $lote)
                            @php
                                // =========================
                                // FEFO POR PRODUCTO
                                // =========================
                                $pid = $lote->producto_id;
                                $fefoIndex[$pid] = ($fefoIndex[$pid] ?? 0) + 1;

                                if ($lote->fecha_vencimiento && \Carbon\Carbon::parse($lote->fecha_vencimiento)->isPast()) {
                                    $fefoIcon = '<i class="fas fa-times-circle text-danger" title="Lote vencido"></i>';
                                } elseif ($fefoIndex[$pid] === 1) {
                                    $fefoIcon = '<i class="fas fa-circle text-success" title="Primer lote en salir (FEFO)"></i>';
                                } elseif ($fefoIndex[$pid] === 2) {
                                    $fefoIcon = '<i class="fas fa-circle text-warning" title="Segundo en prioridad FEFO"></i>';
                                } else {
                                    $fefoIcon = '<i class="fas fa-circle text-secondary" title="Lote posterior"></i>';
                                }

                                // =========================
                                // ESTADO DE VENCIMIENTO (PARA FILTROS)
                                // =========================
                                $hoy = \Carbon\Carbon::today();
                                $dias = $lote->fecha_vencimiento
                                    ? $hoy->diffInDays(\Carbon\Carbon::parse($lote->fecha_vencimiento), false)
                                    : null;

                                if (is_null($dias)) {
                                    $estadoVenc = 'sin';
                                } elseif ($dias < 0) {
                                    $estadoVenc = 'vencido';
                                } elseif ($dias <= 10) {
                                    $estadoVenc = '10';
                                } elseif ($dias <= 30) {
                                    $estadoVenc = '30';
                                } else {
                                    $estadoVenc = 'ok';
                                }
                            @endphp

                            <tr
                                data-estado="{{ $estadoVenc }}"
                                data-producto="{{ strtolower($lote->producto->nombre ?? '') }}"
                                data-stock="{{ $lote->stock_actual > 0 ? 'con' : 'sin' }}"
                                data-fefo="{{ $fefoIndex[$pid] === 1 ? '1' : '0' }}"
                                data-movimientos="{{ $lote->movimientos_count > 0 ? '1' : '0' }}"
                                data-texto="{{ strtolower(
                                    ($lote->codigo_comprobante ?? '') . ' ' .
                                    ($lote->producto->nombre ?? '')
                                ) }}"
                            >
                                {{-- CODIGO COMPROBANTE --}}
                                <td>
                                    <strong>{{ blank($lote->codigo_comprobante) ? '—' : $lote->codigo_comprobante }}</strong>

                                    @if (is_null($dias))
                                        <div>
                                            <span class="badge bg-secondary mt-1">Sin vencimiento</span>
                                        </div>
                                    @elseif ($dias < 0)
                                        <div>
                                            <span class="badge bg-danger mt-1">Vencido</span>
                                        </div>
                                    @elseif ($dias <= 10)
                                        <div>
                                            <span class="badge bg-danger mt-1">
                                                Vence en {{ $dias }} días
                                            </span>
                                        </div>
                                    @elseif ($dias <= 30)
                                        <div>
                                            <span class="badge bg-warning text-dark mt-1">
                                                Vence en {{ $dias }} días
                                            </span>
                                        </div>
                                    @endif
                                </td>

                                {{-- FEFO --}}
                                <td class="text-center">
                                    {!! $fefoIcon !!}
                                </td>

                                {{-- N° LOTE --}}
                                <td class="text-center fw-bold">
                                    LT-{{ str_pad($lote->numero_lote, 5, '0', STR_PAD_LEFT) }}
                                </td>

                                {{-- PRODUCTO --}}
                                <td>
                                    <strong>{{ $lote->producto->nombre ?? '—' }}</strong><br>
                                    <small class="text-muted">
                                        {{ \Illuminate\Support\Str::limit($lote->producto->descripcion ?? '', 50) }}
                                    </small>
                                </td>

                                {{-- PROVEEDOR --}}
                                <td>
                                    {{ $lote->proveedor->nombre ?? '—' }}
                                </td>

                                {{-- STOCK --}}
                                <td class="text-center fw-bold">
                                    {{ $lote->stock_actual }}
                                    <small class="text-muted d-block">
                                        / {{ $lote->stock_inicial }}
                                    </small>
                                </td>

                                {{-- INGRESO --}}
                                <td>
                                    {{ \Carbon\Carbon::parse($lote->fecha_ingreso)->format('d/m/Y') }}
                                </td>

                                {{-- VENCIMIENTO --}}
                                <td>
                                    @if ($lote->fecha_vencimiento)
                                        {{ \Carbon\Carbon::parse($lote->fecha_vencimiento)->format('d/m/Y') }}
                                    @else
                                        <span class="text-muted">No aplica</span>
                                    @endif
                                </td>

                                {{-- ESTADO --}}
                                <td class="text-center">
                                    @if ($lote->stock_actual == 0)
                                        <span class="badge bg-secondary">Agotado</span>
                                    @elseif ($lote->fecha_vencimiento && \Carbon\Carbon::parse($lote->fecha_vencimiento)->isPast())
                                        <span class="badge bg-danger">Vencido</span>
                                    @else
                                        <span class="badge bg-success">Activo</span>
                                    @endif
                                </td>

                                {{-- ACCIONES --}}
                                <td>
                                    <div class="d-flex gap-1 acciones-lote">
                                        <a href="{{ route('lotes.edit', $lote->id) }}"
                                        class="btn btn-sm btn-outline-primary"
                                        title="Editar lote">
                                            <i class="fas fa-pen"></i>
                                        </a>

                                        @if (($lote->movimientos_count ?? 0) > 0)
                                            <a href="{{ route('lotes.movimientos', $lote->id) }}"
                                            class="btn btn-sm btn-outline-info"
                                            title="Ver movimientos">
                                                <i class="fas fa-list-alt"></i>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-4 text-muted">
                                    No hay lotes registrados
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                </div>

            </div>

        </div>
    </div>

</div>
@endsection

{{-- ===================== STYLES ===================== --}}
@push('styles')
<style>
    
    .btn-outline-info:hover {
        color: #fff;
    }
    /*FEFO*/
    .badge.bg-danger {
        animation: pulse 1.5s infinite;
    }

    @keyframes pulse {
        0% { opacity: 1; }
        50% { opacity: .6; }
        100% { opacity: 1; }
    }

    /* =========================
    FILTROS LOTES
    ========================= */
    .filtros-lotes {
        background: #f8faff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 12px;
        margin-bottom: 12px;
    }

    .filtros-lotes .form-select,
    .filtros-lotes .form-control {
        border-radius: 10px;
        border: 1px solid #cfe2ff;
        font-weight: 500;
    }

    .filtros-lotes .form-select:focus,
    .filtros-lotes .form-control:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.15rem rgba(13,110,253,.15);
    }

    .filtros-lotes .form-select option:first-child {
        color: #6c757d;
    }

    .filtro-activo {
        border-color: #0d6efd !important;
        background-color: #e7f1ff;
    }
    /* =========================
    SCROLL HORIZONTAL PRO
    ========================= */
    .tabla-scroll {
        overflow-x: auto;
        scrollbar-width: thin;            /* Firefox */
        scrollbar-color: #c1c9d6 transparent;
    }

    .tabla-scroll::-webkit-scrollbar {
        height: 6px;
    }

    .tabla-scroll::-webkit-scrollbar-track {
        background: transparent;
    }

    .tabla-scroll::-webkit-scrollbar-thumb {
        background-color: #c1c9d6;
        border-radius: 10px;
    }

    .tabla-scroll::-webkit-scrollbar-thumb:hover {
        background-color: #9aa5b1;
    }

    #btnLimpiarFiltros {
    padding: 0 12px;
    height: 38px;
    }

    #btnLimpiarFiltros:hover {
        background-color: #ffd20c;
        border-color: #ffd20c;
        color: #0c0c0c;
    }

</style>
<style>
    /* BOTÓN ICONO FILTRO MOVIMIENTOS */
    .btn-icon {
        width: 38px;
        height: 38px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
    }

    /* Quitar borde azul molesto */
    .btn-icon:focus,
    .btn-icon:active,
    .btn-icon:focus-visible {
        box-shadow: none !important;
        outline: none !important;
    }

    /* Hover elegante */
    .btn-icon:hover {
        background-color: #eef4ff;
        border-color: #0d6efd;
        color: #0d6efd;
    }

    /* Cuando el filtro está activo */
    .btn-icon.activo {
        background-color: #0d6efd;
        border-color: #0d6efd;
        color: #fff;
    }

    /* COLUMNA ACCIONES LOTES */
    .acciones-lote {
        justify-content: flex-start; /* 👈 siempre a la izquierda */
        min-height: 38px;            /* mismo alto visual */
    }

    /* Botones consistentes */
    .acciones-lote .btn {
        width: 34px;
        height: 34px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

</style>
@endpush

{{-- ===================== SCRIPTS ===================== --}}
@push('scripts')

<script>
    document.getElementById('btnLimpiarFiltros')
        .addEventListener('click', function () {

            document.getElementById('filtroEstado').value = '';
            document.getElementById('filtroProducto').value = '';
            document.getElementById('filtroStock').value = '';
            document.getElementById('filtroFefo').value = '';
            document.getElementById('filtroBuscar').value = '';

            document.querySelectorAll('.filtro-activo')
                .forEach(el => el.classList.remove('filtro-activo'));

            // Mostrar todas las filas
            document.querySelectorAll('tbody tr').forEach(tr => {
                tr.style.display = '';
            });
    });
</script>

<script>
    let filtroMovimientosValor = "";
    
    document.addEventListener('DOMContentLoaded', function () { 

        const filas = document.querySelectorAll('tbody tr');

        const estado = document.getElementById('filtroEstado');
        const producto = document.getElementById('filtroProducto');
        const stock = document.getElementById('filtroStock');
        const fefo = document.getElementById('filtroFefo');
        const buscar = document.getElementById('filtroBuscar');

        function filtrar() {
            const vEstado = estado.value;
            const vProducto = producto.value;
            const vStock = stock.value;
            const vFefo = fefo.value;
            const vBuscar = buscar.value.toLowerCase();
            const vMov = filtroMovimientosValor;

            filas.forEach(tr => {
                let visible = true;

                if (vEstado && tr.dataset.estado !== vEstado) visible = false;
                if (vProducto && tr.dataset.producto !== vProducto) visible = false;
                if (vStock && tr.dataset.stock !== vStock) visible = false;
                if (vFefo && tr.dataset.fefo !== vFefo) visible = false;
                if (vBuscar && !tr.dataset.texto.includes(vBuscar)) visible = false;
                if (vMov && tr.dataset.movimientos !== vMov) visible = false;

                tr.style.display = visible ? '' : 'none';
            });
        }

        [estado, producto, stock, fefo].forEach(el =>
            el.addEventListener('change', filtrar)
        );

        [movimientos].forEach(el =>
            el.addEventListener('change', filtrar)
        );

        buscar.addEventListener('input', filtrar);
    });
</script>
<script>
    document.querySelectorAll('#filtroMovimientosBtn + .dropdown-menu a')
        .forEach(item => {
            item.addEventListener('click', e => {
                e.preventDefault();

                filtroMovimientosValor = item.dataset.mov || "";

                // feedback visual
                const btn = document.getElementById('filtroMovimientosBtn');
                btn.classList.toggle('activo', filtroMovimientosValor !== "");

                // aplicar filtro
                const evento = new Event('change');
                document.getElementById('filtroEstado').dispatchEvent(evento);
            });
        });
</script>

<script>
    document.querySelectorAll('.filtros-lotes select, .filtros-lotes input')
        .forEach(el => {
            el.addEventListener('change', () => {
                el.classList.toggle('filtro-activo', el.value !== '');
            });
            el.addEventListener('input', () => {
                el.classList.toggle('filtro-activo', el.value !== '');
            });
    });
</script>

@endpush
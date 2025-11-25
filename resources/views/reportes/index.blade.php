@extends('layouts.app')

@push('styles')
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
@endpush

@section('content')
<link rel="stylesheet" href="{{ asset('css/reportes.css') }}">

<div class="container">
    <h2 class="mb-4"><i class="fas fa-chart-line"></i> Reporte de Ganancias</h2>

    {{-- Filtro de fechas --}}
    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <label for="desde">Desde:</label>
            <input type="date" name="desde" id="desde" class="form-control"
                   value="{{ $desde ?? date('Y-m-01') }}">
        </div>
        <div class="col-md-4">
            <label for="hasta">Hasta:</label>
            <input type="date" name="hasta" id="hasta" class="form-control"
                   value="{{ $hasta ?? date('Y-m-d') }}">
        </div>
        <div class="col-md-4 d-flex align-items-end">
            <button type="button" id="filtrar" class="btn btn-primary w-100">
                <i class="fas fa-filter"></i> Filtrar
            </button>
        </div>
    </div>

    <hr class="my-4">
    <h4 class="mb-3">Resumen financiero</h4>

    {{-- Tarjetas en una sola fila horizontal --}}
    <div class="row row-cols-1 row-cols-md-5 g-3 text-center mb-4">
        <div class="col">
            <div class="card resumen-card border-success">
                <h6 class="text-success"><i class="fas fa-cash-register me-1"></i> Total Ventas</h6>
                <p id="total-ventas" class="fs-5 fw-bold text-success">
                    S/ {{ number_format($ventas, 2) }}
                </p>
            </div>
        </div>
        <div class="col">
            <div class="card resumen-card border-danger">
                <h6 class="text-danger"><i class="fas fa-box-open me-1"></i> Costo de Productos</h6>
                <p id="costo-productos" class="fs-5 fw-bold text-danger">
                    S/ {{ number_format($costo, 2) }}
                </p>
            </div>
        </div>
        <div class="col">
            <div class="card resumen-card border-info">
                <h6 class="text-info"><i class="fas fa-coins me-1"></i> Ganancia Bruta</h6>
                <p id="ganancia-bruta" class="fs-5 fw-bold text-info">
                    S/ {{ number_format($gananciaBruta, 2) }}
                </p>
            </div>
        </div>
        <div class="col">
            <div class="card resumen-card border-warning">
                <h6 class="text-warning"><i class="fas fa-money-bill-wave me-1"></i> Gastos</h6>
                <p id="gastos-report" class="fs-5 fw-bold text-warning">
                    S/ {{ number_format($gastos, 2) }}
                </p>
            </div>
        </div>
        <div class="col">
            @php
                $claseGanancia = $gananciaNeta >= 0 ? 'text-primary' : 'text-danger';
                $iconoGanancia = $gananciaNeta >= 0 ? 'fa-wallet' : 'fa-exclamation-triangle';
                $bordeGanancia = $gananciaNeta >= 0 ? 'primary' : 'danger';
            @endphp
            <div class="card resumen-card border-{{ $bordeGanancia }}">
                <h6 class="{{ $claseGanancia }}">
                    <i class="fas {{ $iconoGanancia }} me-1"></i> Ganancia Neta
                </h6>
                <p id="ganancia-neta" class="fs-5 fw-bold {{ $claseGanancia }}">
                    S/ {{ number_format($gananciaNeta, 2) }}
                </p>
            </div>
        </div>
    </div>

    {{-- Enlaces rápidos --}}
    <div class="row mt-4">
        <div class="col-md-6">
            <a href="{{ route('gastos.index') }}" class="btn btn-outline-dark w-100">
                <i class="fas fa-money-bill-wave"></i> Ver lista de gastos
            </a>
        </div>
        <div class="col-md-6">
            <a href="{{ route('ventas.index') }}" class="btn btn-outline-success w-100">
                <i class="fas fa-shopping-cart"></i> Ver lista de ventas
            </a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener("DOMContentLoaded", function () {
    const btnFiltrar = document.getElementById('filtrar');
    const inputDesde = document.getElementById('desde');
    const inputHasta = document.getElementById('hasta');

    function actualizarResumen() {
        const desde = inputDesde.value;
        const hasta = inputHasta.value;
        const url   = "{{ route('reportes.resumen') }}";

        fetch(`${url}?desde=${desde}&hasta=${hasta}`)
            .then(res => res.json())
            .then(data => {
                // Convertimos cada valor a Number antes de formatear
                document.getElementById('total-ventas').innerText    = `S/ ${( +data.ventas        ).toFixed(2)}`;
                document.getElementById('costo-productos').innerText = `S/ ${( +data.costo         ).toFixed(2)}`;
                document.getElementById('ganancia-bruta').innerText  = `S/ ${( +data.gananciaBruta ).toFixed(2)}`;
                document.getElementById('gastos-report').innerText   = `S/ ${( +data.gastos        ).toFixed(2)}`;
                document.getElementById('ganancia-neta').innerText   = `S/ ${( +data.gananciaNeta  ).toFixed(2)}`;
            })
            .catch(err => console.error('Error al obtener resumen:', err));
    }

    btnFiltrar.addEventListener('click', function (e) {
        e.preventDefault();
        actualizarResumen();
    });

    // Actualiza al cargar la página
    actualizarResumen();
});
</script>
@endpush

@extends('layouts.app')

@section('header-title')
Resumen de Inventario
@endsection

@push('styles')
    <link href="{{ asset('css/resumen.css') }}" rel="stylesheet" />
@endpush

@section('content')

<div class="container py-4">

    {{-- DASHBOARD CARDS PRO --}}
    <div class="row g-3 mb-4 flex-nowrap overflow-auto">

        {{-- Sin stock --}}
        <div class="col">
            <div class="card border-0 shadow-lg rounded-4 h-100 dashboard-card bg-gradient-danger text-white">
                <div class="card-body d-flex flex-column justify-content-between">

                    <div class="d-flex justify-content-between align-items-start">
                        <span class="small opacity-75 fw-semibold">
                            Productos sin stock
                        </span>
                        <i class="bi bi-x-circle fs-4 opacity-75"></i>
                    </div>

                    <div class="mt-3">
                        <h1 class="fw-bold display-6 mb-0 counter"
                            data-target="{{ $productosSinStock }}">
                            0
                        </h1>
                    </div>

                </div>
            </div>

        </div>

        {{-- Stock bajo --}}
        <div class="col">
            <div class="card border-0 shadow-lg rounded-4 h-100 dashboard-card bg-gradient-warning text-dark">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <p class="small mb-1">Stock bajo</p>
                        <h2 class="fw-bold mb-0 counter" data-target="{{ $productosStockBajo->count() }}">0</h2>
                    </div>
                    <i class="bi bi-exclamation-triangle fs-1 opacity-75"></i>
                </div>
            </div>
        </div>

        {{-- Por vencer --}}
        <div class="col">
            <div class="card border-0 shadow-lg rounded-4 h-100 dashboard-card bg-gradient-info text-white">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <p class="small mb-1 opacity-75">Lotes por vencer</p>
                        <h2 class="fw-bold mb-0 counter" data-target="{{ $lotesPorVencer->count() }}">0</h2>
                    </div>
                    <i class="bi bi-calendar-event fs-1 opacity-75"></i>
                </div>
            </div>
        </div>

        {{-- Total unidades --}}
        <div class="col">
            <div class="card border-0 shadow-lg rounded-4 h-100 dashboard-card bg-gradient-success text-white">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <p class="small mb-1 opacity-75">Total unidades</p>
                        <h2 class="fw-bold mb-0 counter" data-target="{{ $totalUnidades }}">0</h2>
                    </div>
                    <i class="bi bi-box-seam fs-1 opacity-75"></i>
                </div>
            </div>
        </div>

        {{-- TARJETA FINANCIERA ERP --}}
        <div class="col-md-6">
            <div class="card border-0 shadow-lg rounded-4 bg-dark text-white h-100">
                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <p class="small mb-1 opacity-75">Inversión total inventario</p>
                            <h2 class="fw-bold mb-0 counter-money" data-target="{{ $inversion }}">S/ 0</h2>
                        </div>
                        <div class="mt-3">
    <canvas id="miniFinanceChart" height="80"></canvas>
</div>

                        <div class="bg-success bg-opacity-25 p-3 rounded-circle">
                            <i class="bi bi-cash-coin fs-3 text-success"></i>
                        </div>
                    </div>

                    <hr class="border-secondary">

                    <div class="row text-center">

                        <div class="col-4">
                            <small class="opacity-75">Valor venta</small>
                            <div class="fw-bold">
                                S/ {{ number_format($valorVenta, 2) }}
                            </div>
                        </div>

                        <div class="col-4">
                            <small class="opacity-75">Margen</small>
                            <div class="fw-bold 
                                {{ $margenPotencial >= 0 ? 'text-success' : 'text-danger' }}">
                                S/ {{ number_format($margenPotencial, 2) }}
                            </div>
                        </div>

                        <div class="col-4">
                            <small class="opacity-75">Rentabilidad</small>
                            <div class="fw-bold 
                                {{ $porcentajeRentabilidad >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ number_format($porcentajeRentabilidad, 1) }}%
                            </div>
                        </div>

                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- PRODUCTOS CRÍTICOS --}}
    <div class="row g-4">

        <div class="col-md-6">
            {{-- PRODUCTOS CRÍTICOS --}}
            <div class="card shadow-lg rounded-4 border-0 h-100">
                <div class="card-header bg-white fw-bold border-0">
                    Productos críticos
                </div>
                <div class="card-body table-responsive">
                    <table class="table align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Producto</th>
                                <th>Stock</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($productosStockBajo as $producto)
                                <tr>
                                    <td>{{ $producto->nombre }}</td>
                                    <td class="fw-bold">{{ $producto->stock_total ?? 0 }}</td>
                                    <td>
                                        @if(($producto->stock_total ?? 0) == 0)
                                            <span class="badge bg-danger">Sin stock</span>
                                        @else
                                            <span class="badge bg-warning text-dark">Bajo</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>   
            </div>
        </div>
    

        <div class="col-md-6">
            {{-- LOTES POR VENCER --}}
            <div class="card shadow-lg rounded-4 border-0 h-100">
                <div class="card-header bg-white fw-bold border-0">
                    Lotes próximos a vencer (30 días)
                </div>
                <div class="card-body table-responsive">
                    <table class="table align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Lote</th>
                                <th>Producto</th>
                                <th>Stock</th>
                                <th>Vencimiento</th>
                                <th>Días restantes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($lotesPorVencer as $lote)
                                @php
                                    $dias = \Carbon\Carbon::now()->diffInDays($lote->fecha_vencimiento, false);
                                @endphp
                                <tr>
                                    <td>LT-{{ $lote->numero_lote }}</td>
                                    <td>{{ $lote->producto->nombre }}</td>
                                    <td>{{ $lote->stock_actual }}</td>
                                    <td class="fw-bold text-danger">
                                        {{ \Carbon\Carbon::parse($lote->fecha_vencimiento)->format('d/m/Y') }}
                                    </td>
                                    <td>
                                        <span class="badge bg-danger">
                                            {{ $dias }} días
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>

@endsection

@push('scripts')

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {

        const ctx = document.getElementById('miniFinanceChart');

        if (ctx) {
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Inversión', 'Margen'],
                    datasets: [{
                        data: [
                            {{ $inversion }},
                            {{ max($margenPotencial, 0) }}
                        ],
                        backgroundColor: [
                            '#0dcaf0',
                            '#28a745'
                        ],
                        borderWidth: 0
                    }]
                },
                options: {
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    cutout: '70%',
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        }

    });
</script>
<script>
document.addEventListener("DOMContentLoaded", () => {

    const counters = document.querySelectorAll('.counter');

    counters.forEach(counter => {
        const update = () => {
            const target = +counter.getAttribute('data-target');
            const current = +counter.innerText;
            const increment = target / 40;

            if (current < target) {
                counter.innerText = Math.ceil(current + increment);
                setTimeout(update, 20);
            } else {
                counter.innerText = target;
            }
        };
        update();
    });

    // Para dinero
    const moneyCounters = document.querySelectorAll('.counter-money');

    moneyCounters.forEach(counter => {
        const updateMoney = () => {
            const target = +counter.getAttribute('data-target');
            const current = parseFloat(counter.innerText.replace('S/','')) || 0;
            const increment = target / 40;

            if (current < target) {
                counter.innerText = "S/ " + (current + increment).toFixed(2);
                setTimeout(updateMoney, 20);
            } else {
                counter.innerText = "S/ " + target.toFixed(2);
            }
        };
        updateMoney();
    });

});
</script>
@endpush

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
    <div class="row g-4 mb-4">

        {{-- Sin stock --}}
        <div class="col-md-3">
            <div class="card border-0 shadow-lg rounded-4 h-100 dashboard-card bg-gradient-danger text-white">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <p class="small mb-1 opacity-75">Productos sin stock</p>
                        <h2 class="fw-bold mb-0">{{ $productosSinStock }}</h2>
                    </div>
                    <i class="bi bi-x-circle fs-1 opacity-75"></i>
                </div>
            </div>
        </div>

        {{-- Stock bajo --}}
        <div class="col-md-3">
            <div class="card border-0 shadow-lg rounded-4 h-100 dashboard-card bg-gradient-warning text-dark">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <p class="small mb-1">Stock bajo</p>
                        <h2 class="fw-bold mb-0">{{ $productosStockBajo->count() }}</h2>
                    </div>
                    <i class="bi bi-exclamation-triangle fs-1 opacity-75"></i>
                </div>
            </div>
        </div>

        {{-- Por vencer --}}
        <div class="col-md-3">
            <div class="card border-0 shadow-lg rounded-4 h-100 dashboard-card bg-gradient-info text-white">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <p class="small mb-1 opacity-75">Lotes por vencer</p>
                        <h2 class="fw-bold mb-0">{{ $lotesPorVencer->count() }}</h2>
                    </div>
                    <i class="bi bi-calendar-event fs-1 opacity-75"></i>
                </div>
            </div>
        </div>

        {{-- Total unidades --}}
        <div class="col-md-3">
            <div class="card border-0 shadow-lg rounded-4 h-100 dashboard-card bg-gradient-success text-white">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <p class="small mb-1 opacity-75">Total unidades</p>
                        <h2 class="fw-bold mb-0">{{ $totalUnidades }}</h2>
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
                            <h2 class="fw-bold mb-0">
                                S/ {{ number_format($inversion, 2) }}
                            </h2>
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
    <div class="card shadow-lg rounded-4 mb-4 border-0">
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

    {{-- LOTES POR VENCER --}}
    <div class="card shadow-lg rounded-4 border-0">
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

@endsection

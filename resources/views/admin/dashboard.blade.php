@extends('layouts.app')

@section('content')
<div class="container">
    <h3 class="mb-4"><i class="fas fa-tachometer-alt"></i> Panel de Administración</h3>
    <div class="card mb-4 shadow-sm">
    <div class="card-header bg-warning text-dark">
        <i class="fas fa-bolt"></i> Accesos Rápidos
    </div>
        <div class="card-body">
            <div class="row text-center g-3">
                <div class="col-sm-6 col-lg-3">
                    <a href="{{ route('ventas.index') }}" class="btn btn-outline-primary w-100 py-3">
                        <i class="fas fa-plus-circle fa-2x mb-2"></i><br>
                        Registrar Venta
                    </a>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <a href="{{ route('productos.index') }}" class="btn btn-outline-dark w-100 py-3">
                        <i class="fas fa-box-open fa-2x mb-2"></i><br>
                        Ver Productos
                    </a>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <a href="{{ route('usuarios.index') }}" class="btn btn-outline-success w-100 py-3">
                        <i class="fas fa-users fa-2x mb-2"></i><br>
                        Usuarios
                    </a>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <a href="{{ route('gastos.index') }}" class="btn btn-outline-danger w-100 py-3">
                        <i class="fas fa-wallet fa-2x mb-2"></i><br>
                        Gastos
                    </a>
                </div>
            </div>
        </div>
    </div>


    <div class="row g-4">
        <!-- Productos sin Stock -->
        <div class="col-lg-4 col-md-6">
            <div class="card">
                <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-exclamation-triangle"></i> Productos sin Stock</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                        <table class="table table-sm table-bordered table-hover mb-0 text-nowrap">
                            <thead class="table-danger text-center">
                                <tr>
                                    <th>ID</th>
                                    <th>Producto</th>
                                    <th>Descripción</th>
                                    <th>Stock</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($productosSinStock as $producto)
                                    <tr>
                                        <td>{{ $producto->id }}</td>
                                        <td>{{ $producto->nombre }}</td>
                                        <td>{{ $producto->descripcion }}</td>
                                        <td class="text-center">
                                            <span class="badge bg-danger">0 unidades</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">Todos los productos tienen stock.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráfico de Ventas -->
        <div class="col-lg-4 col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <i class="fas fa-chart-bar"></i> Ventas de la Semana
                </div>
                <div class="card-body">
                    <canvas id="ventasSemanaChart" height="220"></canvas>
                </div>
            </div>
        </div>

        <!-- Últimas Ventas -->
        <div class="col-lg-4 col-md-12">
            <div class="card shadow-sm">
                <div class="card-header bg-secondary text-white">
                    <i class="fas fa-clock"></i> Últimas Ventas Registradas
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                        <table class="table table-striped table-hover table-sm mb-0 text-nowrap">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Cliente</th>
                                    <th>Total</th>
                                    <th>Fecha</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($ultimasVentas as $venta)
                                    <tr>
                                        <td>{{ $venta->id }}</td>
                                        <td>{{ $venta->cliente->nombre ?? 'Sin cliente' }}</td>
                                        <td>S/ {{ number_format($venta->total, 2) }}</td>
                                        <td>{{ \Carbon\Carbon::parse($venta->fecha)->format('d/m/Y H:i') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">No hay ventas recientes.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('ventasSemanaChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($ventasSemana->pluck('fecha')->map(fn($f) => \Carbon\Carbon::parse($f)->format('d M'))) !!},
            datasets: [{
                label: 'Total Vendido (S/)',
                data: {!! json_encode($ventasSemana->pluck('total')) !!},
                backgroundColor: 'rgba(25, 135, 84, 0.8)',
                borderColor: 'rgba(25, 135, 84, 1)',
                borderWidth: 1,
                borderRadius: 6
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'S/ ' + value;
                        }
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
});
</script>
@endpush

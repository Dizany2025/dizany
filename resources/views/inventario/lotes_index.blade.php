@extends('layouts.app')

@section('header-title', 'Lotes de Inventario')

@section('content')
<div class="container-fluid px-3">

    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">
                <i class="fas fa-layer-group me-2"></i>
                Lotes registrados
            </h5>
        </div>

        <div class="card-body p-0">

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Producto</th>
                            <th>Proveedor</th>
                            <th class="text-center">Stock</th>
                            <th>Ingreso</th>
                            <th>Vencimiento</th>
                            <th class="text-center">Estado</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($lotes as $lote)
                            <tr>
                                <td>
                                    <strong>{{ $lote->producto->nombre ?? '—' }}</strong><br>
                                    <small class="text-muted">
                                        {{ $lote->producto->descripcion ?? '' }}
                                    </small>
                                </td>

                                <td>
                                    {{ $lote->proveedor->nombre ?? '—' }}
                                </td>

                                <td class="text-center fw-bold">
                                    {{ $lote->stock_actual }}
                                    <small class="text-muted d-block">
                                        / {{ $lote->stock_inicial }}
                                    </small>
                                </td>

                                <td>
                                    {{ \Carbon\Carbon::parse($lote->fecha_ingreso)->format('d/m/Y') }}
                                </td>

                                <td>
                                    @if ($lote->fecha_vencimiento)
                                        {{ \Carbon\Carbon::parse($lote->fecha_vencimiento)->format('d/m/Y') }}
                                    @else
                                        <span class="text-muted">No aplica</span>
                                    @endif
                                </td>

                                <td class="text-center">
                                    @if ($lote->stock_actual == 0)
                                        <span class="badge bg-secondary">Agotado</span>
                                    @elseif ($lote->fecha_vencimiento && \Carbon\Carbon::parse($lote->fecha_vencimiento)->isPast())
                                        <span class="badge bg-danger">Vencido</span>
                                    @else
                                        <span class="badge bg-success">Activo</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
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
@endsection

@extends('layouts.app')

{{-- BOTÓN ATRÁS --}}
@section('header-back')
<button class="btn-header-back" onclick="history.back()">
    <i class="fas fa-arrow-left"></i>
</button>
@endsection
@section('header-title', 'Lotes de Productos')

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
                            <th>Cód. Lote</th>
                            <th class="text-center" style="width:60px;">FEFO</th>
                            <th class="text-center" style="width:80px;">N° Lote</th>
                            <th>Producto</th>
                            <th>Proveedor</th>
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
                            @endphp

                            <tr>
                                <td>
                                    <td>
                                    <strong>{{ blank($lote->codigo_lote) ? '—' : $lote->codigo_lote }}</strong>
                                </td>

                                </td>
                                {{-- FEFO --}}
                                <td class="text-center">
                                    {!! $fefoIcon !!}
                                </td>

                                {{-- N° LOTE --}}
                                <td class="text-center fw-bold">
                                    {{ $fefoIndex[$pid] }}
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
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm" role="group">
                                        {{-- EDITAR --}}
                                        <a href="{{ route('lotes.edit', $lote->id) }}"
                                        class="btn btn-sm btn-outline-primary"
                                        title="Editar lote">
                                            <i class="fas fa-pen"></i>
                                        </a>
                                         {{-- MOVIMIENTOS --}}
                                        <a href="{{ route('lotes.movimientos', $lote->id) }}"
                                        class="btn btn-outline-secondary"
                                        title="Ver movimientos">
                                            <i class="fas fa-list-alt"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-4 text-muted">
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

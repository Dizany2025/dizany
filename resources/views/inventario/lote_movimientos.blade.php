@extends('layouts.app')

{{-- BOTÓN ATRÁS --}}
@section('header-back')
<button class="btn-header-back" onclick="history.back()">
    <i class="fas fa-arrow-left"></i>
</button>
@endsection

{{-- TÍTULO --}}
@section('header-title')
Movimientos del lote
@endsection

{{-- ACCIONES HEADER --}}
@section('header-buttons')
<a href="{{ route('inventario.lotes') }}" class="btn-gasto">
    <i class="fas fa-layer-group"></i>
    <span class="btn-text">Volver a lotes</span>
</a>
@endsection

@section('content')
<div class="container-fluid px-3 mt-4">

    {{-- INFO DEL LOTE --}}
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <div class="row g-3">

                <div class="col-md-4">
                    <strong>Producto</strong><br>
                    <span class="fw-semibold">
                        {{ $lote->producto->nombre }}
                    </span><br>
                    <small class="text-muted">
                        {{ $lote->producto->descripcion }}
                    </small>
                </div>

                <div class="col-md-4">
                    <strong>Cod. Lote</strong><br>
                    {{ $lote->codigo_comprobante ?? '—' }}
                </div>

                <div class="col-md-4">
                    <strong>Stock actual</strong><br>
                    <span class="fw-semibold">
                        {{ $lote->stock_actual }} / {{ $lote->stock_inicial }}
                    </span>
                </div>

            </div>
        </div>
    </div>

    {{-- TABLA DE MOVIMIENTOS --}}
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <i class="fas fa-history me-2"></i>
            Historial de movimientos
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Fecha</th>
                        <th>Tipo</th>
                        <th class="text-center">Cantidad</th>
                        <th class="text-center">Stock antes</th>
                        <th class="text-center">Stock después</th>
                        <th>Motivo</th>
                        <th>Usuario</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($movimientos as $m)

                        @php
                            $icon = match($m->tipo) {
                                'ingreso' => 'fa-arrow-down',
                                'venta'   => 'fa-shopping-cart',
                                'ajuste'  => 'fa-sliders-h',
                                'edicion' => 'fa-pen',
                                default   => 'fa-circle'
                            };

                            $badge = match($m->tipo) {
                                'ingreso' => 'success',
                                'venta'   => 'primary',
                                'ajuste'  => 'warning',
                                'edicion' => 'secondary',
                                default   => 'dark'
                            };
                        @endphp

                        <tr>
                            <td>
                                {{ \Carbon\Carbon::parse($m->creado_en)->format('d/m/Y H:i') }}
                            </td>

                            <td>
                                <span class="badge bg-{{ $badge }}">
                                    <i class="fas {{ $icon }} me-1"></i>
                                    {{ ucfirst($m->tipo) }}
                                </span>
                            </td>

                            <td class="text-center fw-semibold">
                                @if($m->cantidad > 0)
                                    <span class="text-success">+{{ $m->cantidad }}</span>
                                @elseif($m->cantidad < 0)
                                    <span class="text-danger">{{ $m->cantidad }}</span>
                                @else
                                    —
                                @endif
                            </td>

                            <td class="text-center">
                                {{ $m->stock_antes }}
                            </td>

                            <td class="text-center fw-bold">
                                {{ $m->stock_despues }}
                            </td>

                            <td>
                                {{ $m->motivo ?? '—' }}
                            </td>

                            <td>
                                {{ $m->usuario->nombre ?? 'Sistema' }}
                            </td>
                        </tr>

                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                No hay movimientos registrados
                            </td>
                        </tr>
                    @endforelse
                </tbody>

            </table>
        </div>

        {{-- PAGINACIÓN --}}
        <div class="card-footer">
            {{ $movimientos->links() }}
        </div>

    </div>

</div>
@endsection

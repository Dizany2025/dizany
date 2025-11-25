@extends('layouts.app')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

@endpush

@section('header-actions')
<div class="d-flex flex-wrap align-items-center gap-3 p-3">
    <a href="{{ route('ventas.listar') }}" class="atras">
        <i class="fas fa-chevron-left"></i> Lista de Ventas
    </a>
</div>
@endsection

@section('content')
<link href="{{ asset('css/detalle_venta.css') }}" rel="stylesheet">
<form id="form-editar-venta" method="POST" action="{{ route('ventas.update', $venta->id) }}" class="container-fluid px-2 px-md-4">
    @csrf
    @method('PUT')
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-edit"></i> Editar Venta #{{ $venta->id }}</h5>
        </div>
        <div class="card-body">
            <div class="row g-3 mb-4">
                <div class="col-12 col-sm-6 col-lg-4">
                    <label class="fw-bold">Cliente:</label>
                    <select name="cliente_id" class="form-select" required>
                        @foreach($clientes as $cliente)
                            <option value="{{ $cliente->id }}" {{ $venta->cliente_id == $cliente->id ? 'selected' : '' }}>
                                {{ $cliente->nombre }} - {{ $cliente->dni ?? $cliente->ruc }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-sm-6 col-lg-4">
                    <label class="fw-bold">Fecha de Venta:</label>
                    <input type="date" name="fecha" class="form-control"
                        value="{{ \Carbon\Carbon::parse($venta->fecha)->format('Y-m-d') }}" required>
                </div>
                <div class="col-12 col-sm-12 col-lg-4">
                    <label class="fw-bold">Método de Pago:</label>
                    <select name="metodo_pago" class="form-select" required>
                        <option value="Efectivo" {{ $venta->metodo_pago == 'Efectivo' ? 'selected' : '' }}>Efectivo</option>
                        <option value="Tarjeta" {{ $venta->metodo_pago == 'Tarjeta' ? 'selected' : '' }}>Tarjeta</option>
                        <option value="Yape" {{ $venta->metodo_pago == 'Yape' ? 'selected' : '' }}>Yape</option>
                        <option value="Plin" {{ $venta->metodo_pago == 'Plin' ? 'selected' : '' }}>Plin</option>
                    </select>
                </div>
            </div>

            <!-- Buscador -->
            <div class="position-relative mb-4">
                <input type="text" id="buscador-productos" class="form-control form-control-sm" placeholder="Buscar productos..." autocomplete="off">
                <div id="resultados-busqueda" class="position-absolute w-100 d-none p-2 border rounded bg-light shadow-sm"
                    style="z-index: 1000; max-height: 400px; overflow-y: auto;"></div>
            </div>

            <!-- Tabla de productos -->
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light text-center">
                        <tr>
                            <th>Producto</th>
                            <th>Descripción</th>
                            <th>Tipo Venta</th>
                            <th>Precio</th>
                            <th>Cantidad</th>
                            <th>Total</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="productos-venta">
                        @foreach($venta->detalleVentas as $detalle)
                        <tr data-producto-id="{{ $detalle->producto->id }}"
                            data-precio-unidad="{{ $detalle->precio_unitario }}"
                            data-precio-mayor="{{ $detalle->precio_mayor > 0 ? $detalle->precio_mayor : $detalle->producto->precio_mayor }}"
                            data-stock="{{ $detalle->producto->stock }}"
                            data-unidades-mayor="{{ $detalle->producto->unidades_por_mayor ?? 1 }}">
                            <td>{{ $detalle->producto->nombre }}</td>
                            <td>{{ $detalle->producto->descripcion }}</td>
                            <td>
                                <select class="form-select tipo-venta" name="productos[{{ $detalle->producto_id }}][tipo_venta]">
                                    <option value="unidad" {{ $detalle->tipo_venta === 'unidad' ? 'selected' : '' }}>Unidad</option>
                                    <option value="mayor" {{ $detalle->tipo_venta === 'mayor' ? 'selected' : '' }}>Mayor</option>
                                </select>
                            </td>
                            <td class="precio-unitario text-center">
                                S/ {{ number_format(
                                    $detalle->tipo_venta === 'mayor' && $detalle->precio_mayor > 0
                                        ? $detalle->precio_mayor
                                        : $detalle->precio_unitario, 2) }}
                            </td>
                            <td>
                                <input type="number"
                                    name="productos[{{ $detalle->producto_id }}][cantidad]"
                                    value="{{ $detalle->cantidad }}"
                                    min="1"
                                    max="{{ $detalle->tipo_venta === 'mayor' ? floor($detalle->producto->stock / ($detalle->producto->unidades_por_mayor ?? 1)) : $detalle->producto->stock }}"
                                    class="form-control form-control-sm cantidad"
                                    style="max-width: 80px; margin: auto;">
                            </td>
                            <td class="total-item text-center">
                                S/ {{ number_format(
                                    ($detalle->tipo_venta === 'mayor' && $detalle->precio_mayor > 0
                                        ? $detalle->precio_mayor
                                        : $detalle->precio_unitario) * $detalle->cantidad, 2) }}
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-danger btn-sm btn-eliminar-producto">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Totales -->
            <!-- Totales + Botones -->
                <div class="row justify-content-end mt-3">
                    <div class="col-md-4">
                        <!-- Campo oculto para el % de IGV desde la configuración -->
                        <input type="hidden" id="igv-config" value="{{ $config->igv ?? 0 }}">
                        <table class="table table-bordered mb-3">
                            <tr>
                                <th>Subtotal:</th>
                                <td id="subtotal">S/ {{ number_format($venta->total, 2) }}</td>
                            </tr>
                            <tr>
                                <th>IGV</th>
                                <td><span id="valor-igv-mostrado">{{ $config->igv ?? 0 }}%</span></td>
                            </tr>

                            <tr class="table-active">
                                <th>TOTAL:</th>
                                <td id="total-venta">S/ {{ number_format($venta->total, 2) }}</td>
                            </tr>
                        </table>

                        <!-- Botones alineados con el total -->
                        <div class="d-flex justify-content-end gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Guardar Cambios
                            </button>
                            <a href="{{ route('ventas.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
                        </div>
                    </div>
                </div>

        </div>

       
    </div>
</form>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="{{ asset('js/edit_list_ventas.js') }}"></script>
@endpush

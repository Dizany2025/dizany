@extends('layouts.app')

@section('header-actions')
<div class="d-flex align-items-center gap-3 p-3">
    <a href="{{ route('productos.index') }}" class="atras">
        <i class="fas fa-chevron-left"></i> Volver a Productos
    </a>
</div>
@endsection

@section('content')

<link href="{{ asset('css/crear_productos.css') }}" rel="stylesheet" />

<!-- Formulario en tarjeta -->
<div class="card shadow-sm mx-auto my-4" style="max-width: 960px;">
    <div class="card-header bg-primary text-white text-center">
        <h4 class="mb-0"><i class="fas fa-box-open"></i> Nuevo Producto</h4>
    </div>
    <div class="card-body">
        <form action="{{ route('productos.store') }}" method="POST" enctype="multipart/form-data" class="row g-4" id="form-producto">
            @csrf

            <div class="col-md-6 col-lg-4">
                <label for="codigo_barras" class="form-label">Código de Barras</label>
                <input type="text" class="form-control shadow-sm" id="codigo_barras" name="codigo_barras" required>
                <div id="codigo_barras_error" class="invalid-feedback d-none">
                    Este código de barras ya está registrado.
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <label for="nombre" class="form-label">Nombre</label>
                <input type="text" class="form-control shadow-sm" id="nombre" name="nombre" required>
            </div>

            <div class="col-lg-4">
                <label for="descripcion" class="form-label">Descripción</label>
                <textarea class="form-control shadow-sm" id="descripcion" name="descripcion" rows="1"></textarea>
            </div>

            <div class="col-md-6 col-lg-4">
                <label for="precio_compra" class="form-label">Precio de Compra</label>
                <input type="number" step="0.01" class="form-control shadow-sm" id="precio_compra" name="precio_compra" required>
            </div>

            <div class="col-md-6 col-lg-4">
                <label for="precio_venta" class="form-label">Precio de Venta</label>
                <input type="number" step="0.01" class="form-control shadow-sm" id="precio_venta" name="precio_venta" required>
            </div>

            <div class="col-md-6 col-lg-4">
                <label for="precio_mayor" class="form-label">Precio por Mayor</label>
                <input type="number" step="0.01" class="form-control shadow-sm" id="precio_mayor" name="precio_mayor">
            </div>

            <div class="col-md-6 col-lg-4">
                <label for="unidades_por_mayor" class="form-label">Unidades Por (Paquete/Caja.)</label>
                <input type="number" class="form-control shadow-sm" id="unidades_por_mayor" name="unidades_por_mayor" placeholder="Ej: 6, 12, 24">
            </div>

            <div class="col-md-6 col-lg-4">
                <label for="stock" class="form-label">Stock</label>
                <input type="number" class="form-control shadow-sm" id="stock" name="stock" required>
            </div>

            <div class="col-md-6 col-lg-4">
                <label for="ubicacion" class="form-label">Ubicación</label>
                <input type="text" class="form-control shadow-sm" id="ubicacion" name="ubicacion">
            </div>

            <div class="col-md-6 col-lg-4">
                <label for="fecha_vencimiento" class="form-label">Fecha de Vencimiento</label>
                <input type="date" class="form-control shadow-sm" id="fecha_vencimiento" name="fecha_vencimiento">
            </div>

            <div class="col-md-6 col-lg-4">
                <label for="imagen" class="form-label">Imagen</label>
                <input type="file" class="form-control shadow-sm" id="imagen" name="imagen" accept="image/*">
            </div>

            <div class="col-md-6 col-lg-4">
                <label for="categoria_id" class="form-label">Categoría</label>
                <select class="form-select shadow-sm" id="categoria_id" name="categoria_id" required>
                    <option value="">-- Seleccione --</option>
                    @foreach($categorias as $categoria)
                        <option value="{{ $categoria->id }}">{{ $categoria->nombre }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6 col-lg-4">
                <label for="marca_id" class="form-label">Marca</label>
                <select class="form-select shadow-sm" id="marca_id" name="marca_id" required>
                    <option value="">-- Seleccione --</option>
                    @foreach($marcas as $marca)
                        <option value="{{ $marca->id }}">{{ $marca->nombre }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-12 text-center mt-4">
                <button type="submit" class="btn btn-success px-4 py-2">
                    <i class="fas fa-save"></i> Guardar Producto
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
    <script src="{{ asset('js/validarCodigoBarras.js') }}"></script>
    <script src="{{ asset('js/registrar_producto.js') }}"></script>
    @if(session('success'))
<script>
    document.addEventListener('DOMContentLoaded', () => {
        Swal.fire({
            icon: 'success',
            title: '¡Producto registrado!',
            text: '{{ session('success') }}',
            timer: 2000,
            showConfirmButton: false
        });
    });
</script>
@endif

@if(session('error'))
<script>
    document.addEventListener('DOMContentLoaded', () => {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: '{{ session('error') }}'
        });
    });
</script>
@endif

@endpush

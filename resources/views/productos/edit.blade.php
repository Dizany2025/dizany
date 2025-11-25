@extends('layouts.app')

@push('styles')
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <!-- Estilos personalizados -->
    <link href="{{ asset('css/edit_productos.css') }}" rel="stylesheet" />
@endpush

@section('title', 'Editar Producto')

@section('header-actions')
<div class="d-flex align-items-center gap-3 p-3">
    <a href="{{ route('productos.index') }}" class="atras">
        <i class="fas fa-chevron-left"></i> Productos
    </a>
</div>
@endsection

@section('content')
<!-- Formulario de edición -->
<div class="card shadow my-4 mx-auto" style="max-width: 900px;">
    <div class="card-header bg-primary text-white text-center">
        <h4 class="mb-0">Editar Producto</h4>
    </div>

    <div class="card-body">
        <input type="hidden" id="producto_id" value="{{ $producto->id }}">

        <form action="{{ route('productos.update', $producto->id) }}" method="POST" enctype="multipart/form-data" id="form-editar-producto">
            @csrf
            @method('PUT')
            <div class="row g-3">
                <!-- Código de Barras -->
                <div class="col-md-6 col-lg-4">
                    <label for="codigo_barras" class="form-label">Código de Barras</label>
                    <input type="text" class="form-control shadow-sm" id="codigo_barras" name="codigo_barras"
                        value="{{ old('codigo_barras', $producto->codigo_barras) }}" required>
                    <div id="codigo_barras_error" class="invalid-feedback d-none">El código ya está registrado.</div>
                </div>

                <!-- Nombre -->
                <div class="col-md-6 col-lg-4">
                    <label for="nombre" class="form-label">Nombre</label>
                    <input type="text" class="form-control shadow-sm" id="nombre" name="nombre"
                        value="{{ old('nombre', $producto->nombre) }}" required>
                </div>

                <!-- Descripción -->
                <div class="col-12">
                    <label for="descripcion" class="form-label">Descripción</label>
                    <textarea class="form-control shadow-sm" id="descripcion" name="descripcion" rows="2" required>{{ old('descripcion', $producto->descripcion) }}</textarea>
                </div>

                <!-- Precio Compra -->
                <div class="col-md-6 col-lg-4">
                    <label for="precio_compra" class="form-label">Precio de Compra</label>
                    <input type="number" step="0.01" class="form-control shadow-sm" id="precio_compra" name="precio_compra"
                        value="{{ old('precio_compra', $producto->precio_compra) }}" required>
                </div>

                <!-- Precio Venta -->
                <div class="col-md-6 col-lg-4">
                    <label for="precio_venta" class="form-label">Precio de Venta</label>
                    <input type="number" step="0.01" class="form-control shadow-sm" id="precio_venta" name="precio_venta"
                        value="{{ old('precio_venta', $producto->precio_venta) }}" required>
                </div>

                <!-- Precio Mayor -->
                <div class="col-md-6 col-lg-4">
                    <label for="precio_mayor" class="form-label">Precio Mayor</label>
                    <input type="number" step="0.01" class="form-control shadow-sm" id="precio_mayor" name="precio_mayor"
                        value="{{ old('precio_mayor', $producto->precio_mayor) }}" required>
                </div>

                <!-- Unidades por Mayor -->
                <div class="col-md-6 col-lg-4">
                    <label for="unidades_por_mayor" class="form-label">Unidades Por (Paquete/Caja.)</label>
                    <input type="number" class="form-control shadow-sm" id="unidades_por_mayor" name="unidades_por_mayor"
                        value="{{ old('unidades_por_mayor', $producto->unidades_por_mayor) }}" required>
                </div>

                <!-- Stock -->
                <div class="col-md-6 col-lg-4">
                    <label for="stock" class="form-label">Stock</label>
                    <input type="number" class="form-control shadow-sm" id="stock" name="stock"
                        value="{{ old('stock', $producto->stock) }}" required>
                </div>

                <!-- Ubicación -->
                <div class="col-md-6 col-lg-4">
                    <label for="ubicacion" class="form-label">Ubicación</label>
                    <input type="text" class="form-control shadow-sm" id="ubicacion" name="ubicacion"
                        value="{{ old('ubicacion', $producto->ubicacion) }}">
                </div>

                <!-- Fecha Vencimiento -->
                <div class="col-md-6 col-lg-4">
                    <label for="fecha_vencimiento" class="form-label">Fecha de Vencimiento</label>
                    <input type="date" class="form-control shadow-sm" id="fecha_vencimiento" name="fecha_vencimiento"
                        value="{{ old('fecha_vencimiento', $producto->fecha_vencimiento) }}">
                </div>

                <!-- Categoría -->
                <div class="col-md-6 col-lg-4">
                    <label for="categoria_id" class="form-label">Categoría</label>
                    <select class="form-select shadow-sm" id="categoria_id" name="categoria_id" required>
                        <option value="">Seleccione categoría</option>
                        @foreach($categorias as $categoria)
                            <option value="{{ $categoria->id }}" {{ $producto->categoria_id == $categoria->id ? 'selected' : '' }}>
                                {{ $categoria->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Marca -->
                <div class="col-md-6 col-lg-4">
                    <label for="marca_id" class="form-label">Marca</label>
                    <select class="form-select shadow-sm" id="marca_id" name="marca_id" required>
                        <option value="">Seleccione marca</option>
                        @foreach($marcas as $marca)
                            <option value="{{ $marca->id }}" {{ $producto->marca_id == $marca->id ? 'selected' : '' }}>
                                {{ $marca->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Imagen -->
                <div class="col-12">
                    <label for="imagen" class="form-label">Imagen</label>
                    <input type="file" class="form-control shadow-sm" id="imagen" name="imagen" accept="image/*">
                    <small class="text-muted">Deja vacío si no deseas cambiarla.</small>
                    @if($producto->imagen && file_exists(public_path('uploads/productos/' . $producto->imagen)))
                        <div class="mt-2">
                            <img src="{{ asset('uploads/productos/' . $producto->imagen) }}" alt="Imagen actual" class="img-thumbnail" width="150">
                        </div>
                    @endif
                </div>
            </div>

            <div class="text-center mt-4">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save"></i> Actualizar Producto
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function () {
        $('#codigo_barras').on('input', function () {
            const codigo = $(this).val();
            const productoId = $('#producto_id').val();

            $.get('{{ route('productos.validarCodigoBarras') }}', {
                codigo_barras: codigo,
                producto_id: productoId
            }, function (response) {
                if (response.exists) {
                    $('#codigo_barras').addClass('is-invalid');
                    $('#codigo_barras_error').removeClass('d-none').text('Este código ya está registrado.');
                } else {
                    $('#codigo_barras').removeClass('is-invalid');
                    $('#codigo_barras_error').addClass('d-none');
                }
            });
        });
    });
</script>
<script>
    @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: '¡Producto actualizado!',
            text: '{{ session('success') }}',
            showConfirmButton: false,
            timer: 2000
        });
    @elseif(session('error'))
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: '{{ session('error') }}',
            showConfirmButton: false,
            timer: 3000
        });
    @endif
</script>
@endpush

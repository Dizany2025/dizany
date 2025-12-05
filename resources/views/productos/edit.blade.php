@extends('layouts.app')

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
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
<link href="{{ asset('css/edit_productos.css') }}" rel="stylesheet" />
<div class="card shadow my-4 mx-auto" style="max-width: 900px;">
    <div class="card-header bg-primary text-white text-center">
        <h4 class="mb-0">Editar Producto</h4>
    </div>

    <div class="card-body">

        {{-- Errores --}}
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

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

                <!-- Slug -->
                <div class="col-md-6 col-lg-4">
                    <label for="slug" class="form-label">Slug (URL)</label>
                    <input type="text" class="form-control shadow-sm" id="slug" name="slug"
                        value="{{ old('slug', $producto->slug) }}" readonly>
                </div>

                <!-- Descripción -->
                <div class="col-12">
                    <label for="descripcion" class="form-label">Descripción</label>
                    <textarea class="form-control shadow-sm" id="descripcion" name="descripcion" rows="2">{{ old('descripcion', $producto->descripcion) }}</textarea>
                </div>

                <!-- Precio Compra -->
                <div class="col-md-6 col-lg-4">
                    <label for="precio_compra" class="form-label">Precio de Compra</label>
                    <div class="input-group shadow-sm">
                        <span class="input-group-text">S/</span>
                        <input type="number" step="0.01" class="form-control" id="precio_compra" name="precio_compra"
                            value="{{ old('precio_compra', $producto->precio_compra) }}" required>
                    </div>
                </div>

                <!-- Precio Venta -->
                <div class="col-md-6 col-lg-4">
                    <label for="precio_venta" class="form-label">Precio de Venta (Unidad)</label>
                    <div class="input-group shadow-sm">
                        <span class="input-group-text">S/</span>
                        <input type="number" step="0.01" class="form-control" id="precio_venta" name="precio_venta"
                            value="{{ old('precio_venta', $producto->precio_venta) }}" required>
                    </div>
                </div>

                <!-- Precio Paquete -->
                <div class="col-md-6 col-lg-4">
                    <label for="precio_paquete" class="form-label">Precio Paquete</label>
                    <div class="input-group shadow-sm">
                        <span class="input-group-text">S/</span>
                        <input type="number" step="0.01" class="form-control" id="precio_paquete" name="precio_paquete"
                            value="{{ old('precio_paquete', $producto->precio_paquete) }}">
                    </div>
                </div>

                <!-- Unidades por Paquete -->
                <div class="col-md-6 col-lg-4">
                    <label for="unidades_por_paquete" class="form-label">Unidades por Paquete</label>
                    <input type="number" class="form-control shadow-sm" id="unidades_por_paquete" name="unidades_por_paquete"
                        value="{{ old('unidades_por_paquete', $producto->unidades_por_paquete) }}">
                </div>

                <!-- Paquetes por Caja -->
                <div class="col-md-6 col-lg-4">
                    <label for="paquetes_por_caja" class="form-label">Paquetes por Caja</label>
                    <input type="number" class="form-control shadow-sm" id="paquetes_por_caja" name="paquetes_por_caja"
                        value="{{ old('paquetes_por_caja', $producto->paquetes_por_caja) }}">
                </div>

                <!-- Cantidad de Cajas (solo para recalcular stock) -->
                <div class="col-md-6 col-lg-4">
                    <label for="cantidad_cajas" class="form-label">Cantidad de Cajas (stock)</label>
                    <input type="number" class="form-control shadow-sm" id="cantidad_cajas" name="cantidad_cajas"
                    value="{{ old('cantidad_cajas', $producto->paquetes_por_caja && $producto->unidades_por_paquete ? 
                    intval($producto->stock / ($producto->paquetes_por_caja * $producto->unidades_por_paquete)) : '') }}">
                </div>

                <!-- Precio Caja -->
                <div class="col-md-6 col-lg-4">
                    <label for="precio_caja" class="form-label">Precio Caja</label>
                    <div class="input-group shadow-sm">
                        <span class="input-group-text">S/</span>
                        <input type="number" step="0.01" class="form-control" id="precio_caja" name="precio_caja"
                            value="{{ old('precio_caja', $producto->precio_caja) }}">
                    </div>
                </div>

                <!-- Tipo de Paquete -->
                <div class="col-md-6 col-lg-4">
                    <label for="tipo_paquete" class="form-label">Tipo de Paquete</label>
                    <input type="text" class="form-control shadow-sm" id="tipo_paquete" name="tipo_paquete"
                        value="{{ old('tipo_paquete', $producto->tipo_paquete) }}">
                </div>

                <!-- Stock -->
                <div class="col-md-6 col-lg-4">
                    <label for="stock" class="form-label">Stock (unidades)</label>
                    <input type="number" class="form-control shadow-sm" id="stock" name="stock"
                        value="{{ old('stock', $producto->stock) }}" required>
                </div>

                <!-- Ubicación -->
                <div class="col-md-6 col-lg-4">
                    <label for="ubicacion" class="form-label">Ubicación</label>
                    <input type="text" class="form-control shadow-sm" id="ubicacion" name="ubicacion"
                        value="{{ old('ubicacion', $producto->ubicacion) }}">
                </div>

                <!-- Fecha de vencimiento -->
                <div class="col-md-6 col-lg-4">
                    <label for="fecha_vencimiento" class="form-label">Fecha de Vencimiento</label>
                    <input type="date" class="form-control shadow-sm" id="fecha_vencimiento" name="fecha_vencimiento"
                        value="{{ old('fecha_vencimiento', optional($producto->fecha_vencimiento)->format('Y-m-d')) }}">
                </div>

                <!-- Categoría -->
                <div class="col-md-4">
                    <label class="form-label d-flex justify-content-between">
                        <span>Categoría</span>
                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalNuevaCategoria">
                            <i class="fas fa-plus"></i>
                        </button>
                    </label>

                    <select name="categoria_id" id="categoria_id" class="form-select" required>
                        <option value="">Seleccione</option>
                        @foreach($categorias as $categoria)
                            <option value="{{ $categoria->id }}"
                                {{ (old('categoria_id', $producto->categoria_id) == $categoria->id) ? 'selected' : '' }}>
                                {{ $categoria->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <!-- Marca -->
                <div class="col-md-4">
                    <label class="form-label d-flex justify-content-between">
                        <span>Marca</span>
                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalNuevaMarca">
                            <i class="fas fa-plus"></i>
                        </button>
                    </label>

                    <select name="marca_id" id="marca_id" class="form-select">
                        <option value="">Seleccione</option>
                        @foreach($marcas as $marca)
                            <option value="{{ $marca->id }}"
                                {{ (old('marca_id', $producto->marca_id) == $marca->id) ? 'selected' : '' }}>
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

                <!-- Activo y visible -->
                <div class="col-md-6 col-lg-4">
                    <div class="form-check mt-3">
                        <input type="checkbox" class="form-check-input" id="activo" name="activo"
                            {{ old('activo', $producto->activo ?? 1) ? 'checked' : '' }}>
                        <label class="form-check-label" for="activo">Activo</label>
                    </div>

                    <div class="form-check mt-2">
                        <input type="checkbox" class="form-check-input" id="visible_en_catalogo" name="visible_en_catalogo"
                            {{ old('visible_en_catalogo', $producto->visible_en_catalogo ?? 1) ? 'checked' : '' }}>
                        <label class="form-check-label" for="visible_en_catalogo">Visible en Catálogo</label>
                    </div>
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
<!-- Modal Nueva Categoría -->
<div class="modal fade" id="modalNuevaCategoria" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Nueva Categoría</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <label class="form-label">Nombre</label>
                <input type="text" id="nueva_categoria_nombre" class="form-control">
                <small id="error_categoria" class="text-danger d-none"></small>
            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button class="btn btn-success" id="btnGuardarCategoria">Guardar</button>
            </div>

        </div>
    </div>
</div>
<!-- Modal Nueva Marca -->
<div class="modal fade" id="modalNuevaMarca" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Nueva Marca</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <label class="form-label">Nombre</label>
                <input type="text" id="nueva_marca_nombre" class="form-control">
                <small id="error_marca" class="text-danger d-none"></small>
            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button class="btn btn-success" id="btnGuardarMarca">Guardar</button>
            </div>

        </div>
    </div>
</div>
@endsection

@push('scripts')

<!-- Validación de código de barras -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
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
</script>

<!-- ==== SCRIPT PARA CALCULAR STOCK AUTOMÁTICO EN EDITAR ==== -->
<script>
    function calcularStockEdit() {
        const cajas    = parseInt(document.getElementById('cantidad_cajas')?.value) || 0;
        const paquetes = parseInt(document.getElementById('paquetes_por_caja')?.value) || 0;
        const unidades = parseInt(document.getElementById('unidades_por_paquete')?.value) || 0;

        let stock = 0;

        if (cajas && paquetes && unidades) {
            // Caja -> Paquete -> Unidad
            stock = cajas * paquetes * unidades;
        } 
        else if (cajas && unidades && !paquetes) {
            // Caja -> Unidad (vino)
            stock = cajas * unidades;
        } 
        else if (paquetes && unidades && !cajas) {
            // Paquete -> Unidad
            stock = paquetes * unidades;
        }

        if (stock > 0) {
            document.getElementById('stock').value = stock;
        }
    }

    ['cantidad_cajas', 'paquetes_por_caja', 'unidades_por_paquete'].forEach(id => {
        const el = document.getElementById(id);
        if (el) {
            el.addEventListener('input', calcularStockEdit);
            el.addEventListener('change', calcularStockEdit);
        }
    });
</script>

<!-- Alertas SweetAlert -->
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
<script>

// GUARDAR CATEGORÍA
$("#btnGuardarCategoria").click(function() {
    let nombre = $("#nueva_categoria_nombre").val().trim();

    if (!nombre) {
        $("#error_categoria").text("El nombre es obligatorio.").removeClass("d-none");
        return;
    }

    $.post("{{ route('categoria.ajax.store') }}", {
        nombre: nombre,
        _token: '{{ csrf_token() }}'
    }, function(response) {

        // Si ya existe
        if (response.error) {
            $("#error_categoria").text(response.message).removeClass("d-none");
            return;
        }

        // Si se guarda correctamente, limpiar errores
        $("#error_categoria").addClass("d-none").text("");

        // Insertar en el select y seleccionar automáticamente
        $("#categoria_id").append(
            `<option value="${response.data.id}" selected>${response.data.nombre}</option>`
        );

        // Cerrar modal
        $("#modalNuevaCategoria").modal("hide");
        $("#nueva_categoria_nombre").val("");

        Swal.fire("Éxito", "Categoría registrada correctamente.", "success");
    });
});

// GUARDAR MARCA
$("#btnGuardarMarca").click(function() {
    let nombre = $("#nueva_marca_nombre").val().trim();

    if (!nombre) {
        $("#error_marca").text("El nombre es obligatorio.").removeClass("d-none");
        return;
    }

    $.post("{{ route('marca.ajax.store') }}", {
        nombre: nombre,
        _token: '{{ csrf_token() }}'
    }, function(response) {

        // Si la marca ya existe
        if (response.error) {
            $("#error_marca").text(response.message).removeClass("d-none");
            return;
        }

        // Si se guarda correctamente
        $("#error_marca").addClass("d-none").text("");

        // Agregar al select y seleccionar automáticamente
        $("#marca_id").append(
            `<option value="${response.data.id}" selected>${response.data.nombre}</option>`
        );

        // Cerrar modal
        $("#modalNuevaMarca").modal("hide");
        $("#nueva_marca_nombre").val("");

        Swal.fire("Éxito", "Marca registrada correctamente.", "success");
    });
});

</script>

@endpush

@extends('layouts.app')

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link href="{{ asset('css/edit_productos.css') }}" rel="stylesheet" />
@endpush

@section('title', 'Editar Producto')

{{-- BOTÓN ATRÁS --}}
@section('header-back')
<button class="btn-header-back"
        type="button"
        onclick="window.location='{{ route('productos.index') }}'">
    <i class="fas fa-chevron-left"></i>
</button>
@endsection

{{-- TÍTULO --}}
@section('header-title')
Editar Producto
@endsection

{{-- BOTONES DERECHA --}}
@section('header-buttons')
{{-- sin acciones --}}
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

        <form action="{{ route('productos.update', $producto->id) }}"
      method="POST"
      enctype="multipart/form-data"
      id="form-editar-producto">

@csrf
@method('PUT')

<div class="row g-3">

    <!-- ================= CÓDIGO DE BARRAS ================= -->
    <div class="col-md-6 col-lg-4">
        <label class="form-label">Código de Barras</label>
        <input type="text"
               class="form-control shadow-sm"
               id="codigo_barras"
               name="codigo_barras"
               value="{{ old('codigo_barras', $producto->codigo_barras) }}"
               required>
        <div id="codigo_barras_error"
             class="invalid-feedback d-none">
            El código ya está registrado.
        </div>
    </div>

    <!-- ================= NOMBRE ================= -->
    <div class="col-md-6 col-lg-4">
        <label class="form-label">Nombre</label>
        <input type="text"
               class="form-control shadow-sm"
               id="nombre"
               name="nombre"
               value="{{ old('nombre', $producto->nombre) }}"
               required>
    </div>

    <!-- ================= SLUG ================= -->
    <div class="col-md-6 col-lg-4">
        <label class="form-label">Slug</label>
        <input type="text"
               class="form-control shadow-sm"
               id="slug"
               name="slug"
               value="{{ old('slug', $producto->slug) }}"
               readonly>
    </div>

    <!-- ================= DESCRIPCIÓN ================= -->
    <div class="col-12">
        <label class="form-label">Descripción</label>
        <textarea class="form-control shadow-sm"
                  name="descripcion"
                  rows="2">{{ old('descripcion', $producto->descripcion) }}</textarea>
    </div>

    <!-- ================= PRECIOS ================= -->
    <div class="col-md-4">
        <label class="form-label">Precio Compra</label>
        <div class="input-group shadow-sm">
            <span class="input-group-text">S/</span>
            <input type="number" step="0.01"
                   name="precio_compra"
                   class="form-control"
                   value="{{ old('precio_compra', $producto->precio_compra) }}"
                   required>
        </div>
    </div>

    <div class="col-md-4">
        <label class="form-label">Precio Venta (Unidad)</label>
        <div class="input-group shadow-sm">
            <span class="input-group-text">S/</span>
            <input type="number" step="0.01"
                   name="precio_venta"
                   class="form-control"
                   value="{{ old('precio_venta', $producto->precio_venta) }}"
                   required>
        </div>
    </div>

    <div class="col-md-4">
        <label class="form-label">Precio Paquete</label>
        <div class="input-group shadow-sm">
            <span class="input-group-text">S/</span>
            <input type="number" step="0.01"
                   name="precio_paquete"
                   class="form-control"
                   value="{{ old('precio_paquete', $producto->precio_paquete) }}">
        </div>
    </div>

    <div class="col-md-4">
        <label class="form-label">Precio Caja</label>
        <div class="input-group shadow-sm">
            <span class="input-group-text">S/</span>
            <input type="number" step="0.01"
                   name="precio_caja"
                   class="form-control"
                   value="{{ old('precio_caja', $producto->precio_caja) }}">
        </div>
    </div>

    <!-- ================= CONVERSIONES ================= -->
    <div class="col-md-4">
        <label class="form-label">Unidades por paquete / caja</label>
        <input type="number"
               class="form-control shadow-sm"
               name="unidades_por_paquete"
               value="{{ old('unidades_por_paquete', $producto->unidades_por_paquete) }}">
    </div>

    <div class="col-md-4">
        <label class="form-label">Paquetes por caja</label>
        <input type="number"
               class="form-control shadow-sm"
               name="paquetes_por_caja"
               value="{{ old('paquetes_por_caja', $producto->paquetes_por_caja) }}">
    </div>

    <!-- ================= STOCK ACTUAL (A) ================= -->
    <div class="col-md-4">
        <label class="form-label fw-bold">
            Stock actual (editable)
        </label>
        <input type="number"
               class="form-control shadow-sm"
               name="stock"
               id="stock_actual"
               value="{{ old('stock', $producto->stock) }}">
        <small class="text-muted">
            Puedes editarlo manualmente si lo deseas
        </small>
    </div>

    <!-- ================= RECALCULAR STOCK (B + C) ================= -->
    <div class="col-12 mt-4">
        <div class="alert alert-warning">
            <strong>Recalcular stock (opcional)</strong><br>
            Usa este bloque solo si estás ingresando nuevo stock.
            Si lo dejas vacío, se mantiene el stock actual.
        </div>

        <div class="form-check">
            <input class="form-check-input" type="radio"
                   name="nivel_ingreso" value="unidad">
            <label class="form-check-label">Ingreso por unidades</label>
        </div>

        <div class="form-check">
            <input class="form-check-input" type="radio"
                   name="nivel_ingreso" value="paquete">
            <label class="form-check-label">Ingreso por paquetes</label>
        </div>

        <div class="form-check">
            <input class="form-check-input" type="radio"
                   name="nivel_ingreso" value="caja">
            <label class="form-check-label">Ingreso por cajas</label>
        </div>

        <div class="row mt-2">
            <div class="col-md-4">
                <label class="form-label">Cantidad ingresada</label>
                <input type="number"
                       class="form-control shadow-sm"
                       name="cantidad_ingresada"
                       value="{{ old('cantidad_ingresada') }}">
            </div>

            <div class="col-md-4">
                <label class="form-label">Nuevo stock calculado</label>
                <input type="number"
                       class="form-control shadow-sm"
                       name="stock_calculado"
                       readonly>
            </div>
        </div>
    </div>

    <!-- ================= UBICACIÓN / FECHA ================= -->
    <div class="col-md-4">
        <label class="form-label">Ubicación</label>
        <input type="text"
               class="form-control shadow-sm"
               name="ubicacion"
               value="{{ old('ubicacion', $producto->ubicacion) }}">
    </div>

    <div class="col-md-4">
        <label class="form-label">Fecha de vencimiento</label>
        <input type="date"
               class="form-control shadow-sm"
               name="fecha_vencimiento"
               value="{{ old('fecha_vencimiento', optional($producto->fecha_vencimiento)->format('Y-m-d')) }}">
    </div>

    <!-- ================= CATEGORÍA / MARCA ================= -->
    <div class="col-md-4">
        <label class="form-label d-flex justify-content-between">
            <span>Categoría</span>
            <button type="button"
                    class="btn btn-sm btn-primary"
                    data-bs-toggle="modal"
                    data-bs-target="#modalNuevaCategoria">
                <i class="fas fa-plus"></i>
            </button>
        </label>
        <select name="categoria_id"
                id="categoria_id"
                class="form-select"
                required>
            @foreach($categorias as $categoria)
                <option value="{{ $categoria->id }}"
                    {{ old('categoria_id', $producto->categoria_id) == $categoria->id ? 'selected' : '' }}>
                    {{ $categoria->nombre }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-4">
        <label class="form-label d-flex justify-content-between">
            <span>Marca</span>
            <button type="button"
                    class="btn btn-sm btn-primary"
                    data-bs-toggle="modal"
                    data-bs-target="#modalNuevaMarca">
                <i class="fas fa-plus"></i>
            </button>
        </label>
        <select name="marca_id"
                id="marca_id"
                class="form-select">
            @foreach($marcas as $marca)
                <option value="{{ $marca->id }}"
                    {{ old('marca_id', $producto->marca_id) == $marca->id ? 'selected' : '' }}>
                    {{ $marca->nombre }}
                </option>
            @endforeach
        </select>
    </div>

    <!-- ================= IMAGEN ================= -->
    <div class="col-12">
        <label class="form-label">Imagen</label>
        <input type="file" class="form-control shadow-sm" name="imagen" id="imagen" accept="image/*">
        <small class="text-muted">
            Deja vacío si no deseas cambiarla
        </small>

        @if($producto->imagen)
            <div class="mt-2">
                <img id="preview_imagen"
                    src="{{ $producto->imagen ? asset('uploads/productos/' . $producto->imagen) : '' }}"
                    class="img-thumbnail {{ $producto->imagen ? '' : 'd-none' }}"
                    width="150">
            </div>
        @endif
    </div>

    <!-- ================= ESTADO ================= -->
    <div class="col-md-4">
        <div class="form-check mt-3">
            <input class="form-check-input"
                   type="checkbox"
                   name="activo"
                   {{ old('activo', $producto->activo) ? 'checked' : '' }}>
            <label class="form-check-label">Activo</label>
        </div>

        <div class="form-check mt-2">
            <input class="form-check-input"
                   type="checkbox"
                   name="visible_en_catalogo"
                   {{ old('visible_en_catalogo', $producto->visible_en_catalogo) ? 'checked' : '' }}>
            <label class="form-check-label">Visible en catálogo</label>
        </div>
    </div>

</div>

<div class="text-center mt-4">
    <button type="submit" class="btn btn-success px-5">
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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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
document.addEventListener('DOMContentLoaded', function () {

    /* =====================================================
     * PREVIEW DE IMAGEN
     * ===================================================== */
    const inputImagen = document.getElementById('imagen');
    const previewImg  = document.getElementById('preview_imagen');

    if (inputImagen && previewImg) {
        inputImagen.addEventListener('change', function () {
            const file = this.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = function (e) {
                previewImg.src = e.target.result;
                previewImg.classList.remove('d-none');
            };
            reader.readAsDataURL(file);
        });
    }

    /* =====================================================
     * RECALCULAR STOCK (EDITAR)
     * ===================================================== */

    const cantidadInput = document.querySelector('input[name="cantidad_ingresada"]');
    const radiosIngreso = document.querySelectorAll('input[name="nivel_ingreso"]');

    const unidadesPorPaquete = document.querySelector('input[name="unidades_por_paquete"]');
    const paquetesPorCaja   = document.querySelector('input[name="paquetes_por_caja"]');

    const stockCalculado = document.querySelector('input[name="stock_calculado"]');

    // 👉 PRIMERO obtener el input
    const stockActualInput = document.getElementById('stock_actual');

    // 👉 LUEGO guardar el stock base
    const stockBase = stockActualInput ? parseInt(stockActualInput.value) || 0 : 0;

    function calcularStock() {

        const cantidad = parseInt(cantidadInput.value);
        const stockActualInput = document.getElementById('stock_actual');

        // 🔁 Si no hay ingreso, restaurar stock original
        if (!cantidad || cantidad <= 0) {
            stockCalculado.value = '';
            stockActualInput.value = stockBase;
            return;
        }

        const nivel = document.querySelector('input[name="nivel_ingreso"]:checked')?.value;
        if (!nivel) {
            stockCalculado.value = '';
            stockActualInput.value = stockBase;
            return;
        }

        const up = parseInt(unidadesPorPaquete?.value) || 0;
        const pc = parseInt(paquetesPorCaja?.value) || 0;

        let ingreso = 0;

        // ===== UNIDAD =====
        if (nivel === 'unidad') {
            ingreso = cantidad;
        }

        // ===== PAQUETE =====
        if (nivel === 'paquete') {
            if (up <= 0) return;
            ingreso = cantidad * up;
        }

        // ===== CAJA =====
        if (nivel === 'caja') {
            if (pc > 0 && up > 0) {
                ingreso = cantidad * pc * up;
            } else if (up > 0) {
                ingreso = cantidad * up;
            } else {
                return;
            }
        }

        // 👉 MOSTRAR INGRESO
        stockCalculado.value = ingreso;

        // 👉 MOSTRAR STOCK TOTAL (BASE + INGRESO)
        stockActualInput.value = stockBase + ingreso;
    }  

    // Eventos
    cantidadInput?.addEventListener('input', calcularStock);
    unidadesPorPaquete?.addEventListener('input', calcularStock);
    paquetesPorCaja?.addEventListener('input', calcularStock);

    radiosIngreso.forEach(radio => {
        radio.addEventListener('change', calcularStock);
    });

});
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

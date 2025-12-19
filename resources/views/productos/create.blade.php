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

        {{-- Errores de validación --}}
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('productos.store') }}" method="POST" enctype="multipart/form-data">
@csrf

<div class="row g-3">

    <!-- ================= DATOS BÁSICOS ================= -->

    <div class="col-md-4"> <label class="form-label">Código de Barras</label> 
        <input type="text" id="codigo_barras" name="codigo_barras" class="form-control" value="{{ old('codigo_barras') }}"> 
        <div id="codigo_barras_error" class="invalid-feedback d-none"> Este código ya está registrado. </div> 
    </div>

    <div class="col-md-4">
        <label class="form-label">Nombre</label>
        <input type="text" name="nombre" class="form-control"
               value="{{ old('nombre') }}" required>
    </div>

    <div class="col-md-4">
        <label class="form-label">Precio Compra</label>
        <div class="input-group">
            <span class="input-group-text">S/</span>
            <input type="number" step="0.01" name="precio_compra"
                   class="form-control"
                   value="{{ old('precio_compra') }}" required>
        </div>
    </div>

    <div class="col-12">
        <label class="form-label">Descripción</label>
        <textarea name="descripcion" class="form-control"
                  rows="2">{{ old('descripcion') }}</textarea>
    </div>

    <!-- ================= PRESENTACIONES ================= -->

    <div class="col-12 mt-4">
        <label class="form-label fw-bold text-primary">
            Presentaciones disponibles
        </label>

        <div class="form-check">
            <input class="form-check-input" type="checkbox" checked disabled>
            <label class="form-check-label">
                Unidad (siempre)
            </label>
        </div>

        <div class="form-check">
            <input class="form-check-input" type="checkbox" id="usa_paquete">
            <label class="form-check-label">
                Paquete
            </label>
        </div>

        <div class="form-check">
            <input class="form-check-input" type="checkbox" id="usa_caja">
            <label class="form-check-label">
                Caja
            </label>
        </div>
    </div>

    <!-- ================= CONVERSIONES ================= -->

    <div class="row mt-2">

        <div class="col-md-4 d-none" id="grupo_unidades_base">
            <label class="form-label fw-bold" id="label_unidades_base">
                Unidades
            </label>
            <input type="number" min="1"
                name="unidades_por_paquete"
                id="unidades_base"
                class="form-control"
                value="{{ old('unidades_por_paquete') }}">
        </div>

        <div class="col-md-4 d-none" id="grupo_paquetes_caja">
            <label class="form-label fw-bold">
                Paquetes por caja
            </label>
            <input type="number" min="1"
                   name="paquetes_por_caja"
                   class="form-control"
                   value="{{ old('paquetes_por_caja') }}">
        </div>

    </div>

    <!-- ================= PRECIOS ================= -->

    <div class="row mt-3">

        <div class="col-md-4">
            <label class="form-label">Precio Unidad</label>
            <div class="input-group">
                <span class="input-group-text">S/</span>
                <input type="number" step="0.01"
                       name="precio_venta"
                       class="form-control"
                       value="{{ old('precio_venta') }}" required>
            </div>
        </div>

        <div class="col-md-4 d-none" id="grupo_precio_paquete">
            <label class="form-label">Precio Paquete</label>
            <div class="input-group">
                <span class="input-group-text">S/</span>
                <input type="number" step="0.01"
                       name="precio_paquete"
                       class="form-control"
                       value="{{ old('precio_paquete') }}">
            </div>
        </div>

        <div class="col-md-4 d-none" id="grupo_precio_caja">
            <label class="form-label">Precio Caja</label>
            <div class="input-group">
                <span class="input-group-text">S/</span>
                <input type="number" step="0.01"
                       name="precio_caja"
                       class="form-control"
                       value="{{ old('precio_caja') }}">
            </div>
        </div>

    </div>

    <!-- ================= INGRESO DE STOCK ================= -->

    <div class="col-12 mt-4">
        <label class="form-label fw-bold text-primary">
            ¿Cómo estás ingresando este stock?
        </label>

        <div class="form-check">
            <input class="form-check-input" type="radio"
                   name="nivel_ingreso" value="unidad">
            <label class="form-check-label">Unidades</label>
        </div>

        <div class="form-check d-none" id="ingreso_paquete">
            <input class="form-check-input" type="radio"
                   name="nivel_ingreso" value="paquete">
            <label class="form-check-label">Paquetes</label>
        </div>

        <div class="form-check d-none" id="ingreso_caja">
            <input class="form-check-input" type="radio"
                   name="nivel_ingreso" value="caja">
            <label class="form-check-label">Cajas</label>
        </div>
    </div>

    <!-- ================= CANTIDAD / STOCK ================= -->

    <div class="col-md-4 mt-3">
        <label class="form-label fw-bold">
            Cantidad ingresada
        </label>
        <input type="number" min="1"
               name="cantidad_ingresada"
               class="form-control">
    </div>

    <div class="col-md-4 mt-3">
        <label class="form-label fw-bold">
            Stock final (unidades)
        </label>
        <input type="number" name="stock"
               id="stock"
               class="form-control" readonly>
    </div>

    <!-- ================= UBICACIÓN / FECHA ================= -->

    <div class="col-md-4">
        <label class="form-label">Ubicación</label>
        <input type="text" name="ubicacion"
               class="form-control"
               value="{{ old('ubicacion') }}">
    </div>

    <div class="col-md-4">
        <label class="form-label">Fecha de Vencimiento</label>
        <input type="date" name="fecha_vencimiento"
               class="form-control"
               value="{{ old('fecha_vencimiento') }}">
    </div>

    <!-- ================= CATEGORÍA / MARCA ================= --> 
     <div class="col-md-4"> 
        <label class="form-label d-flex justify-content-between"> <span>Categoría</span> 
            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalNuevaCategoria"><i class="fas fa-plus"></i> </button> 
        </label> 
        <select name="categoria_id" id="categoria_id" class="form-select" required> 
            <option value="">Seleccione</option> @foreach($categorias as $categoria) 
            <option value="{{ $categoria->id }}">{{ $categoria->nombre }}</option>@endforeach 
        </select> 
    </div> 
    <div class="col-md-4"> 
        <label class="form-label d-flex justify-content-between"> <span>Marca</span> 
            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalNuevaMarca"> <i class="fas fa-plus"></i></button> 
        </label> 
        <select name="marca_id" id="marca_id" class="form-select"> 
            <option value="">Seleccione</option> @foreach($marcas as $marca) 
            <option value="{{ $marca->id }}">{{ $marca->nombre }}</option> @endforeach 
        </select> 
    </div>

    <!-- ================= IMAGEN ================= -->

    <div class="col-md-4">
        <label class="form-label">Imagen</label>
        <input type="file" name="imagen" id="imagen" class="form-control" accept="image/*">
    </div>
    <div class="mt-2">
        <img id="preview_imagen"
            src="#"
            class="img-thumbnail d-none"
            style="max-height: 150px;">
    </div>

    <!-- ================= ESTADO ================= -->

    <div class="col-md-4">
        <div class="form-check mt-4">
            <input class="form-check-input" type="checkbox"
                   name="activo" checked>
            <label class="form-check-label">Activo</label>
        </div>

        <div class="form-check mt-2">
            <input class="form-check-input" type="checkbox"
                   name="visible_en_catalogo" checked>
            <label class="form-check-label">Visible en catálogo</label>
        </div>
    </div>

</div>

<div class="mt-4 text-center">
    <button type="submit" class="btn btn-primary px-5">
        Guardar Producto
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
    <script src="{{ asset('js/validarCodigoBarras.js') }}"></script>
    <script src="{{ asset('js/registrar_producto.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    //MOSTRAR IMAGEN
    <script>
        document.getElementById('imagen').addEventListener('change', function (e) {
            const file = e.target.files[0];
            const preview = document.getElementById('preview_imagen');

            if (!file) {
                preview.classList.add('d-none');
                return;
            }

            const reader = new FileReader();
            reader.onload = function (event) {
                preview.src = event.target.result;
                preview.classList.remove('d-none');
            };

            reader.readAsDataURL(file);
        });
    </script>

    //calcularStock
    <script>
        document.addEventListener('DOMContentLoaded', function () {

            const cantidadInput = document.querySelector('input[name="cantidad_ingresada"]');
            const stockInput    = document.getElementById('stock');

            const unidadesPorPaquete = document.querySelector('input[name="unidades_por_paquete"]');
            const paquetesPorCaja   = document.querySelector('input[name="paquetes_por_caja"]');

            const radiosIngreso = document.querySelectorAll('input[name="nivel_ingreso"]');

            const preview       = document.getElementById('preview_stock');
            const textoPreview  = document.getElementById('texto_stock');

            function calcularStock() {

                const cantidadInput = document.querySelector('input[name="cantidad_ingresada"]');
                const stockInput    = document.getElementById('stock');

                const unidadesPorPaquete = document.querySelector('input[name="unidades_por_paquete"]');
                const paquetesPorCaja   = document.querySelector('input[name="paquetes_por_caja"]');

                const preview      = document.getElementById('preview_stock');
                const textoPreview = document.getElementById('texto_stock');

                const cantidad = parseInt(cantidadInput.value) || 0;
                if (cantidad <= 0) {
                    stockInput.value = '';
                    preview.classList.add('d-none');
                    return;
                }

                const nivel = document.querySelector('input[name="nivel_ingreso"]:checked')?.value;
                if (!nivel) return;

                const up = parseInt(unidadesPorPaquete?.value) || 0;
                const pc = parseInt(paquetesPorCaja?.value) || 0;

                let stock = 0;
                let detalle = '';

                // ========= UNIDAD =========
                if (nivel === 'unidad') {
                    stock = cantidad;
                    detalle = `${cantidad} unidades`;
                }

                // ========= PAQUETE =========
                if (nivel === 'paquete') {
                    if (up <= 0) return;
                    stock = cantidad * up;
                    detalle = `${cantidad} paquetes × ${up} unidades`;
                }

                // ========= CAJA =========
                if (nivel === 'caja') {

                    // 👉 CAJA → PAQUETE → UNIDAD
                    if (pc > 0 && up > 0) {
                        stock = cantidad * pc * up;
                        detalle = `${cantidad} cajas × ${pc} paquetes × ${up} unidades`;
                    }

                    // 👉 CAJA → UNIDAD DIRECTO (aceite, vino)
                    else if (up > 0) {
                        stock = cantidad * up;
                        detalle = `${cantidad} cajas × ${up} unidades`;
                    }
                    else {
                        return;
                    }
                }

                stockInput.value = stock;
                textoPreview.textContent = `${detalle} = ${stock} unidades`;
                preview.classList.remove('d-none');
            }

            // ====== EVENTOS ======
            cantidadInput.addEventListener('input', calcularStock);
            unidadesPorPaquete?.addEventListener('input', calcularStock);
            paquetesPorCaja?.addEventListener('input', calcularStock);

            radiosIngreso.forEach(radio => {
                radio.addEventListener('change', calcularStock);
            });

        });
    </script>

    //MOSTRAR OCULTAR UNPUTS    
    <script>
        document.addEventListener('DOMContentLoaded', function () {

            const chkPaquete = document.getElementById('usa_paquete');
            const chkCaja    = document.getElementById('usa_caja');

            const grupoUnidadesBase = document.getElementById('grupo_unidades_base');
            const labelUnidadesBase = document.getElementById('label_unidades_base');

            const grupoPaquetesCaja  = document.getElementById('grupo_paquetes_caja');
            const grupoPrecioPaquete = document.getElementById('grupo_precio_paquete');
            const grupoPrecioCaja    = document.getElementById('grupo_precio_caja');

            const ingresoPaquete = document.getElementById('ingreso_paquete');
            const ingresoCaja    = document.getElementById('ingreso_caja');

            function mostrar(el) {
                el.classList.remove('d-none');
            }

            function ocultar(el) {
                el.classList.add('d-none');
            }

            function actualizarPresentaciones() {

                // ===== UNIDADES BASE (PAQUETE O CAJA) =====
                if (chkPaquete.checked) {
                    mostrar(grupoUnidadesBase);
                    labelUnidadesBase.textContent = 'Unidades por paquete';
                }
                else if (chkCaja.checked) {
                    mostrar(grupoUnidadesBase);
                    labelUnidadesBase.textContent = 'Unidades por caja';
                }
                else {
                    ocultar(grupoUnidadesBase);
                }

                // ===== PRECIO + INGRESO PAQUETE =====
                if (chkPaquete.checked) {
                    mostrar(grupoPrecioPaquete);
                    mostrar(ingresoPaquete);
                } else {
                    ocultar(grupoPrecioPaquete);
                    ocultar(ingresoPaquete);
                }

                // ===== CAJA =====
                if (chkCaja.checked) {
                    mostrar(grupoPrecioCaja);
                    mostrar(ingresoCaja);

                    // Paquetes por caja SOLO si existe paquete
                    if (chkPaquete.checked) {
                        mostrar(grupoPaquetesCaja);
                    } else {
                        ocultar(grupoPaquetesCaja);
                    }

                } else {
                    ocultar(grupoPaquetesCaja);
                    ocultar(grupoPrecioCaja);
                    ocultar(ingresoCaja);
                }
            }

            chkPaquete.addEventListener('change', actualizarPresentaciones);
            chkCaja.addEventListener('change', actualizarPresentaciones);

            actualizarPresentaciones();
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

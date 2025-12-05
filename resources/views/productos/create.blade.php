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

                <!-- Código de Barras -->
                <div class="col-md-4">
                    <label class="form-label">Código de Barras</label>
                    <input type="text" id="codigo_barras" name="codigo_barras" class="form-control"
                           value="{{ old('codigo_barras') }}">
                </div>

                <!-- Nombre -->
                <div class="col-md-4">
                    <label class="form-label">Nombre</label>
                    <input type="text" id="nombre" name="nombre" class="form-control"
                           value="{{ old('nombre') }}" required>
                </div>

                <!-- Precio compra -->
                <div class="col-md-4">
                    <label class="form-label">Precio Compra</label>
                    <div class="input-group">
                        <span class="input-group-text">S/</span>
                        <input type="number" step="0.01" name="precio_compra" class="form-control"
                            value="{{ old('precio_compra') }}" required>
                    </div>
                </div>

                <!-- Descripción -->
                <div class="col-12">
                    <label class="form-label">Descripción</label>
                    <textarea id="descripcion" name="descripcion" class="form-control" rows="2">{{ old('descripcion') }}</textarea>
                </div>

                <!-- Precio venta -->
                <div class="col-md-4">
                    <label class="form-label">Precio Venta</label>
                    <div class="input-group">
                        <span class="input-group-text">S/</span>
                        <input type="number" step="0.01" name="precio_venta" class="form-control"
                            value="{{ old('precio_venta') }}" required>
                    </div>
                </div>

                <!-- PRECIOS Y CANTIDADES DE PAQUETE/CAJA -->
                <div class="col-md-4">
                    <label class="form-label">Precio Paquete</label>
                    <div class="input-group">
                        <span class="input-group-text">S/</span>
                        <input type="number" step="0.01" name="precio_paquete" class="form-control"
                            value="{{ old('precio_paquete') }}">
                    </div>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Unidades por Paquete</label>
                    <input type="number" id="unidades_por_paquete" name="unidades_por_paquete" class="form-control"
                           value="{{ old('unidades_por_paquete') }}">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Paquetes por Caja</label>
                    <input type="number" id="paquetes_por_caja" name="paquetes_por_caja" class="form-control"
                           value="{{ old('paquetes_por_caja') }}">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Cantidad de Cajas (stock inicial)</label>
                    <input type="number" id="cantidad_cajas" name="cantidad_cajas" class="form-control"
                           value="{{ old('cantidad_cajas') }}">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Precio Caja</label>
                    <div class="input-group">
                        <span class="input-group-text">S/</span>
                        <input type="number" step="0.01" name="precio_caja" class="form-control"
                            value="{{ old('precio_caja') }}">
                    </div>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Tipo de Paquete</label>
                    <input type="text" id="tipo_paquete" name="tipo_paquete" class="form-control"
                           value="{{ old('tipo_paquete') }}" placeholder="caja, fardo, paquete...">
                </div>

                <!-- Stock manual (se llena auto con JS, pero se puede editar) -->
                <div class="col-md-4">
                    <label class="form-label">Stock (unidades, opcional)</label>
                    <input type="number" id="stock" name="stock" class="form-control"
                           value="{{ old('stock') }}">
                </div>

                <!-- Ubicación y fecha -->
                <div class="col-md-4">
                    <label class="form-label">Ubicación</label>
                    <input type="text" id="ubicacion" name="ubicacion" class="form-control"
                           value="{{ old('ubicacion') }}">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Fecha de Vencimiento</label>
                    <input type="date" id="fecha_vencimiento" name="fecha_vencimiento" class="form-control"
                           value="{{ old('fecha_vencimiento') }}">
                </div>

                <!-- Categoría / Marca -->
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
                            <option value="{{ $categoria->id }}">{{ $categoria->nombre }}</option>
                        @endforeach
                    </select>
                </div>

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
                            <option value="{{ $marca->id }}">{{ $marca->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Imagen -->
                <div class="col-md-4">
                    <label class="form-label">Imagen</label>
                    <input type="file" id="imagen" name="imagen" class="form-control" accept="image/*">
                </div>

                <!-- Activo / visible -->
                <div class="col-md-4">
                    <div class="form-check mt-4">
                        <input class="form-check-input" type="checkbox" id="activo" name="activo" checked>
                        <label class="form-check-label" for="activo">Activo</label>
                    </div>
                    <div class="form-check mt-2">
                        <input class="form-check-input" type="checkbox" id="visible_en_catalogo" name="visible_en_catalogo" checked>
                        <label class="form-check-label" for="visible_en_catalogo">Visible en Catálogo</label>
                    </div>
                </div>

            </div>

            <div class="mt-4 text-center">
                <button type="submit" class="btn btn-primary">
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

    {{-- Stock dinámico --}}
    <script>
        function calcularStock() {
            const cajas    = parseInt(document.getElementById('cantidad_cajas')?.value) || 0;
            const paquetes = parseInt(document.getElementById('paquetes_por_caja')?.value) || 0;
            const unidades = parseInt(document.getElementById('unidades_por_paquete')?.value) || 0;

            let stock = 0;

            // 1) Caja -> Paquete -> Unidad
            if (cajas && paquetes && unidades) {
                stock = cajas * paquetes * unidades;
            }
            // 2) Caja -> Unidad (vino en cajas de 12)
            else if (cajas && unidades && !paquetes) {
                stock = cajas * unidades;
            }
            // 3) Solo Paquete -> Unidad (fardos sin caja)
            else if (paquetes && unidades && !cajas) {
                stock = paquetes * unidades;
            } else {
                stock = 0;
            }

            const stockInput = document.getElementById('stock');
            if (stockInput) {
                stockInput.value = stock > 0 ? stock : '';
            }
        }

        ['cantidad_cajas', 'paquetes_por_caja', 'unidades_por_paquete'].forEach(id => {
            const el = document.getElementById(id);
            if (el) {
                el.addEventListener('input', calcularStock);
                el.addEventListener('change', calcularStock);
            }
        });
    </script>

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
<script>

// GUARDAR CATEGORÍA
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

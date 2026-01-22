@extends('layouts.app')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
<link href="{{ asset('css/edit_productos.css') }}" rel="stylesheet" />
@endpush

{{-- BOTÓN ATRÁS --}}
@section('header-back')
<button class="btn-header-back"
        type="button"
        onclick="window.location='{{ route('productos.index') }}'">
    <i class="fas fa-chevron-left"></i>
</button>
@endsection

@section('header-title','Editar Producto')

@section('content')
<div class="card shadow-sm mx-auto my-4" style="max-width: 960px;">
    <div class="card-header bg-primary text-white text-center">
        <h4 class="mb-0">
            <i class="fas fa-box"></i> Editar Producto
        </h4>
    </div>

    <div class="card-body">

        {{-- ERRORES --}}
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
              enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="row g-3">

                {{-- ================= DATOS BÁSICOS ================= --}}

                <div class="col-md-4">
                    <label class="form-label">Código de Barras</label>
                    <input type="text"
                           id="codigo_barras"
                           name="codigo_barras"
                           class="form-control"
                           value="{{ old('codigo_barras', $producto->codigo_barras) }}">
                    <div id="codigo_barras_error"
                         class="invalid-feedback d-none">
                        Este código ya está registrado.
                    </div>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Nombre</label>
                    <input type="text"
                           name="nombre"
                           class="form-control"
                           value="{{ old('nombre', $producto->nombre) }}"
                           required>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Slug</label>
                    <input type="text"
                           class="form-control"
                           value="{{ $producto->slug }}"
                           readonly>
                </div>

                <div class="col-12">
                    <label class="form-label">Descripción</label>
                    <textarea name="descripcion"
                              class="form-control"
                              rows="2">{{ old('descripcion', $producto->descripcion) }}</textarea>
                </div>

                {{-- ================= PRESENTACIONES ================= --}}

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
                        <input class="form-check-input"
                               type="checkbox"
                               id="usa_paquete"
                               {{ $producto->unidades_por_paquete ? 'checked' : '' }}>
                        <label class="form-check-label">Paquete</label>
                    </div>

                    <div class="form-check">
                        <input class="form-check-input"
                               type="checkbox"
                               id="usa_caja"
                               {{ ($producto->paquetes_por_caja || $producto->unidades_por_caja) ? 'checked' : '' }}>
                        <label class="form-check-label">Caja</label>
                    </div>
                </div>

                {{-- ================= CONVERSIONES ================= --}}

                <div class="row mt-2">

                    <div class="col-md-4 d-none" id="grupo_unidades_paquete">
                        <label class="form-label fw-bold">
                            Unidades por paquete
                        </label>
                        <input type="number"
                               min="1"
                               name="unidades_por_paquete"
                               class="form-control"
                               value="{{ old('unidades_por_paquete', $producto->unidades_por_paquete) }}">
                    </div>

                    <div class="col-md-4 d-none" id="grupo_paquetes_caja">
                        <label class="form-label fw-bold">
                            Paquetes por caja
                        </label>
                        <input type="number"
                               min="1"
                               name="paquetes_por_caja"
                               class="form-control"
                               value="{{ old('paquetes_por_caja', $producto->paquetes_por_caja) }}">
                    </div>

                    <div class="col-md-4 d-none" id="grupo_unidades_caja">
                        <label class="form-label fw-bold">
                            Unidades por caja
                        </label>
                        <input type="number"
                               min="1"
                               name="unidades_por_caja"
                               class="form-control"
                               value="{{ old('unidades_por_caja', $producto->unidades_por_caja) }}">
                    </div>

                </div>

                {{-- ================= UBICACIÓN ================= --}}

                <div class="col-md-4">
                    <label class="form-label">Ubicación</label>
                    <input type="text"
                           name="ubicacion"
                           class="form-control"
                           value="{{ old('ubicacion', $producto->ubicacion) }}">
                </div>

                {{-- ================= CATEGORÍA / MARCA ================= --}}

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

                {{-- ================= IMAGEN ================= --}}

                <div class="col-12">
                    <label class="form-label">Imagen</label>
                    <input type="file"
                           name="imagen"
                           id="imagen"
                           class="form-control"
                           accept="image/*">

                    <div class="mt-2">
                        <img id="preview_imagen"
                             src="{{ $producto->imagen ? asset('uploads/productos/'.$producto->imagen) : '' }}"
                             class="img-thumbnail {{ $producto->imagen ? '' : 'd-none' }}"
                             style="max-height:150px;">
                    </div>
                </div>

                {{-- ================= ESTADO ================= --}}

                <div class="col-md-4">

                    {{-- VENCIMIENTO --}}
                    <div class="form-check">
                        <input class="form-check-input"
                            type="checkbox"
                            id="maneja_vencimiento"
                            name="maneja_vencimiento"
                            {{ old('maneja_vencimiento', $producto->maneja_vencimiento) ? 'checked' : '' }}>
                        <label class="form-check-label" for="maneja_vencimiento">
                                Maneja fecha de vencimiento
                        </label>
                    </div>
                    
                    <div class="form-check mt-4">
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

            <div class="mt-4 text-center">
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
                <button type="button" class="btn btn-success" id="btnGuardarCategoria">Guardar</button>
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
                <button type="button" class="btn btn-success" id="btnGuardarMarca">Guardar</button>
            </div>

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
/* ==========================
   VALIDAR CÓDIGO DE BARRAS
========================== */
document.addEventListener("DOMContentLoaded", () => {
    const input = document.getElementById("codigo_barras");
    const errorDiv = document.getElementById("codigo_barras_error");
    const productoId = document.getElementById("producto_id")?.value;

    if (!input || !errorDiv) return;

    let timer;

    input.addEventListener("input", () => {
        const codigo = input.value.trim();
        clearTimeout(timer);

        if (!codigo) {
            input.classList.remove("is-invalid");
            errorDiv.classList.add("d-none");
            return;
        }

        timer = setTimeout(() => {
            fetch(`{{ route('productos.validarCodigoBarras') }}?codigo_barras=${codigo}&producto_id=${productoId}`)
                .then(res => res.json())
                .then(data => {
                    if (data.exists) {
                        input.classList.add("is-invalid");
                        errorDiv.classList.remove("d-none");
                    } else {
                        input.classList.remove("is-invalid");
                        errorDiv.classList.add("d-none");
                    }
                });
        }, 300);
    });
});
</script>

<script>
/* ==========================
   MOSTRAR / OCULTAR INPUTS
========================== */
document.addEventListener('DOMContentLoaded', function () {

    const chkPaquete = document.getElementById('usa_paquete');
    const chkCaja    = document.getElementById('usa_caja');

    const grupoUP = document.getElementById('grupo_unidades_paquete');
    const grupoPC = document.getElementById('grupo_paquetes_caja');
    const grupoUC = document.getElementById('grupo_unidades_caja');

    function actualizar() {

        // PAQUETE
        if (chkPaquete.checked) {
            grupoUP.classList.remove('d-none');
        } else {
            grupoUP.classList.add('d-none');
            grupoPC.classList.add('d-none');
        }

        // CAJA
        if (chkCaja.checked) {

            if (chkPaquete.checked) {
                // caja por paquetes
                grupoPC.classList.remove('d-none');
                grupoUC.classList.add('d-none');
            } else {
                // caja directa
                grupoUC.classList.remove('d-none');
                grupoPC.classList.add('d-none');
            }

        } else {
            grupoPC.classList.add('d-none');
            grupoUC.classList.add('d-none');
        }
    }

    chkPaquete.addEventListener('change', actualizar);
    chkCaja.addEventListener('change', actualizar);

    actualizar(); // init
});
</script>

<script>
/* ==========================
   PREVIEW IMAGEN
========================== */
document.getElementById('imagen')?.addEventListener('change', function () {
    const file = this.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = e => {
        const img = document.getElementById('preview_imagen');
        img.src = e.target.result;
        img.classList.remove('d-none');
    };
    reader.readAsDataURL(file);
});
</script>

<script>
    $(document).on('click', '#btnGuardarCategoria', function () {

    let nombre = $("#nueva_categoria_nombre").val().trim();

    if (!nombre) {
        $("#error_categoria").text("El nombre es obligatorio.").removeClass("d-none");
        return;
    }

    $.post("{{ route('categoria.ajax.store') }}", {
        nombre: nombre,
        _token: '{{ csrf_token() }}'
    }, function (response) {

        if (response.error) {
            $("#error_categoria").text(response.message).removeClass("d-none");
            return;
        }

        $("#error_categoria").addClass("d-none").text("");

        // 👇 CLAVE: agregar y seleccionar
        $("#categoria_id").append(
            new Option(response.data.nombre, response.data.id, true, true)
        );

        $("#modalNuevaCategoria").modal("hide");
        $("#nueva_categoria_nombre").val("");

        Swal.fire("Éxito", "Categoría registrada correctamente.", "success");
    });
});

$(document).on('click', '#btnGuardarMarca', function () {

    let nombre = $("#nueva_marca_nombre").val().trim();

    if (!nombre) {
        $("#error_marca").text("El nombre es obligatorio.").removeClass("d-none");
        return;
    }

    $.post("{{ route('marca.ajax.store') }}", {
        nombre: nombre,
        _token: '{{ csrf_token() }}'
    }, function (response) {

        if (response.error) {
            $("#error_marca").text(response.message).removeClass("d-none");
            return;
        }

        $("#error_marca").addClass("d-none").text("");

        $("#marca_id").append(
            new Option(response.data.nombre, response.data.id, true, true)
        );

        $("#modalNuevaMarca").modal("hide");
        $("#nueva_marca_nombre").val("");

        Swal.fire("Éxito", "Marca registrada correctamente.", "success");
    });
});

    </script>
@endpush

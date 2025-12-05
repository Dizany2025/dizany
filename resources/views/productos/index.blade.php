@extends('layouts.app')

@push('styles')
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <!-- CSS personalizado para productos -->
    
@endpush

@section('header-actions')
<div class="d-flex align-items-center gap-3">
    <a href="{{ route('productos.create') }}" class="nuevo-producto">
        <i class="fa-solid fa-plus"></i> Nuevo Producto
    </a>
</div>
@endsection

@section('content')
<link href="{{ asset('css/mostrar_detalles_productos.css') }}" rel="stylesheet" />
<div class="card mx-auto my-4" style="max-width: 1000px;">
    <div class="card-header text-center bg-primary text-white">
        <h4 class="mb-0"><i class="fa-solid fa-receipt"></i> Lista de Productos</h4>
    </div>
    <div class="card-body">

        <!-- Filtro y buscador -->
        <form method="GET" action="{{ route('productos.index') }}" class="row gy-2 gx-2 align-items-center mb-3">
            <div class="col-md-3">
                <select name="categoria_id" class="form-select">
                    <option value="todos">- Todas las Categorías -</option>
                    @foreach($categorias as $categoria)
                        <option value="{{ $categoria->id }}" {{ request('categoria_id') == $categoria->id ? 'selected' : '' }}>{{ $categoria->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="marca_id" class="form-select">
                    <option value="todos">- Todas las Marcas -</option>
                    @foreach($marcas as $marca)
                        <option value="{{ $marca->id }}" {{ request('marca_id') == $marca->id ? 'selected' : '' }}>{{ $marca->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <input type="search" name="search" class="form-control" placeholder="Buscar código / nombre..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2 d-flex justify-content-end">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="fas fa-search"></i>
                </button>
                <a href="{{ route('productos.export') }}" class="btn btn-success">
                    <i class="fa-solid fa-file-excel"></i>
                </a>
            </div>
        </form>

        <!-- Tabla de productos -->
        <div class="table-responsive">
            <table class="table table-hover align-middle text-center table-bordered mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Imagen</th>
                        <th>Código de Barras</th>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th>Precio Venta</th>
                        <th>Stock</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($productos as $producto)
                        <tr>
                            <td>
                                @if($producto->imagen)
                                    <img src="{{ asset('uploads/productos/' . $producto->imagen) }}" 
                                        alt="Imagen actual" 
                                        class="img-thumbnail" 
                                        style="width: 80px; height: 80px; object-fit: contain; background-color: #f8f9fa;">
                                @endif

                            </td>
                            <td>{{ $producto->codigo_barras }}</td>
                            <td>{{ $producto->nombre }}</td>
                            <td>{{ $producto->descripcion }}</td>
                            <td class="text-end">{{ number_format($producto->precio_venta, 2) }}</td>
                            <td>
                                <span class="fw-bold">{{ $producto->stock }}</span>
                                @if($producto->stock <= 5)
                                    <span class="badge bg-danger ms-1">Stock bajo</span>
                                @elseif($producto->stock <= 10)
                                    <span class="badge bg-warning text-dark ms-1">Poco stock</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex justify-content-center gap-2">
                                    <a href="{{ route('productos.edit', $producto->id) }}" class="btn btn-warning btn-sm">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>

                                    <!-- Botón de Activar/Desactivar -->
                                    <form action="{{ route('productos.toggleEstado', $producto->id) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        @if($producto->activo)
                                            <button type="submit" class="btn btn-success btn-sm" title="Activo: clic para desactivar">
                                                <i class="fas fa-toggle-on"></i>
                                            </button>
                                        @else
                                            <button type="submit" class="btn btn-danger btn-sm" title="Inactivo: clic para activar">
                                                <i class="fas fa-toggle-off"></i>
                                            </button>
                                        @endif
                                    </form>
                                     <!-- Coloca este código dentro de tu tabla de productos, en la columna de acciones -->
                                    
                                        <div class="d-flex justify-content-center gap-2">
                                            <a href="javascript:void(0);" class="btn btn-info btn-sm ver-detalles" data-id="{{ $producto->id }}">
                                                <i class="fa-solid fa-eye"></i>
                                            </a>
                                        </div>
                                    
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">No se encontraron productos.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <!-- Paginación -->
        <div class="d-flex justify-content-center mt-3">
            {{ $productos->links('pagination::simple-bootstrap-4') }}
        </div>

    </div>
</div>


<!-- Modal para ver detalles del producto -->
<div class="modal fade" id="productoModal" tabindex="-1" aria-labelledby="productoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">

            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="productoModalLabel">Detalles del Producto</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <div class="row g-3">

                    <!-- 🟦 COLUMNA 1: Información general -->
                    <div class="col-md-4">
                        <h6 class="fw-bold border-bottom pb-1">Información General</h6>

                        <div class="mb-2">
                            <label class="form-label">ID</label>
                            <input type="text" id="modalId" class="form-control" disabled>
                        </div>

                        <div class="mb-2">
                            <label class="form-label">Código de Barras</label>
                            <input type="text" id="modalCodigo" class="form-control" disabled>
                        </div>

                        <div class="mb-2">
                            <label class="form-label">Nombre</label>
                            <input type="text" id="modalNombre" class="form-control" disabled>
                        </div>

                        <div class="mb-2">
                            <label class="form-label">Slug</label>
                            <input type="text" id="modalSlug" class="form-control" disabled>
                        </div>

                        <div class="mb-2">
                            <label class="form-label">Descripción</label>
                            <textarea id="modalDescripcion" class="form-control" rows="3" disabled></textarea>
                        </div>

                        <div class="mb-2">
                            <label class="form-label">Activo</label>
                            <input type="text" id="modalActivo" class="form-control" disabled>
                        </div>

                        <div class="mb-2">
                            <label class="form-label">Visible en Catálogo</label>
                            <input type="text" id="modalVisibleCatalogo" class="form-control" disabled>
                        </div>
                    </div>

                    <!-- 🟩 COLUMNA 2: Precios y empaques -->
                    <div class="col-md-4">
                        <h6 class="fw-bold border-bottom pb-1">Precios y Empaques</h6>

                        <div class="mb-2">
                            <label class="form-label">Precio Compra</label>
                            <input type="text" id="modalPrecioCompra" class="form-control" disabled>
                        </div>

                        <div class="mb-2">
                            <label class="form-label">Precio Venta</label>
                            <input type="text" id="modalPrecioVenta" class="form-control" disabled>
                        </div>

                        

                        <div class="mb-2">
                            <label class="form-label">Precio Paquete</label>
                            <input type="text" id="modalPrecioPaquete" class="form-control" disabled>
                        </div>

                        <div class="mb-2">
                            <label class="form-label">Unidades por Paquete</label>
                            <input type="text" id="modalUnidadesPorPaquete" class="form-control" disabled>
                        </div>

                        <div class="mb-2">
                            <label class="form-label">Paquetes por Caja</label>
                            <input type="text" id="modalPaquetesPorCaja" class="form-control" disabled>
                        </div>

                        <div class="mb-2">
                            <label class="form-label">Precio Caja</label>
                            <input type="text" id="modalPrecioCaja" class="form-control" disabled>
                        </div>

                        <div class="mb-2">
                            <label class="form-label">Tipo de Paquete</label>
                            <input type="text" id="modalTipoPaquete" class="form-control" disabled>
                        </div>
                    </div>

                    <!-- 🟧 COLUMNA 3: Stock, Ubicación, Imagen -->
                    <div class="col-md-4">
                        <h6 class="fw-bold border-bottom pb-1">Inventario</h6>

                        <div class="mb-2">
                            <label class="form-label">Stock Total</label>
                            <input type="text" id="modalStock" class="form-control" disabled>
                        </div>

                        <div class="mb-2">
                            <label class="form-label">Ubicación</label>
                            <input type="text" id="modalUbicacion" class="form-control" disabled>
                        </div>

                        <div class="mb-2">
                            <label class="form-label">Fecha de Vencimiento</label>
                            <input type="text" id="modalFechaVencimiento" class="form-control" disabled>
                        </div>

                        <div class="mb-2">
                            <label class="form-label">Categoría</label>
                            <input type="text" id="modalCategoria" class="form-control" disabled>
                        </div>

                        <div class="mb-2">
                            <label class="form-label">Marca</label>
                            <input type="text" id="modalMarca" class="form-control" disabled>
                        </div>

                        <div class="mt-3 text-center">
                            <img id="modalImagen" class="img-thumbnail border"
                                 style="width: 130px; height: 130px; object-fit: contain;">
                        </div>
                    </div>

                </div>
            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>

        </div>
    </div>
</div>


@endsection

@push('scripts')
@if(session('estado_actualizado'))
<script>
    Swal.fire({
        icon: 'success',
        title: '¡Producto {{ session('estado_actualizado') }}!',
        text: 'El estado del producto fue actualizado correctamente.',
        timer: 2000,
        showConfirmButton: false
    });
</script>
@endif

<script>
    
    function confirmarCambioEstado(id, activar) {
        Swal.fire({
            title: activar ? '¿Activar producto?' : '¿Desactivar producto?',
            text: activar
                ? 'Este producto estará disponible nuevamente para ventas.'
                : 'Este producto ya no se mostrará en el sistema.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: activar ? 'Sí, activar' : 'Sí, desactivar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('form-estado-' + id).submit();
            }
        });
    }
</script>

<script>
        function formatFechaLarga(fecha) {
        if (!fecha) return "---";

        const meses = [
            "Ene", "Feb", "Mar", "Abr", "May", "Jun",
            "Jul", "Ago", "Sep", "Oct", "Nov", "Dic"
        ];

        const d = new Date(fecha);
        const dia = d.getDate().toString().padStart(2, "0");
        const mes = meses[d.getMonth()];
        const año = d.getFullYear();

        return `${dia} ${mes} ${año}`;
    }
$(document).on('click', '.ver-detalles', function() {
    const productoId = $(this).data('id');

    $.ajax({
        url: `/producto/detalles/${productoId}`,
        method: 'GET',
        success: function(response) {

            if (response.success) {

                // Helpers
                const money = v => formatMoney(v);
                const num   = v => formatNumber(v);

                // === Columna 1 ===
                $('#modalId').val(response.id);
                $('#modalCodigo').val(response.codigo_barras);
                $('#modalNombre').val(response.nombre);
                $('#modalSlug').val(response.slug);
                $('#modalDescripcion').val(response.descripcion);

                $('#modalActivo').val(response.activo ? "Sí" : "No");
                $('#modalVisibleCatalogo').val(response.visible_en_catalogo ? "Sí" : "No");

                // === Columna 2 ===
                $('#modalPrecioCompra').val(money(response.precio_compra));
                $('#modalPrecioVenta').val(money(response.precio_venta));

                $('#modalPrecioPaquete').val(money(response.precio_paquete));
                $('#modalUnidadesPorPaquete').val(num(response.unidades_por_paquete));
                $('#modalPaquetesPorCaja').val(num(response.paquetes_por_caja));
                $('#modalPrecioCaja').val(money(response.precio_caja));
                $('#modalTipoPaquete').val(response.tipo_paquete);

                // === Columna 3 ===
                $('#modalStock').val(num(response.stock));
                $('#modalUbicacion').val(response.ubicacion);
                // === Fecha de vencimiento ===
                $('#modalFechaVencimiento').val(
                    formatFechaLarga(response.fecha_vencimiento));
                $('#modalCategoria').val(response.categoria_nombre);
                $('#modalMarca').val(response.marca_nombre);

                // Imagen
                $('#modalImagen').attr(
                    'src',
                    response.imagen ? `/uploads/productos/${response.imagen}` : '/img/sin-imagen.png'
                );

                // Mostrar modal
                new bootstrap.Modal(document.getElementById('productoModal')).show();
            }
        }
    });
});

// FORMATO MONEY
function formatMoney(value) {
    if (!value) return "S/. 0.00";
    return "S/. " + parseFloat(value).toFixed(2);
}

// FORMATO NUMERICO
function formatNumber(value) {
    if (!value) return "0";
    return new Intl.NumberFormat('es-PE').format(value);
}
</script>


@endpush

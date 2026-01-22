@extends('layouts.app')

@push('styles')
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <!-- CSS personalizado para productos -->
    
@endpush

{{-- Botón atrás (opcional) --}}
@section('header-back')
<button class="btn-header-back" onclick="history.back()">
    <i class="fas fa-arrow-left"></i>
</button>
@endsection

{{-- TÍTULO --}}
@section('header-title')
Productos
@endsection

{{-- BOTONES DERECHA --}}
@section('header-buttons')
<a href="{{ route('productos.create') }}" class="btn-gasto">
    <i class="fa-solid fa-plus"></i>
    <span class="btn-text">Nuevo producto</span>
</a>
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
                            <td class="text-end">{{ number_format($producto->precio_venta_actual, 2) }}</td>
                            <td>
                                <span class="fw-bold">{{ $producto->stock_total }}</span>
                                @if($producto->stock_total <= 5)
                                    <span class="badge bg-danger ms-1">Stock bajo</span>
                                @elseif($producto->stock_total <= 10)
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
<div class="modal fade" id="productoModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">

            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Detalles del Producto</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div class="row g-3">

                    <!-- 🟦 INFO GENERAL -->
                    <div class="col-md-4">
                        <h6 class="fw-bold border-bottom pb-1">Información General</h6>

                        <label>ID</label>
                        <input id="modalId" class="form-control mb-2" disabled>

                        <label>Código de Barras</label>
                        <input id="modalCodigo" class="form-control mb-2" disabled>

                        <label>Nombre</label>
                        <input id="modalNombre" class="form-control mb-2" disabled>

                        <label>Descripción</label>
                        <textarea id="modalDescripcion" class="form-control mb-2" rows="3" disabled></textarea>

                        <label>Categoría</label>
                        <input id="modalCategoria" class="form-control mb-2" disabled>

                        <label>Marca</label>
                        <input id="modalMarca" class="form-control mb-2" disabled>

                        <label>Ubicación</label>
                        <input id="modalUbicacion" class="form-control mb-2" disabled>

                        <label>Activo</label>
                        <input id="modalActivo" class="form-control mb-2" disabled>

                        <label>Visible en catálogo</label>
                        <input id="modalVisibleCatalogo" class="form-control mb-2" disabled>
                    </div>

                    <!-- 🟩 PRESENTACIONES -->
                    <div class="col-md-4">
                        <h6 class="fw-bold border-bottom pb-1">Presentaciones</h6>

                        <label>Unidades por paquete</label>
                        <input id="modalUnidadesPorPaquete" class="form-control mb-2" disabled>

                        <label>Paquetes por caja</label>
                        <input id="modalPaquetesPorCaja" class="form-control mb-2" disabled>

                        <label>Unidades por caja</label>
                        <input id="modalUnidadesPorCaja" class="form-control mb-2" disabled>

                        <label>Maneja fecha de vencimiento</label>
                        <input id="modalManejaVencimiento" class="form-control mb-2" disabled>
                    </div>

                    <!-- 🟧 RESUMEN INVENTARIO (DESDE LOTES) -->
                    <div class="col-md-4">
                        <h6 class="fw-bold border-bottom pb-1">Inventario (Resumen)</h6>

                        <label>Stock total actual</label>
                        <input id="modalStockTotal" class="form-control mb-2" disabled>

                        <label>Lotes activos</label>
                        <input id="modalCantidadLotes" class="form-control mb-2" disabled>

                        <div class="text-center mt-3">
                            <img id="modalImagen"
                                 class="img-thumbnail"
                                 style="width:130px;height:130px;object-fit:contain;">
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
function formatNumber(value) {
    if (!value || value <= 0) return "0";
    return new Intl.NumberFormat('es-PE').format(value);
}

$(document).on('click', '.ver-detalles', function () {

    const productoId = $(this).data('id');

    $.get(`/producto/detalles/${productoId}`, function (r) {

        if (!r.success) return;

        /* =====================
           INFO GENERAL
        ===================== */
        $('#modalId').val(r.id);
        $('#modalCodigo').val(r.codigo_barras ?? '-');
        $('#modalNombre').val(r.nombre);
        $('#modalDescripcion').val(r.descripcion ?? '-');
        $('#modalCategoria').val(r.categoria_nombre ?? '-');
        $('#modalMarca').val(r.marca_nombre ?? '-');
        $('#modalUbicacion').val(r.ubicacion ?? '-');

        $('#modalActivo').val(r.activo ? 'Sí' : 'No');
        $('#modalVisibleCatalogo').val(r.visible_en_catalogo ? 'Sí' : 'No');

        /* =====================
           PRESENTACIONES
        ===================== */
        $('#modalUnidadesPorPaquete').val(
            r.unidades_por_paquete ? formatNumber(r.unidades_por_paquete) : '-'
        );

        $('#modalPaquetesPorCaja').val(
            r.paquetes_por_caja ? formatNumber(r.paquetes_por_caja) : '-'
        );

        $('#modalUnidadesPorCaja').val(
            r.unidades_por_caja ? formatNumber(r.unidades_por_caja) : '-'
        );

        $('#modalManejaVencimiento').val(
            r.maneja_vencimiento ? 'Sí' : 'No'
        );

        /* =====================
           INVENTARIO (RESUMEN)
        ===================== */
        $('#modalStockTotal').val(formatNumber(r.stock_total));
        $('#modalCantidadLotes').val(formatNumber(r.lotes_activos));

        /* =====================
           IMAGEN
        ===================== */
        $('#modalImagen').attr(
            'src',
            r.imagen
                ? `/uploads/productos/${r.imagen}`
                : '/img/sin-imagen.png'
        );

        /* =====================
           MOSTRAR MODAL
        ===================== */
        new bootstrap.Modal(document.getElementById('productoModal')).show();
    });
});
</script>

@endpush

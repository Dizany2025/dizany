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
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="productoModalLabel">Detalles del Producto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="row">
                        <!-- Columna 1 -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="modalId" class="form-label">ID Producto</label>
                                <input type="text" class="form-control" id="modalId" disabled>
                            </div>

                            <div class="mb-3">
                                <label for="modalCodigo" class="form-label">Código de Barras</label>
                                <input type="text" class="form-control" id="modalCodigo" disabled>
                            </div>

                            <div class="mb-3">
                                <label for="modalNombre" class="form-label">Nombre</label>
                                <input type="text" class="form-control" id="modalNombre" disabled>
                            </div>

                            <div class="mb-3">
                                <label for="modalDescripcion" class="form-label">Descripción</label>
                                <textarea class="form-control" id="modalDescripcion" rows="3" disabled></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="modalPrecioCompra" class="form-label">Precio de Compra</label>
                                <input type="text" class="form-control" id="modalPrecioCompra" disabled>
                            </div>

                            <div class="mb-3">
                                <label for="modalPrecioVenta" class="form-label">Precio de Venta</label>
                                <input type="text" class="form-control" id="modalPrecioVenta" disabled>
                            </div>
                        </div>

                        <!-- Columna 2 -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="modalPrecioMayor" class="form-label">Precio al por Mayor</label>
                                <input type="text" class="form-control" id="modalPrecioMayor" disabled>
                            </div>

                            <div class="mb-3">
                                <label for="modalUnidadesPorMayor" class="form-label">Unidades por Mayor</label>
                                <input type="number" class="form-control" id="modalUnidadesPorMayor" disabled>
                            </div>

                            <div class="mb-3">
                                <label for="modalStock" class="form-label">Stock</label>
                                <input type="number" class="form-control" id="modalStock" disabled>
                            </div>

                            <div class="mb-3">
                                <label for="modalUbicacion" class="form-label">Ubicación</label>
                                <input type="text" class="form-control" id="modalUbicacion" disabled>
                            </div>

                            <div class="mb-3">
                                <label for="modalImagen" class="form-label">Imagen</label>
                                <img id="modalImagen" src="" alt="Imagen Producto" class="img-thumbnail" style="width: 100px; height: 100px; object-fit: contain;">
                            </div>

                            <div class="mb-3">
                                <label for="modalFechaVencimiento" class="form-label">Fecha de Vencimiento</label>
                                <input type="text" class="form-control" id="modalFechaVencimiento" disabled>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="modalCategoria" class="form-label">Categoría</label>
                                <input type="text" class="form-control" id="modalCategoria" disabled>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="modalMarca" class="form-label">Marca</label>
                                <input type="text" class="form-control" id="modalMarca" disabled>
                            </div>
                        </div>
                    
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="modalActivo" class="form-label">Activo</label>
                                <input type="text" class="form-control" id="modalActivo" disabled>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
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
$(document).on('click', '.ver-detalles', function() {
    const productoId = $(this).data('id');  // Obtener el ID del producto

    // Hacer la solicitud AJAX para obtener los detalles del producto
    $.ajax({
        url: `/producto/detalles/${productoId}`,  // Ruta para obtener los detalles del producto
        method: 'GET',
            success: function(response) {
            console.log(response);  // Verifica los datos recibidos
            if (response.success) {
            $('#modalId').val(response.id);
            $('#modalCodigo').val(response.codigo_barras);
            $('#modalNombre').val(response.nombre);
            $('#modalDescripcion').val(response.descripcion);
            $('#modalPrecioCompra').val(response.precio_compra);
            $('#modalPrecioVenta').val(response.precio_venta);
            $('#modalPrecioMayor').val(response.precio_mayor);
            $('#modalUnidadesPorMayor').val(response.unidades_por_mayor);
            $('#modalStock').val(response.stock);
            $('#modalUbicacion').val(response.ubicacion);

            const imagenSrc = response.imagen 
                ? `/uploads/productos/${response.imagen}` 
                : '/img/sin-imagen.png';

            $('#modalImagen').attr('src', imagenSrc);
            
            $('#modalFechaVencimiento').val(response.fecha_vencimiento);
            $('#modalCategoria').val(response.categoria_nombre);
            $('#modalMarca').val(response.marca_nombre);
            $('#modalActivo').val(response.activo ? 'Sí' : 'No');

            const modal = new bootstrap.Modal(document.getElementById('productoModal'));
            modal.show();
            } else {
                console.error('Error al cargar los detalles:', response.message); // Ver si hay algún error
            }
        }
    });
});

</script>
@endpush

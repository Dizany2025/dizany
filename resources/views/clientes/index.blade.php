@extends('layouts.app')

@section('content')
<div class="card mx-auto my-4" style="max-width: 900px;">
    <div class="card-header text-center bg-primary text-white">
        <h4 class="mb-0"><i class="fas fa-user-friends"></i> Lista de Clientes</h4>
    </div>
    <div class="card-body">
        <!-- Formulario de Búsqueda con ícono de lupa -->
        <div class="input-group mb-4" style="max-width: 300px; margin: 0 auto;">
            <span class="input-group-text"><i class="fas fa-search"></i></span>
            <input type="text" name="search" id="search" class="form-control" placeholder="Buscar" value="{{ request()->query('search') }}" style="height: 35px;">
        </div>

        <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
            <div id="table-content">
                <!-- Aquí se cargará la tabla de clientes a través de AJAX -->
                <table class="table table-sm table-bordered table-hover mb-0 text-nowrap">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Dirección</th>
                            <th>Teléfono</th>
                            <th>RUC</th>
                            <th>DNI</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($clientes as $cliente)
                            <tr>
                                <td>{{ $cliente->id }}</td>
                                <td>{{ $cliente->nombre }}</td>
                                <td>{{ $cliente->direccion ?? 'No disponible' }}</td>
                                <td>{{ $cliente->telefono ?? 'No disponible' }}</td>
                                <td>{{ $cliente->ruc ?? 'No disponible' }}</td>
                                <td>{{ $cliente->dni ?? 'No disponible' }}</td>
                                <td>
                                    <div class="d-flex justify-content-center gap-1">
                                    <a href="javascript:void(0);" class="btn btn-warning btn-edit" data-id="{{ $cliente->id }}"><i class="fa fa-edit"></i></a>
                                    </div>                
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <!-- Agregar la paginación aquí -->
                <div class="d-flex justify-content-center mt-3">
                    {{ $clientes->appends(['search' => request()->query('search')])->links() }}
                </div>
            </div>    
        </div>       
    </div>       
</div>
<!-- Modal de Edición de Cliente -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">Editar Cliente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editClientForm">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="client_id" name="client_id">
                    <div class="mb-3">
                        <label for="client_name" class="form-label">Nombre</label>
                        <input type="text" class="form-control" id="client_name" name="client_name">
                    </div>
                    <div class="mb-3">
                        <label for="client_address" class="form-label">Dirección</label>
                        <input type="text" class="form-control" id="client_address" name="client_address">
                    </div>
                    <div class="mb-3">
                        <label for="client_phone" class="form-label">Teléfono</label>
                        <input type="text" class="form-control" id="client_phone" name="client_phone">
                    </div>
                    <div class="mb-3">
                        <label for="client_ruc" class="form-label">RUC</label>
                        <input type="text" class="form-control" id="client_ruc" name="client_ruc">
                    </div>
                    <div class="mb-3">
                        <label for="client_dni" class="form-label">DNI</label>
                        <input type="text" class="form-control" id="client_dni" name="client_dni">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    // Detectar cambios en el campo de búsqueda
    $('#search').on('keyup', function() {
        var query = $(this).val();

        // Realizar la solicitud AJAX
        $.ajax({
            url: '{{ route('clientes.index') }}',
            method: 'GET',
            data: { search: query },
            success: function(response) {
                // Actualizar solo el contenido de la tabla con los nuevos resultados
                $('#table-content').html($(response).find('#table-content').html());
            }
        });
    });
});
</script>
<script>
    $(document).ready(function() {
    // Detectar el clic en el botón "Editar"
    $(document).on('click', '.btn-edit', function() {
        var clientId = $(this).data('id'); // Obtener el ID del cliente desde el atributo data-id
        
        // Realizar una solicitud AJAX para obtener los datos del cliente
        $.ajax({
            url: '/clientes/' + clientId + '/edit', // Ruta para obtener los datos del cliente
            method: 'GET',
            success: function(response) {
                // Rellenar los campos del modal con los datos del cliente
                $('#client_id').val(response.id);
                $('#client_name').val(response.nombre);
                $('#client_address').val(response.direccion);
                $('#client_phone').val(response.telefono);
                $('#client_ruc').val(response.ruc);
                $('#client_dni').val(response.dni);

                // Mostrar el modal con los datos del cliente
                $('#editModal').modal('show');
            },
            error: function() {
                alert("Error al obtener los datos del cliente.");
            }
        });
    });

    // Enviar el formulario de edición con AJAX para actualizar los datos
    $('#editClientForm').on('submit', function(e) {
        e.preventDefault(); // Prevenir que el formulario se envíe de forma tradicional

        var clientId = $('#client_id').val(); // Obtener el ID del cliente
        var formData = $(this).serialize(); // Obtener todos los datos del formulario

        // Realizar la solicitud AJAX para actualizar los datos
        $.ajax({
            url: '/clientes/' + clientId, // Ruta para actualizar los datos del cliente
            method: 'PUT',
            data: formData, // Enviar los datos del formulario
            success: function(response) {
                // Si la respuesta es exitosa, mostrar un SweetAlert de éxito
                Swal.fire({
                    title: '¡Éxito!',
                    text: 'Cliente actualizado correctamente',
                    icon: 'success',
                    confirmButtonText: 'Aceptar'
                }).then((result) => {
                    // Después de cerrar el SweetAlert, cerrar el modal y recargar la página
                    if (result.isConfirmed) {
                        $('#editModal').modal('hide');
                        location.reload(); // Recargar la página o la tabla de clientes
                    }
                });
            },
            error: function(xhr, status, error) {
                alert('Error al actualizar el cliente. Inténtalo nuevamente.');
            }
        });
    });
});

</script>
@endpush

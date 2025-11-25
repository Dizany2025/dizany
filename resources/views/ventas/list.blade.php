@extends('layouts.app')

@push('styles')
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.css"> 
    <!-- CSS personalizado para productos -->  

       
@endpush
@section('header-actions')
<div class="d-flex align-items-center gap-3 p-3">
    <a href="{{ route('ventas.index') }}" class="atras">
        <i class="fas fa-chevron-left"></i> Nueva Venta
    </a>
</div>
@endsection

@section('content')
<link href="{{ asset('css/detalle_venta.css') }}" rel="stylesheet">
<link href="{{ asset('css/filtros_ventas.css') }}" rel="stylesheet">
<link href="{{ asset('css/flatpickr.min.css') }}" rel="stylesheet">

<!-- Filtros -->
<div class="card mx-auto my-4" style="max-width: 900px;">
    <div class="card-body">
        <form id="filter-form" method="GET" action="{{ route('ventas.listar') }}">
            <div class="row g-2 align-items-end">
                <!-- Rango -->
                <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                    <label for="filter-type" class="form-label">Rango:</label>
                    <select name="filter-type" id="filter-type" class="form-select">
                        <option value="diario" {{ $rango == 'diario' ? 'selected' : '' }}>Diario</option>
                        <option value="semanal" {{ $rango == 'semanal' ? 'selected' : '' }}>Semanal</option>
                        <option value="mensual" {{ $rango == 'mensual' ? 'selected' : '' }}>Mensual</option>
                    </select>
                </div>

                <!-- Fecha -->
                <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                    <label for="filter-date" class="form-label">Fecha:</label>
                    <div class="position-relative">
                        <input type="text" name="filter-date" id="filter-date" class="form-control pe-4" value="{{ old('filter-date', $fecha) }}">
                        <i class="fas fa-calendar-alt calendar-icon" id="calendar-icon"></i>
                    </div>
                </div>

                <!-- Cliente -->
                <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6">
                    <label for="filter-client" class="form-label">Cliente:</label>
                    <input type="text" name="filter-client" id="filter-client" class="form-control" value="{{ old('filter-client', $cliente) }}" placeholder="Por nombre, DNI o RUC" onblur="this.form.submit()" />
                </div>

                <!-- Usuario -->
                <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6">
                    <label for="filter-user" class="form-label">Usuario:</label>
                    <select name="filter-user" id="filter-user" class="form-select">
                        <option value="">Selec. un usuario</option>
                        @foreach ($usuarios as $usuario)
                            <option value="{{ $usuario->id }}" {{ request('filter-user') == $usuario->id ? 'selected' : '' }}>
                                {{ $usuario->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Botones -->
                <div class="col-xl-2 col-lg-3 col-md-6 col-sm-6 d-flex gap-2">
                    <a href="{{ route('ventas.exportarExcel', request()->query()) }}" class="btn btn-success w-100" title="Exportar a Excel">
                        <i class="fas fa-file-excel"></i>
                    </a>
                    <a href="{{ route('ventas.exportarPDF', request()->query()) }}" class="btn btn-danger w-100" title="Exportar a PDF" target="_blank">
                        <i class="fas fa-file-pdf"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Resumen -->
    <div class="card-body pt-0">
        <div class="row text-center mt-4 g-3">
            <!-- Balance -->
            <div class="col-lg-4 col-md-6 col-12">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <i class="fas fa-chart-line" style="font-size: 2rem; color: #28a745;"></i>
                        <h5 class="card-title mt-3">Balance</h5>
                        <p class="card-text fw-bold" id="balance">S/ {{ number_format($balance, 2) }}</p>
                    </div>
                </div>
            </div>

            <!-- Ventas Totales -->
            <div class="col-lg-4 col-md-6 col-12">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <i class="fas fa-money-bill-wave" style="font-size: 2rem; color: #17a2b8;"></i>
                        <h5 class="card-title mt-3">Ventas Totales</h5>
                        <p class="card-text fw-bold" id="ventasTotales">S/ {{ number_format($ventasTotales, 2) }}</p>
                    </div>
                </div>
            </div>

            <!-- Ganancia -->
            <div class="col-lg-4 col-md-6 col-12">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <i class="fas fa-piggy-bank" style="font-size: 2rem; color: #ffc107;"></i>
                        <h5 class="card-title mt-3">Ganancia</h5>
                        <p class="card-text fw-bold" id="ganancias">S/ {{ number_format($ganancias, 2) }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card mx-auto my-4" style="max-width: 900px;">
    <div class="card-header text-center bg-primary text-white">
        <h4 class="mb-0"><i class="fas fa-list"></i> Listado de Ventas</h4>
    </div>

    <!-- Scroll horizontal en dispositivos pequeños -->
    <div class="table-responsive">
        <table class="table table-bordered table-hover mt-3 text-center">
            <thead class="table-primary">
                <tr>
                    <th>#</th>
                    <th>Cliente</th>
                    <th>Tipo</th>
                    <th>Total</th>
                    <th>Fecha</th>
                    <th>Usuario</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                @forelse($ventas as $venta)
                    <tr data-id="{{ $venta->id }}" class="venta-row" onclick="mostrarDetallesVenta({{ $venta->id }})">
                        <td>{{ $venta->id }}</td>
                        <td>{{ $venta->cliente->nombre ?? $venta->documento }}</td>
                        <td>{{ ucfirst($venta->tipo_comprobante) }}</td>
                        <td>S/ {{ number_format($venta->total, 2) }}</td>
                        <td>{{ \Carbon\Carbon::parse($venta->fecha)->format('d/m/Y H:i') }}</td>
                        <td>{{ $venta->usuario->nombre }}</td>
                        <td>{{ $venta->estado ?? 'Pagada' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">No hay ventas registradas.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Paginación -->
    <div class="d-flex justify-content-center mb-3">
        {{ $ventas->appends(request()->query())->links('pagination::bootstrap-5') }}
    </div>
</div>


<!-- Modal de detalles de la venta -->
<div class="modal fade" id="detalleVentaModal" tabindex="-1" aria-labelledby="detalleVentaModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <!-- Encabezado con título y botón de cierre -->
                <h5 class="modal-title" id="detalleVentaModalLabel"><i class="fas fa-info-circle"></i> Detalles de la Venta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Card con los detalles de la venta -->
                <div class="card">
                    <div class="card-body">
                        <input type="hidden" id="modalId">

                        <!-- Encabezado con Valor Total grande y Estado alineado a la derecha -->
                        <div class="mb-3">
                            <div class="fw-semibold text-muted mb-1">VALOR TOTAL</div>
                            <div class="d-flex justify-content-between align-items-center flex-wrap">
                                <span id="modalTotal" class="monto-total">S/ </span>
                                <span id="modalEstadoPago" class="estado-pago">Pagada</span>
                            </div>
                            <hr class="linea-total mt-2" />
                        </div>

                        <div class="row mb-3">
                            <div class="col-4"><i class="fas fa-user"></i> <strong> Cliente:</strong></div>
                            <div class="col-8 text-end" id="modalCliente"></div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-4"><i class="fas fa-file-alt"></i> <strong> Comprobante:</strong></div>
                            <div class="col-8 text-end" id="modalTipoComprobante"></div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-4"><i class="fas fa-cogs"></i> <strong> Ganancia:</strong></div>
                            <div class="col-8 text-end" id="modalGanancia"></div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-4"><i class="fas fa-calendar-alt"></i> <strong> Fecha:</strong></div>
                            <div class="col-8 text-end" id="modalFecha"></div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-4"><i class="fas fa-user-tie"></i> <strong> Usuario:</strong></div>
                            <div class="col-8 text-end" id="modalUsuario"></div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-4"><i class="fas fa-check-circle"></i> <strong> Estado:</strong></div>
                            <div class="col-8 text-end"><span id="modalEstado" class="badge rounded-pill"></span></div>
                        </div>
                    </div>
                </div>

                <!-- Lista de productos -->
                <h5 class="mt-3"><i class="fas fa-box"></i> Productos:</h5>
                <div id="modalProductosContainer">
                <ul id="modalProductos" class="list-group">
                    <!-- Los productos serán insertados dinámicamente aquí -->
                </ul>
                </div>
            </div>
            <div class="modal-footer">
                <!-- Botones del modal -->
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times"></i> Cerrar</button>
                <button type="button" id="editarVentaBtn" class="btn btn-primary"><i class="fas fa-edit"></i> Editar Venta</button>
                <button type="button" class="btn btn-danger" id="eliminarVentaBtn"><i class="fas fa-trash-alt"></i> Eliminar Venta</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de autorización -->
<div class="modal fade" id="modalAutorizacion" tabindex="-1" aria-labelledby="modalAutorizacionLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form id="formAutorizacion" class="modal-content shadow-lg rounded-4 border-0">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="modalAutorizacionLabel">
          <i class="fas fa-user-shield me-2"></i> Autorización requerida
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        
        <div class="mb-3">
          <label for="admin_usuario" class="form-label">Usuario Administrador</label>
          <div class="input-group">
            <span class="input-group-text bg-light"><i class="fas fa-user"></i></span>
            <input type="text" class="form-control" id="admin_usuario" name="usuario" placeholder="Ingrese su usuario" required>
          </div>
        </div>

        <div class="mb-3">
          <label for="admin_clave" class="form-label">Contraseña</label>
          <div class="input-group">
            <span class="input-group-text bg-light"><i class="fas fa-lock"></i></span>
            <input type="password" class="form-control" id="admin_clave" name="clave" placeholder="Ingrese su contraseña" required>
          </div>
        </div>

        <div id="errorAutorizacion" class="text-danger small d-none mt-2">Credenciales incorrectas.</div>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
          <i class="fas fa-times"></i> Cancelar
        </button>
        <button type="submit" class="btn btn-primary">
          <i class="fas fa-check-circle me-1"></i> Autorizar
        </button>
      </div>
    </form>
  </div>
</div>

@endsection

@push('scripts')
    <!-- Agregar jQuery primero -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Luego, agregar tu script de validación -->
    <script src="{{ asset('js/detalle_list_ventas.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13"></script>
    <script src="{{ asset('js/filtros_ventas.js') }}"></script>
    <script>
    document.addEventListener("DOMContentLoaded", function () {
        const calendarIcon = document.getElementById('calendar-icon');
        const calendarInput = document.getElementById('filter-date');
        if (calendarIcon && calendarInput) {
            calendarIcon.addEventListener('click', function () {
                calendarInput._flatpickr.open();
            });
        }
    });
    </script>

     <script>
        // Evento para cuando cambie el filtro de rango
        document.getElementById('filter-type').addEventListener('change', function() {
            this.form.submit();
        });
        // Evento para cuando cambie el filtro de fecha
        document.getElementById('filter-date').addEventListener('change', function() {
            this.form.submit();
        });
        // Evento para cuando cambie el filtro de usuario
        document.getElementById('filter-user').addEventListener('change', function() {
            this.form.submit();
        });
        // Evento para cuando cambie el filtro de cliente
        document.getElementById('filter-client').addEventListener('input', function() {
        });
    </script>
    <script>
        $(document).ready(function() {
            $('#filter-form').on('change', 'select, input', function() {
                var formData = $('#filter-form').serialize(); // Serializa los datos del formulario
                
                $.ajax({
                    url: $('#filter-form').attr('action'),
                    method: 'GET',
                    data: formData,
                    success: function(response) {
                        // Actualizar los valores de balance, ventas totales y ganancias
                        $('#balance').text('S/ ' + response.balance.toFixed(2));
                        $('#ventasTotales').text('S/ ' + response.ventasTotales.toFixed(2));
                        $('#ganancias').text('S/ ' + response.ganancias.toFixed(2));
                    },
                    error: function(xhr, status, error) {
                        console.log('Error al filtrar:', error);
                    }
                });
            });
        });
    </script>
<script>
let ventaIdSeleccionada = null;

document.addEventListener('DOMContentLoaded', function () {
    const editarBtn = document.getElementById('editarVentaBtn');
    const formAutorizacion = document.getElementById('formAutorizacion');

    if (editarBtn) {
        editarBtn.addEventListener('click', function (e) {
            e.preventDefault();

            ventaIdSeleccionada = document.getElementById('modalId').value;

            if (!ventaIdSeleccionada) {
                Swal.fire('Error', 'No se pudo obtener el ID de la venta.', 'error');
                return;
            }

            // Cerrar el modal de detalles (si está abierto)
            const modalDetalle = bootstrap.Modal.getInstance(document.getElementById('detalleVentaModal'));
            if (modalDetalle) modalDetalle.hide();

            // Abrir modal de autorización
            const modal = new bootstrap.Modal(document.getElementById('modalAutorizacion'));
            modal.show();
        });
    }

    if (formAutorizacion) {
        formAutorizacion.addEventListener('submit', function (e) {
            e.preventDefault();

            const usuarioInput = document.getElementById('admin_usuario');
            const claveInput = document.getElementById('admin_clave');
            const usuario = usuarioInput.value;
            const clave = claveInput.value;

            fetch('/autorizar', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ usuario, clave })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const modal = bootstrap.Modal.getInstance(document.getElementById('modalAutorizacion'));
                    modal.hide();
                    window.location.href = `/ventas/${ventaIdSeleccionada}/edit`;
                } else {
                    // Limpiar formulario
                    usuarioInput.value = '';
                    claveInput.value = '';

                    // Ocultar error textual si existe
                    document.getElementById('errorAutorizacion').classList.add('d-none');

                    // Mostrar alerta con SweetAlert
                    Swal.fire({
                        icon: 'error',
                        title: 'Usuario no autorizado',
                        text: data.message || 'Credenciales incorrectas.',
                        confirmButtonColor: '#d33'
                    });
                }
            })
            .catch(err => {
                console.error(err);

                // Limpiar formulario
                document.getElementById('admin_usuario').value = '';
                document.getElementById('admin_clave').value = '';

                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error al verificar credenciales.',
                    confirmButtonColor: '#d33'
                });
            });
        });
    }
});
</script>

@endpush



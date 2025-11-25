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
        <a href="{{ route('ventas.listar') }}" class="nuevo-producto">
        <i class="fa-solid fa-coins"></i> Ventas
    </a>
</div>
@endsection

@section('content')
<link href="{{ asset('css/ventas.css') }}" rel="stylesheet" />
<div class="ventas-form">
    <h2><i class="fas fa-receipt"></i> Registro de Ventas</h2>

    <form id="ventasForm">
        <input type="hidden" id="igv-config" value="{{ $config->igv ?? 0 }}">
        <div class="main-content">
            <div class="contenedor-ventas">

                <!-- Sección Cliente y Comprobante -->
                <div class="fila-venta-datos row">

                    <!-- Datos del Cliente -->
                    <div class="col-md-6 columna-datos-cliente">

                        <div class="input-icono input-group mb-3">
                            <span class="input-group-text"><i class="fas fa-id-card"></i></span>
                            <input type="text" id="documento" name="documento" placeholder="DNI o RUC" class="form-control" />
                            <button id="guardar-cliente-btn" class="btn btn-outline-secondary" type="button" style="display: none;">
                                <i class="fas fa-save"></i></button>
                            <!-- Boton para registrar un Cliente -->
                            <button id="open-modal-btn" class="btn btn-outline-secondary" type="button" data-bs-toggle="modal" data-bs-target="#clientModal">
                                    <i class="fas fa-plus-circle"></i>
                            </button>                            
                        </div>

                        <div class="input-icono input-group mb-3">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                            <input type="text" id="razon_social" name="razon_social" placeholder="Razón Social / Nombre" readonly class="form-control" />
                        </div>

                        <div class="input-icono input-group mb-3">
                            <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                            <input type="text" id="direccion" name="direccion" placeholder="Dirección" readonly class="form-control" />
                        </div>

                        <p id="estado_ruc" class="estado-rojo"></p>

                    </div>

                    <!-- Comprobante / Pago / Fecha -->
                    <div class="col-md-6 columna-comprobante">

                        <div class="input-icono input-group mb-3">
                            <span class="input-group-text"><i class="fas fa-file-invoice"></i></span>
                            <select id="tipo_comprobante" name="tipo_comprobante" class="form-select">
                                <option value="boleta">Boleta</option>
                                <option value="factura">Factura</option>
                                <option value="nota_venta">Nota de Venta</option>
                            </select>
                        </div>
                        <!-- SERIE CORRELATIVO -->
                        <div class="input-icono input-group mb-3">
                            <span class="input-group-text"><i class="fas fa-receipt"></i></span>
                            <input type="text" id="serie_correlativo" class="form-control" placeholder="Serie-Correlativo" readonly />
                        </div>


                        <div class="input-icono input-group mb-3">
                            <span class="input-group-text"><i class="fas fa-money-bill-wave"></i></span>
                            <select id="estado_pago" name="estado_pago" class="form-select">
                                <option value="pagado">Pagado</option>
                                <option value="pendiente">Pendiente</option>
                            </select>
                        </div>

                        <div class="grupo-fecha-hora d-flex gap-3">

                            <div class="input-icono input-group mb-3 flex-fill">
                                <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                                <input type="date"
                                    id="fecha_emision"
                                    name="fecha_emision"
                                    class="form-control picker-fecha"
                                    value="{{ date('Y-m-d') }}"
                                    readonly />
                            </div>

                            <div class="input-icono input-group mb-3 flex-fill">
                                <span class="input-group-text"><i class="fas fa-clock"></i></span>
                                <input type="time"
                                    id="hora_actual"
                                    name="hora_actual"
                                    class="form-control picker-hora"
                                    value="{{ date('H:i') }}"
                                    readonly />
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <!-- Agregar Productos -->
        <div class="mb-3" >
            <label for="buscar_producto" class="form-label fw-bold"><i class="fas fa-barcode"></i> Agregar Productos</label>
            <input style="max-width: 400px; border-color: #495057"; type="text" id="buscar_producto" class="form-control" placeholder="Buscar por nombre o código..." name="buscar_producto" />
        </div>
        <!-- Contenedor de resultados -->
        <div id="resultados-busqueda" class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-3 d-none">
            <!-- Aquí van las cards -->
        </div>

        <h5 class="mt-4"><i class="fas fa-shopping-cart"></i> Lista de Productos</h5>
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-primary text-center align-middle">
                    <tr>
                        <th>Producto</th>
                        <th>Descripción</th>
                        <th>Tipo de Venta</th> <!-- Nuevo selector de tipo de venta -->
                        <th>Tipo de Unidad</th> <!-- Nuevo selector de tipo de unidad -->
                        <th>Precio.U</th> <!-- Precio Unitario -->
                        <th>Cantidad</th>
                        <th>Total</th>
                        <th>Eliminar</th>
                    </tr>
                </thead>
                <tbody id="productos-seleccionados">
                    <!-- Productos añadidos dinámicamente aquí -->
                </tbody>
            </table>
        </div>


        <div class="resumen mt-4" style="max-width: 400px; float: right;">
            <h6><i class="fas fa-chart-pie"></i> Resumen</h6>
            <div class="d-flex justify-content-between mb-2">
                <span>Op. Gravadas</span>
                <input type="text" readonly class="form-control-plaintext text-end" value="0.00" name="op_gravadas" />
            </div>
            <!-- Mostrar el IGV al usuario -->
            <div class="d-flex justify-content-between mb-2">
                <span>IGV</span>
                <span id="valor-igv-mostrado">{{ $config->igv ?? 0 }}%</span>
            </div>

            <div class="d-flex justify-content-between fw-bold fs-5 border-top pt-2">
                <span>TOTAL</span>
                <input type="text" readonly class="form-control-plaintext text-end" value="0.00" name="total" />
            </div>
            <div class="d-flex align-items-center mt-3 gap-2">
                <i class="fas fa-user"></i>
                <input type="text" readonly class="form-control-plaintext" value="{{ auth()->user()->nombre ?? 'Usuario' }}" name="usuario" />
            </div>
            <div class="d-flex align-items-center mt-3 gap-2">
            <i class="fas fa-money-bill-wave"></i>
            
            <!-- Monto pagado solo lectura -->
            <input type="text" readonly class="form-control-plaintext" 
                value="{{ old('monto_pagado', number_format($venta->total ?? 0, 2)) }}" 
                name="monto_pagado" />

            <!-- Método de pago -->
            <select id="metodo_pago" class="form-select w-auto ms-2" name="metodo_pago" required>
                <option value="">-- Selecciona --</option>
                <option value="efectivo" {{ old('metodo_pago', $venta->metodo_pago ?? '') == 'efectivo' ? 'selected' : '' }}>Efectivo</option>
                <option value="tarjeta" {{ old('metodo_pago', $venta->metodo_pago ?? '') == 'tarjeta' ? 'selected' : '' }}>Tarjeta</option>
                <option value="yape" {{ old('metodo_pago', $venta->metodo_pago ?? '') == 'yape' ? 'selected' : '' }}>Yape</option>
                <option value="plin" {{ old('metodo_pago', $venta->metodo_pago ?? '') == 'plin' ? 'selected' : '' }}>Plin</option>
                <option value="transferencia" {{ old('metodo_pago', $venta->metodo_pago ?? '') == 'transferencia' ? 'selected' : '' }}>Transferencia</option>
                <option value="otro" {{ old('metodo_pago', $venta->metodo_pago ?? '') == 'otro' ? 'selected' : '' }}>Otro</option>
            </select>
            </div>
                    <!-- ✅ Botón justo al pie del resumen -->
            <div class="d-grid mt-4">
                <button type="button" class="btn btn-success" id="continuar-venta-btn">
                    <i class="fas fa-check-circle"></i> Continuar Venta
                </button>
            </div>
        </div>
    </form>
</div>
<!-- Modal para mostrar los detalles de la venta -->
<div class="modal fade" id="modalConfirmarVenta" tabindex="-1" aria-labelledby="modalConfirmarVentaLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalConfirmarVentaLabel">Calcula el cambio de tu venta</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" id="cerrarModal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="valor_venta" class="form-label">Valor de la venta</label>
                    <div class="input-group">
                        <span class="input-group-text">S/</span>
                        <input type="text" class="form-control" id="valor_venta" readonly>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="valor_pagar" class="form-label">Valor a pagar en efectivo</label>
                    <div class="input-group">
                        <span class="input-group-text">S/</span>
                        <input type="text" class="form-control" id="valor_pagar">
                    </div>
                </div>
                <div class="mb-3">
                    <label for="valor_devolver" class="form-label">Valor a devolver</label>
                    <div class="input-group">
                        <span class="input-group-text">S/</span>
                        <input type="text" class="form-control" id="valor_devolver" readonly>
                    </div>
                </div>
                <!-- ✅ Nuevo: Formato de impresión -->
                <div class="mb-3">
                    <label for="formato_pdf" class="form-label">Formato de impresión</label>
                    <select id="formato_pdf" class="form-select">
                        <option value="a4" selected>A4</option>
                        <option value="ticket">Ticket</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="confirmar-venta">
                    <i class="fas fa-check-circle"></i> Confirmar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para registrar un cliente -->
<div class="modal fade" id="clientModal" tabindex="-1" aria-labelledby="clientModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="clientModalLabel">Registrar Cliente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="clientForm">
                    <!-- Campo Nombre -->
                    <div class="mb-3">
                        <label for="client_name" class="form-label">Nombre</label>
                        <input type="text" class="form-control" id="client_name" name="client_name" required>
                    </div>
                    
                    <!-- Campo Dirección -->
                    <div class="mb-3">
                        <label for="client_address" class="form-label">Dirección</label>
                        <input type="text" class="form-control" id="client_address" name="client_address">
                    </div>

                    <!-- Campo Teléfono -->
                    <div class="mb-3">
                        <label for="client_phone" class="form-label">Teléfono</label>
                        <input type="text" class="form-control" id="client_phone" name="client_phone">
                    </div>

                    <!-- Campo DNI -->
                    <div class="mb-3">
                        <label for="client_dni" class="form-label">DNI</label>
                        <input type="text" class="form-control" id="client_dni" name="client_dni">
                    </div>

                    <!-- Campo RUC -->
                    <div class="mb-3">
                        <label for="client_ruc" class="form-label">RUC</label>
                        <input type="text" class="form-control" id="client_ruc" name="client_ruc">
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-primary">Registrar Cliente</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- Modal de Venta Exitosa -->
<div class="modal fade" id="modalVentaExitosa" tabindex="-1" aria-labelledby="modalVentaExitosaLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content text-center p-4">
      <div class="modal-body">
        <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
        <h4 class="mb-3">¡Venta registrada con éxito!</h4>
        <p class="text-muted">Puedes imprimir o descargar el comprobante.</p>

        <div class="d-flex justify-content-center gap-3 my-4">
            <a id="btnImprimir" target="_blank" class="btn btn-primary">
                <i class="fas fa-print"></i> Imprimir
            </a>
            <a id="btn-descargar" href="#" class="btn btn-success">Descargar</a>

        </div>

        <button id="btnNuevaVenta" class="btn btn-success mt-3">
          <i class="fas fa-plus-circle"></i> Continuar vendiendo
        </button>
      </div>
    </div>
  </div>
</div>



<script src="{{ asset('js/ventas_dniruc.js') }}"></script>
<script src="{{ asset('js/ventas_productos.js') }}"></script>

@endsection
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const horaInput = document.getElementById('hora_actual');
    if (horaInput) {
        function actualizarHora() {
            const ahora = new Date();
            const horas = String(ahora.getHours()).padStart(2, '0');
            const minutos = String(ahora.getMinutes()).padStart(2, '0');
            const segundos = String(ahora.getSeconds()).padStart(2, '0');
            horaInput.value = `${horas}:${minutos}:${segundos}`;
        }
        setInterval(actualizarHora, 1000);
        actualizarHora();
    }
});
</script>

<script>
   $(document).ready(function() {
    // Detectar el clic en el botón "Abrir modal"
    $(document).on('click', '#open-modal-btn', function() {
        $('#clientModal').modal('show');
    });

    // Registrar un cliente
    $('#clientForm').on('submit', function(e) {
        e.preventDefault(); // Evitar que el formulario se envíe de forma tradicional

        // Obtener los datos del formulario del modal
        var clientName = $('#client_name').val();
        var clientAddress = $('#client_address').val();
        var clientPhone = $('#client_phone').val();
        var clientDni = $('#client_dni').val();
        var clientRuc = $('#client_ruc').val();

        // Enviar los datos del cliente al servidor con AJAX
        $.ajax({
            url: '/clientes', // Ruta para almacenar el cliente
            method: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                client_name: clientName,
                client_address: clientAddress,
                client_phone: clientPhone,
                client_dni: clientDni,
                client_ruc: clientRuc
            },
            success: function(response) {
                // Llenar los campos del formulario de ventas con los datos del cliente
                $('#razon_social').val(response.nombre);
                $('#direccion').val(response.direccion);


                // Llenar el input DNI/RUC con el DNI o RUC registrado
                $('#documento').val(response.dni || response.ruc);

                // Mostrar el alerta de éxito
                Swal.fire({
                    title: '¡Éxito!',
                    text: 'Cliente registrado correctamente',
                    icon: 'success',
                    confirmButtonText: 'Aceptar'
                }).then(() => {
                    // Limpiar los campos del modal después de cerrar
                    $('#client_name').val('');
                    $('#client_address').val('');
                    $('#client_phone').val('');
                    $('#client_dni').val('');
                    $('#client_ruc').val('');

                    // Cerrar el modal
                    $('#clientModal').modal('hide');
                });
            },
            error: function(xhr, status, error) {
                alert('Error al registrar el cliente. Inténtalo nuevamente.');
            }
        });
    });
});

</script>
@endpush
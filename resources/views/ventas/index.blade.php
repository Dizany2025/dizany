@extends('layouts.app')

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
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

<div class="container-fluid ventas-treinta">

    <!-- 🔵 HEADER -->
    <div class="treinta-header">
        <h3 class="titulo-venta">Nueva venta</h3>
    </div>

    <!-- 🔥 CUERPO PRINCIPAL 2 COLUMNAS -->
    <div class="treinta-body">

        <!-- ====================== 🟩 COLUMNA IZQUIERDA ====================== -->
        <div class="treinta-col izquierda">

            <!-- BUSCADOR -->
            <div class="contenedor-buscador">
                <div class="d-flex align-items-center">
                    <i class="fas fa-search me-2 text-primary"></i>
                    <input type="text" id="buscar_producto" class="form-control"
                        placeholder="Buscar productos por nombre o código...">
                </div>

                <!-- CATEGORÍAS -->
                <div class="ventas-categorias mt-3">
                    <button class="btn-filtro-categoria active" data-cat="0">Todos</button>

                    @foreach($categorias as $cat)
                        <button class="btn-filtro-categoria" data-cat="{{ $cat->id }}">
                            {{ strtoupper($cat->nombre) }}
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- GRID DE PRODUCTOS -->
            <div id="resultados-busqueda" class="productos-scroll row g-3">
                <!-- Productos renderizados por JS -->
            </div>

        </div> <!-- cierre columna izquierda -->
                <!-- ====================== 🟥 COLUMNA DERECHA ====================== -->
        <div class="treinta-col derecha">
        <div class="scroll-derecha venta-steps">

            <!-- █████████████████████████████ -->
            <!--        FASE 1: CARRITO       -->
            <!-- █████████████████████████████ -->
            <!-- ==================== FASE 1 ==================== -->
            <div id="step-1" class="step-panel is-active">

                <!-- HEADER FIJO -->
                <div class="card shadow-sm mb-0">
                    <div class="card-header bg-dark text-white d-flex justify-content-between">
                        <span>Productos</span>
                        <a href="#" id="vaciar-canasta" class="text-white">Vaciar canasta</a>
                    </div>
                </div>

                <!-- LISTA (SCROLL) -->
                <div id="carrito-lista" class="carrito-scroll">
                    <!-- Aquí se agregan dinámicamente los productos -->
                </div>

                <!-- FOOTER FIJO -->
                <div class="card p-2 mt-0" style="border-radius: 0 0 12px 12px;">
                    <button id="btn-ir-step2" class="btn btn-primary w-100">
                        <span id="contador-items">0</span> Continuar
                        <span class="ms-2">S/ <span id="total-general-footer">0.00</span></span>
                    </button>
                </div>

            </div>

            <!-- █████████████████████████████ -->
            <!-- FASE 2: CLIENTE + RESUMEN    -->
            <!-- █████████████████████████████ -->
            <!-- ==================== FASE 2 ==================== -->
            <div id="step-2" class="step-panel">

                <!-- CLIENTE Y COMPROBANTE -->
                <div class="card shadow-sm mb-3">
                    <div class="card-header bg-primary text-white">Cliente y Comprobante</div>

                    <div class="card-body small">
                        <div class="row g-3">

                            <!-- ========= COLUMNA IZQUIERDA: CLIENTE ========= -->
                            <div class="col-md-6">
                                <h6 class="fw-bold text-secondary mb-2">Cliente</h6>

                                <!-- DOCUMENTO -->
                                <div class="input-group input-group-sm mb-2">
                                    <span class="input-group-text"><i class="fas fa-id-card"></i></span>
                                    <input type="text" id="documento" class="form-control" placeholder="DNI / RUC">
                                    <button id="btn-cliente-accion" class="btn btn-outline-secondary" type="button">
                                        <i class="fas fa-plus-circle" id="icono-plus"></i>
                                        <i class="fas fa-save d-none" id="icono-save"></i>
                                    </button>
                                </div>

                                <p id="estado_ruc" class="text-success small mb-1"></p>

                                <!-- RAZÓN SOCIAL -->
                                <div class="input-group input-group-sm mb-2">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                    <input type="text" id="razon_social" class="form-control" placeholder="Razón Social" readonly>
                                </div>

                                <!-- DIRECCIÓN -->
                                <div class="input-group input-group-sm mb-2">
                                    <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                                    <input type="text" id="direccion" class="form-control" placeholder="Dirección" readonly>
                                </div>
                            </div>

                            <!-- ========= COLUMNA DERECHA: COMPROBANTE ========= -->
                            <div class="col-md-6">
                                <h6 class="fw-bold text-secondary mb-2">Comprobante</h6>

                                <!-- TIPO COMPROBANTE -->
                                <div class="input-group input-group-sm mb-2">
                                    <span class="input-group-text"><i class="fas fa-file-invoice"></i></span>
                                    <select id="tipo_comprobante" class="form-select">
                                        <option value="boleta">Boleta</option>
                                        <option value="factura">Factura</option>
                                        <option value="nota_venta">Nota de Venta</option>
                                    </select>
                                </div>

                                <!-- SERIE - CORRELATIVO -->
                                <div class="input-group input-group-sm mb-2">
                                    <span class="input-group-text"><i class="fas fa-receipt"></i></span>
                                    <input type="text" id="serie_correlativo" class="form-control" readonly>
                                </div>

                                <!-- ESTADO PAGO (PAGADO / PENDIENTE) -->
                                <div class="input-group input-group-sm mb-2">
                                    <span class="input-group-text"><i class="fas fa-money-bill-wave"></i></span>
                                    <select id="estado_pago" class="form-select">
                                        <option value="pagado">Pagado</option>
                                        <option value="pendiente">Pendiente</option>
                                    </select>
                                </div>

                                <!-- FECHA / HORA -->
                                <div class="row g-2">
                                    <div class="col-6">
                                        <div class="input-group input-group-sm mb-2">
                                            <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                                            <input type="date" id="fecha_emision" class="form-control form-control-sm"
                                                value="{{ date('Y-m-d') }}" readonly>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="input-group input-group-sm mb-2">
                                            <span class="input-group-text"><i class="fas fa-clock"></i></span>
                                            <input type="time" id="hora_actual" class="form-control form-control-sm"
                                                value="{{ date('H:i') }}" readonly>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

                <!-- ===== RESUMEN + MÉTODOS DE PAGO ===== -->
                <div class="card shadow-sm mb-3">
                    <div class="card-body">

                        <!-- IGV desde configuración global -->
                        <input type="hidden" id="igv-config" value="{{ $config->igv }}">

                        <div class="resumen-box mb-3">
                            <div class="resumen-row">
                                <div class="resumen-label">Op. Gravadas:</div>
                                <div class="resumen-value" id="resumen-op-gravadas">S/ 0.00</div>
                            </div>

                            <div class="resumen-row">
                                <div class="resumen-label">
                                    IGV (<span id="resumen-igv-porcentaje">0%</span>):
                                </div>
                                <div class="resumen-value" id="resumen-igv-monto">S/ 0.00</div>
                            </div>

                            <div class="resumen-row resumen-total">
                                <div class="resumen-label">TOTAL:</div>
                                <div class="resumen-value" id="resumen-total">S/ 0.00</div>
                            </div>
                        </div>

                        <!-- Inputs ocultos para backend -->
                        <input type="hidden" name="op_gravadas" value="0">
                        <input type="hidden" name="total" value="0">
                        <input type="hidden" name="monto_pagado" value="0">

                        <!-- MÉTODOS DE PAGO BONITOS -->
                        <label class="fw-bold mb-2">Método de pago:</label>
                        <div class="d-flex justify-content-between gap-1 metodo-pago-opciones">

                            @foreach([
                                ['efectivo','efectivo.svg','Efectivo'],
                                ['tarjeta','tarjeta.svg','Tarjeta'],
                                ['transferencia','transferencia.svg','Transf.'],
                                ['plin','plin.svg','Plin'],
                                ['yape','yape.svg','Yape'],
                                ['otro','otro.svg','Otro'],
                            ] as $mp)
                                <div class="metodo-pago-item" data-value="{{ $mp[0] }}">
                                    <img src="/images/{{ $mp[1] }}" class="icon-img">
                                    <span class="label">{{ $mp[2] }}</span>
                                </div>
                            @endforeach

                        </div>

                        <input type="hidden" id="metodo_pago" name="metodo_pago" value="">
                    </div>

                    <div class="card-footer d-flex justify-content-between">
                        <button id="btn-volver-step1" class="btn btn-light">
                            <i class="fas fa-arrow-left me-1"></i> Volver
                        </button>
                        <button id="btn-ir-step3" class="btn btn-success">
                            Continuar venta <i class="fas fa-arrow-right ms-2"></i>
                        </button>
                    </div>
                </div>

            </div>

            <!-- █████████████████████████████ -->
            <!--     FASE 3: PANEL DE VUELTO    -->
            <!-- █████████████████████████████ -->
            <!-- ==================== FASE 3 ==================== -->
            <div id="step-3" class="step-panel">

                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        Calcula el cambio de tu venta
                    </div>

                    <div class="card-body">

                        <label class="form-label">Valor de la venta</label>
                        <input type="text" id="vuelto-total-venta" class="form-control mb-3" readonly>

                        <label class="form-label">Valor a pagar</label>
                        <input type="number" id="vuelto-paga" class="form-control mb-3">

                        <label class="form-label">Vuelto</label>
                        <input type="text" id="vuelto-mostrar" class="form-control mb-3" readonly>

                        <label class="form-label">Formato de impresión</label>
                        <select id="formato_pdf" class="form-select">
                            <option value="a4">A4</option>
                            <option value="ticket">Ticket</option>
                        </select>

                    </div>

                    <div class="card-footer d-flex justify-content-between">
                        <button id="btn-volver-step2" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Volver
                        </button>
                        <button id="btn-confirmar-venta" class="btn btn-success">
                            <i class="fas fa-check"></i> Confirmar venta
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div><!-- /.treinta-col.derecha -->

    </div> <!-- /.treinta-body -->
</div> <!-- /.ventas-treinta -->


<!-- Modal para mostrar los detalles de la venta -->


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
<script>
  window.PRODUCTOS_INICIALES = @json($productos); // productos activos con imagen, etc.
</script>
<script src="{{ asset('js/ventas_productos.js') }}"></script>
<script>
function showStep(n) {
    document.querySelectorAll(".step-panel").forEach(p => p.classList.remove("is-active"));
    document.getElementById("step-" + n).classList.add("is-active");
}

document.getElementById("btn-ir-step2").addEventListener("click", () => showStep(2));
document.getElementById("btn-volver-step1").addEventListener("click", () => showStep(1));

document.getElementById("btn-ir-step3").addEventListener("click", () => showStep(3));
document.getElementById("btn-volver-step2").addEventListener("click", () => showStep(2));
</script>
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
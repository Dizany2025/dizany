@extends('layouts.app')

@section('header-back')
<button class="btn-header-back" onclick="history.back()">
    <i class="fas fa-arrow-left"></i>
</button>
@endsection

@section('header-title')
Panel de Editar
@endsection

@section('header-buttons')
<a href="{{ route('inventario.lotes') }}" class="btn-gasto">
    <i class="fas fa-layer-group"></i>
    <span class="btn-text">Ver lotes</span>
</a>
@endsection

@section('content')
<div class="container-fluid px-3">

    <div class="card mx-auto my-4" style="max-width: 1000px;">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">
                <i class="fas fa-truck-loading me-2"></i>
                Modificar Lote
            </h5>
        </div>

        <div class="p-3 p-md-4">
            <form action="{{ route('lotes.update', $lote->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row">
                    {{-- COLUMNA IZQUIERDA --}}
                    <div class="col-md-6">

                        <div class="mb-3">
                            <label class="inv-label">Código de lote</label>
                            <input type="text"
                                name="codigo_lote"
                                class="form-control inv-input"
                                value="{{ old('codigo_lote', $lote->codigo_lote) }}"
                                placeholder="LOTE-2026-01-A">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Producto</label>
                            <input type="text" class="form-control" disabled
                                value="{{ $lote->producto->nombre }}">
                        </div>

                        <div class="mb-2">
                            <label class="form-label">Stock</label>
                            <input type="text" class="form-control" disabled
                                value="{{ $lote->stock_actual }} / {{ $lote->stock_inicial }}">
                        </div>

                        <small class="text-muted d-flex align-items-center mb-3">
                            <i class="fas fa-lock me-2"></i>
                            El stock no puede modificarse porque este lote puede tener movimientos de venta.
                        </small>

                        <div class="mb-3">
                            <label class="form-label">Fecha de vencimiento</label>
                            <input type="date"
                                name="fecha_vencimiento"
                                class="form-control"
                                value="{{ $lote->fecha_vencimiento }}">
                        </div>

                    </div>

                    {{-- COLUMNA DERECHA --}}
                    <div class="col-md-6">

                        <div class="mb-3">
                            <label class="form-label">Precio unidad (S/)</label>
                            <input type="text"
                                name="precio_unidad"
                                class="form-control precio-decimal"
                                value="{{ $lote->precio_unidad }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Precio paquete (S/)</label>
                            <input type="text"
                                name="precio_paquete"
                                class="form-control precio-decimal"
                                value="{{ $lote->precio_paquete }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Precio caja (S/)</label>
                            <input type="text"
                                name="precio_caja"
                                class="form-control precio-decimal"
                                value="{{ $lote->precio_caja }}">
                        </div>

                    </div>
                </div>
                {{-- AJUSTE DE INVENTARIO --}}
                <div class="card border-warning mt-4">
                    <div class="card-header bg-warning bg-opacity-10 fw-semibold">
                        <i class="fas fa-tools me-2"></i> Ajuste de inventario
                    </div>

                    <div class="card-body">

                        <div class="alert alert-warning small mb-4">
                            <i class="fas fa-info-circle me-1"></i>
                            Usa este ajuste solo para correcciones de stock (conteo físico, merma, error).
                            Este cambio quedará registrado.
                        </div>

                        <div class="row align-items-end g-3">

                            {{-- TIPO DE AJUSTE --}}
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Tipo de ajuste</label>
                                <div class="btn-group w-100" role="group">
                                    <input type="radio" class="btn-check" name="tipo_ajuste" id="ajuste_restar" value="restar">
                                    <label class="btn btn-outline-danger" for="ajuste_restar">
                                        − Restar
                                    </label>

                                    <input type="radio" class="btn-check" name="tipo_ajuste" id="ajuste_sumar" value="sumar">
                                    <label class="btn btn-outline-success" for="ajuste_sumar">
                                        + Sumar
                                    </label>
                                </div>
                            </div>

                            {{-- CANTIDAD --}}
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Cantidad</label>

                                <div class="d-flex align-items-center qty-control">
                                    <button type="button" class="btn btn-light btn-qty" data-action="minus">−</button>

                                    <input type="number"
                                        id="ajuste_cantidad"
                                        class="form-control text-center mx-2"
                                        min="0"
                                        value="0">

                                    <button type="button" class="btn btn-light btn-qty" data-action="plus">+</button>
                                </div>

                                <small id="stock_resultante" class="text-muted d-block mt-1">
                                    Stock resultante: — unidades
                                </small>
                            </div>

                            {{-- MOTIVO --}}
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Motivo</label>
                                <select id="ajuste_motivo" class="form-select">
                                    <option value="">Seleccionar motivo</option>
                                    <option value="conteo_fisico">Conteo físico</option>
                                    <option value="merma">Merma</option>
                                    <option value="error_registro">Error de registro</option>
                                    <option value="ajuste_admin">Ajuste administrativo</option>
                                    <option value="otro">Otro</option>
                                </select>
                            </div>

                        </div>

                        {{-- BOTÓN APLICAR --}}
                        <div class="text-center mt-4">
                            <button type="button"
                                    id="btn_aplicar_ajuste"
                                    class="btn btn-warning px-4"
                                    disabled>
                                <i class="fas fa-save me-1"></i> Aplicar ajuste
                            </button>
                        </div>

                    </div>
                </div>


                <div class="d-flex justify-content-end gap-2 mt-3">
                    <a href="{{ route('inventario.lotes') }}" class="btn btn-secondary">
                        Cancelar
                    </a>
                    <button class="btn btn-primary">
                        Guardar cambios
                    </button>
                </div>
            </form>
            
        </div>
    </div>

</div>
@endsection

{{-- ===================== STYLES ===================== --}}
@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="{{ asset('css/ajuste_lote.css') }}">
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", () => {

        const stockActual = {{ (int) $lote->stock_actual }};
        const inputCantidad = document.getElementById("ajuste_cantidad");
        const stockResultante = document.getElementById("stock_resultante");
        const btnAplicar = document.getElementById("btn_aplicar_ajuste");
        const motivo = document.getElementById("ajuste_motivo");

        function tipoAjuste() {
            return document.querySelector('input[name="tipo_ajuste"]:checked')?.value || null;
        }

        function recalcular() {
            const tipo = tipoAjuste();
            const cantidad = parseInt(inputCantidad.value) || 0;

            // 1) Mostrar stock resultante SIN depender del motivo
            if (!tipo || cantidad <= 0) {
                stockResultante.textContent = "Stock resultante: — unidades";
            } else {
                let nuevoStock = stockActual;
                if (tipo === "sumar") nuevoStock += cantidad;
                if (tipo === "restar") nuevoStock -= cantidad;

                stockResultante.textContent = `Stock resultante: ${nuevoStock} unidades`;

                // opcional: si resta y queda negativo, deshabilitar
                if (nuevoStock < 0) {
                    stockResultante.textContent = `Stock resultante: ${nuevoStock} unidades (inválido)`;
                }
            }

            // 2) El botón SOLO se habilita si todo está OK
            const puedeAplicar = (
                tipo &&
                cantidad > 0 &&
                motivo.value &&
                ((tipo === "sumar") || (tipo === "restar")) &&
                (tipo !== "restar" || (stockActual - cantidad) >= 0)
            );

            btnAplicar.disabled = !puedeAplicar;
        }

        document.querySelectorAll('.btn-qty').forEach(btn => {
        btn.addEventListener("click", () => {
            let val = parseInt(inputCantidad.value) || 0;
            inputCantidad.value = btn.dataset.action === "plus"
                    ? val + 1
                    : Math.max(0, val - 1);
                recalcular();
            });
        });

        document.querySelectorAll('input[name="tipo_ajuste"]').forEach(r =>
            r.addEventListener("change", recalcular)
        );

        inputCantidad.addEventListener("input", recalcular);
        $('#ajuste_motivo').on('change', recalcular);

        // primer render
        recalcular();
    });
</script>

<script>
    document.addEventListener("DOMContentLoaded", function () {

        $('#ajuste_motivo').select2({
            placeholder: 'Seleccionar motivo',
            allowClear: true,
            width: '100%',
            minimumResultsForSearch: Infinity, // 👈 oculta buscador
            dropdownParent: $('#ajuste_motivo').closest('.card-body')
        });

    });
</script>

<script>
    document.getElementById('btn_aplicar_ajuste').addEventListener('click', () => {

        const tipo = document.querySelector('input[name="tipo_ajuste"]:checked')?.value;
        const cantidad = parseInt(document.getElementById('ajuste_cantidad').value) || 0;
        const motivo = document.getElementById('ajuste_motivo').value;

        if (!tipo || cantidad <= 0 || !motivo) {
            Swal.fire({
                icon: 'warning',
                title: 'Datos incompletos',
                text: 'Completa todos los campos del ajuste',
                width: 320,
                confirmButtonText: 'Entendido'
            });
            return;
        }

        Swal.fire({
            title: 'Confirmar ajuste',
            text: `¿Deseas ${tipo === 'sumar' ? 'sumar' : 'restar'} ${cantidad} unidades?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Aplicar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: tipo === 'sumar' ? '#198754' : '#dc3545',
            cancelButtonColor: '#6c757d',
            width: 360,
            reverseButtons: true
        }).then((result) => {

            if (!result.isConfirmed) return;

            fetch("{{ route('lotes.ajustar', $lote->id) }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    tipo: tipo,
                    cantidad: cantidad,
                    motivo: motivo
                })
            })
            .then(res => res.json())
            .then(data => {
                Swal.fire({
                    icon: 'success',
                    title: 'Ajuste aplicado',
                    text: data.message,
                    timer: 1500,
                    showConfirmButton: false,
                    width: 320
                }).then(() => {
                    location.reload(); // ver stock actualizado
                });
            })
            .catch(() => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudo aplicar el ajuste',
                    width: 320
                });
            });

        });
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function () {

        function formatearPrecio(input) {
            let valor = input.value.replace(',', '.').trim();

            if (valor === '' || isNaN(valor)) {
                input.value = '';
                return;
            }

            let numero = parseFloat(valor);

            // Máximo 3 decimales
            let formateado = numero.toFixed(3);

            // Quitar .000, .00 innecesarios
            formateado = formateado.replace(/\.?0+$/, '');

            // Asegurar mínimo 2 decimales
            if (!formateado.includes('.')) {
                formateado += '.00';
            } else {
                let decimales = formateado.split('.')[1].length;
                if (decimales === 1) {
                    formateado += '0';
                }
            }

            input.value = formateado;
        }

        document.querySelectorAll('.precio-decimal').forEach(input => {

            // 🔥 FORMATEAR AL CARGAR
            formatearPrecio(input);

            // 🔥 FORMATEAR AL SALIR
            input.addEventListener('blur', function () {
                formatearPrecio(this);
            });

        });

    });
</script>


@endpush

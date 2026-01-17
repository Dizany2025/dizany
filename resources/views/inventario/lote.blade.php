@extends('layouts.app')

{{-- Activa el sistema de header-actions --}}
@extends('layouts.app')

{{-- BOTÓN ATRÁS --}}
@section('header-back')
<button class="btn-header-back" onclick="history.back()">
    <i class="fas fa-arrow-left"></i>
</button>
@endsection

{{-- TÍTULO --}}
@section('header-title')
Ingreso de Mercadería
@endsection

{{-- ACCIONES DEL HEADER --}}
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
                Ingreso de mercadería por lote
            </h5>
        </div>

        <div class="p-3 p-md-4">

            <form action="{{ route('inventario.lote.store') }}" method="POST">
                @csrf

                <div class="row g-4">

                    {{-- Columna izquierda --}}
                    <div class="col-lg-6">
                        <div class="mb-3 inv-section-title">
                            <span class="dot"></span> Producto / Proveedor
                        </div>

                        <div class="mb-3">
                            <label class="inv-label">Producto</label>
                            <select name="producto_id" id="producto-select" class="form-select inv-select" required>
                                <option value="">Buscar producto...</option>
                                @foreach($productos as $producto)
                                    <option value="{{ $producto->id }}"
                                            data-vencimiento="{{ $producto->maneja_vencimiento }}">
                                        {{ $producto->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="inv-label">Proveedor</label>
                            <select name="proveedor_id"
                                    id="proveedor-select"
                                    class="form-select inv-select">
                                <option value="">— Sin proveedor —</option>
                                @foreach($proveedores as $proveedor)
                                    <option value="{{ $proveedor->id }}"
                                        data-doc="{{ $proveedor->ruc ?? $proveedor->documento ?? '—' }}">
                                        {{ $proveedor->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>


                        <div class="alert alert-info mt-3 mb-0" style="border-radius:12px;border:1px solid #dbe5f3;">
                            <strong>Tip:</strong> Usa el buscador para escribir “coca 500”, “pilsen 630”, etc.
                        </div>
                    </div>

                    {{-- Columna derecha --}}
                    <div class="col-lg-6">
                        <div class="mb-3 inv-section-title" style="color:#0f5132;">
                            <span class="dot" style="background:#14a44d; box-shadow:0 0 0 5px rgba(20,164,77,.12)"></span>
                            Datos del lote
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="inv-label">Cantidad</label>
                                <input type="number" name="cantidad" class="form-control inv-input" min="1" required>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="inv-label">Costo unit. (S/)</label>
                                <input type="number" name="costo_unitario" class="form-control inv-input" step="0.01" min="0" required>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="inv-label">Precio venta (S/)</label>
                                <input type="number" name="precio_venta" class="form-control inv-input" step="0.01" min="0" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="inv-label">Fecha ingreso</label>
                                <input
                                    type="text"
                                    name="fecha_ingreso"
                                    class="form-control inv-input date-ingreso"
                                    value="{{ now()->format('Y-m-d') }}"
                                    required
                                >
                            </div>

                            <div class="col-md-6 mb-3" id="grupo-vencimiento" style="display:none;">
                                <label class="inv-label">Fecha vencimiento</label>
                                <input
                                    type="text"
                                    name="fecha_vencimiento"
                                    class="form-control inv-input date-vencimiento"
                                >
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="inv-label">Método de pago</label>
                            <select name="metodo_pago" class="form-select inv-select" required>
                                <option value="efectivo">Efectivo (Caja)</option>
                                <option value="transferencia">Transferencia</option>
                                <option value="credito">Crédito proveedor</option>
                            </select>
                        </div>
                    </div>

                </div>

                {{-- Footer botones --}}
                <div class="inv-actions mt-4">
                    <a href="{{ route('inventario.stock') }}" class="btn btn-soft btn-cancel">
                        Cancelar
                    </a>
                    <button type="submit" class="btn btn-soft btn-save">
                        <i class="fas fa-save me-1"></i> Guardar lote
                    </button>
                </div>

            </form>
            
        </div>
    </div>

</div>
@endsection

@push('styles')

<!-- Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />  
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/material_blue.css">

    <link rel="stylesheet" href="{{ asset('css/lote.css') }}">

@endpush

@push('scripts')
    <!-- jQuery (si ya lo tienes global, puedes quitarlo aquí) -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Select2 -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>
    
    <script>
flatpickr(".date-ingreso", {
    locale: "es",
    dateFormat: "Y-m-d",
    altInput: true,
    altFormat: "d F Y",
    allowInput: true,
    disableMobile: true
    // ⛔ NO minDate
});

flatpickr(".date-vencimiento", {
    locale: "es",
    dateFormat: "Y-m-d",
    altInput: true,
    altFormat: "d F Y",
    allowInput: true,
    disableMobile: true,
    minDate: "today" // ✅ aquí sí
});
</script>

    <script>
        $('#producto-select').on('change', function () {
            const vence = $(this).find(':selected').data('vencimiento');

            if (vence == 1) {
                $('#grupo-vencimiento').slideDown();
            } else {
                $('#grupo-vencimiento').slideUp();
                $('input[name="fecha_vencimiento"]').val('');
            }
        });
    </script>

    <script>
        $(document).ready(function () {

            function formatProducto (item) {
                if (!item.id) return item.text;

                const desc = $(item.element).data('descripcion');
                return $(`
                    <div class="prod-item">
                        <div class="name">${item.text}</div>
                        ${desc ? `<div class="desc">${desc}</div>` : ''}
                    </div>
                `);
            }

            $('#producto-select').select2({
                placeholder: 'Buscar producto...',
                allowClear: true,
                width: '100%',
                templateResult: formatProducto,
                templateSelection: function (item) {
                    if (!item.id) return item.text;
                    return item.text;
                }
            });
        });


        // proveedores
        function formatProveedor (item) {
            if (!item.id) return item.text;

            const doc = $(item.element).data('doc');

            return $(`
                <div class="prod-item">
                    <div class="name">${item.text}</div>
                    <div class="desc">Doc: ${doc}</div>
                </div>
            `);
        }

        $('#proveedor-select').select2({
            placeholder: 'Buscar proveedor...',
            allowClear: true,
            width: '100%',
            templateResult: formatProveedor,
            templateSelection: function (item) {
                return item.text;
            }
        });

    </script>
@endpush
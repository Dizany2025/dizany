@extends('layouts.app')

@push('styles')
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <!-- Flatpickr CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" />
@endpush

{{-- BOTÓN ATRÁS (opcional) --}}
@section('header-back')
<button class="btn-header-back" onclick="history.back()">
    <i class="fas fa-arrow-left"></i>
</button>
@endsection

{{-- TÍTULO --}}
@section('header-title')
Gastos
@endsection

{{-- BOTONES DERECHA --}}
@section('header-buttons')
<a href="{{ route('gastos.create') }}" class="btn-gasto">
    <i class="fa-solid fa-plus"></i>
    <span class="btn-text">Nuevo gasto</span>
</a>
@endsection

@section('content')
<link rel="stylesheet" href="{{ asset('css/gastos.css') }}">
    <link rel="stylesheet" href="{{ asset('css/flatpickr.min.css') }}">
<div class="container-fluid px-3">
    <div class="card mx-auto my-4" style="max-width: 100%;">
        <div class="card-header text-center bg-primary text-white">
            <h4 class="mb-0"><i class="fas fa-money-bill-wave"></i> Lista de Gastos</h4>
        </div>
        <div class="card-body">
            <!-- Filtros Dinámicos -->
            <div class="row g-3 mb-3">
                <div class="col-12 col-md-4">
                    <label for="filter-date" class="form-label">: Por Fecha:</label>
                    <input type="text" id="filter-date" class="form-control" placeholder="Selecciona una fecha">
                </div>
                <div class="col-12 col-md-4">
                    <label for="filter-descripcion" class="form-label">: Por Descripción:</label>
                    <input type="text" id="filter-descripcion" class="form-control" placeholder="Filtrar por descripción">
                </div>
                <div class="col-12 col-md-4">
                    <label for="filter-usuario" class="form-label">: Por Usuario:</label>
                    <select id="filter-usuario" class="form-select">
                        <option value="">Seleccione un usuario</option>
                        @foreach($usuarios as $usuario)
                            <option value="{{ $usuario->id }}">{{ $usuario->nombre }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Mensaje de no encontrados -->
            <div id="no-gastos-msg" class="alert alert-warning d-none">
                No se encontraron gastos para los filtros aplicados.
            </div>

            <!-- Tabla Gastos -->
            <div class="table-responsive">
                <table class="table table-striped table-bordered mb-0">
                    <thead class="thead-dark">
                        <tr>
                            <th>Fecha</th>
                            <th>Descripción</th>
                            <th>Monto</th>
                            <th>Método de Pago</th>
                            <th>Usuario</th>
                        </tr>
                    </thead>
                    <tbody id="tabla-gastos">
                        @foreach($gastos as $gasto)
                        <tr>
                            <td>{{ date('d/m/Y H:i', strtotime($gasto->fecha)) }}</td>
                            <td>{{ $gasto->descripcion }}</td>
                            <td>S/ {{ number_format($gasto->monto, 2) }}</td>
                            <td>{{ $gasto->metodo_pago }}</td>
                            <td>{{ $gasto->usuario->nombre ?? '—' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="2" class="text-end">Total del día:</th>
                            <th id="total-gastos">S/ 0.00</th>
                            <th colspan="2"></th>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Paginación -->
            <div class="mt-3">
                {{ $gastos->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const inputFecha         = document.getElementById("filter-date");
    const filterDescripcion  = document.getElementById("filter-descripcion");
    const filterUsuario      = document.getElementById("filter-usuario");
    const noGastosMsg        = document.getElementById("no-gastos-msg");
    const totalGastosEl      = document.getElementById("total-gastos");

     // Establecer el valor predeterminado de fecha como hoy (sin hora)
    const today = new Date();
    const todayFormatted = today.toLocaleDateString("en-CA"); // "YYYY-MM-DD"
    inputFecha.value = todayFormatted;

    // Usar flatpickr para seleccionar la fecha
    flatpickr("#filter-date", {
        dateFormat: "Y-m-d", // Formato de la fecha
        defaultDate: todayFormatted,
        onChange: function () {
            filtrarTabla(); // Filtra cada vez que la fecha cambia
        }
    });

    // Suma los montos de las filas visibles
    function calcularTotal() {
        let suma = 0;
        document.querySelectorAll("#tabla-gastos tr").forEach(row => {
            if (row.style.display !== 'none') {
                const texto = row.cells[2].textContent.replace(/[^\d.]/g, '');
                suma += parseFloat(texto) || 0;
            }
        });
        totalGastosEl.textContent = `S/ ${suma.toFixed(2)}`;
    }

    // Petición AJAX y renderizado de la tabla filtrada
    function filtrarTabla() {
        const fechaValue       = inputFecha.value.trim();
        const descripcionValue = filterDescripcion.value.trim().toLowerCase();
        const usuarioValue     = filterUsuario.value.trim();
        const url              = "{{ route('gastos.index') }}";

        $.ajax({
            url: url,
            type: 'GET',
            data: {
                fecha: fechaValue,
                descripcion: descripcionValue,
                usuario: usuarioValue
            },
            success: function (response) {
                const parser = new DOMParser();
                const doc = parser.parseFromString(response, 'text/html');
                document.getElementById("tabla-gastos").innerHTML =
                    doc.querySelector('#tabla-gastos').innerHTML;

                // Mostrar u ocultar mensaje
                noGastosMsg.classList.toggle('d-none',
                    document.querySelectorAll('#tabla-gastos tr').length > 0
                );

                // Recalcular total
                calcularTotal();
            },
            error: function (xhr, status, error) {
                console.error("Error al filtrar los datos:", error);
            }
        });
    }

    // Listeners de filtro
    inputFecha.addEventListener("input", filtrarTabla);
    filterDescripcion.addEventListener("input", filtrarTabla);
    filterUsuario.addEventListener("change", filtrarTabla);

    // Filtrar y calcular al cargar la página
    filtrarTabla();
});
</script>

@endpush
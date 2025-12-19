@extends('layouts.app')

@section('content')
<div class="container-fluid">

    {{-- ================= HEADER ================= --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">Movimientos</h4>

        <div class="d-flex gap-2">
            <button class="btn btn-dark">
                👑 Abrir caja
            </button>

            <button class="btn btn-outline-dark">
                ⬇️ Descargar reporte
            </button>
        </div>
    </div>

    {{-- ================= TABS PRINCIPALES ================= --}}
    <div class="card mb-3">
        <div class="card-body p-2 d-flex">
            <button class="btn btn-dark flex-fill">Transacciones</button>
            <button class="btn btn-light flex-fill">Cierres de caja</button>
        </div>
    </div>

    {{-- ================= FILTROS ================= --}}
    <div class="d-flex flex-wrap gap-2 align-items-center mb-4">

        <button class="btn btn-outline-secondary">
            🔍 Filtrar
        </button>

        <select class="form-select w-auto">
            <option>Diario</option>
            <option>Semanal</option>
            <option>Mensual</option>
            <option>Rango</option>
        </select>

        <input type="date" class="form-control w-auto">

        <div class="input-group w-auto">
            <span class="input-group-text">🔎</span>
            <input type="text" class="form-control" placeholder="Buscar concepto...">
        </div>
    </div>

    {{-- ================= KPIs ================= --}}
    <div class="row mb-4">

        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-success bg-opacity-10 p-3">
                        📈
                    </div>
                    <div>
                        <small class="text-muted">Balance</small>
                        <h5 class="fw-bold mb-0">S/ {{ number_format($balance ?? 0, 2) }}</h5>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-success bg-opacity-10 p-3">
                        💵
                    </div>
                    <div>
                        <small class="text-muted">Ventas totales</small>
                        <h5 class="fw-bold text-success mb-0">S/ {{ number_format($ventas ?? 0, 2) }}</h5>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-danger bg-opacity-10 p-3">
                        💸
                    </div>
                    <div>
                        <small class="text-muted">Gastos totales</small>
                        <h5 class="fw-bold text-danger mb-0">S/ {{ number_format($gastos ?? 0, 2) }}</h5>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- ================= SUB TABS ================= --}}
    <ul class="nav nav-tabs mb-3">
        <li class="nav-item">
            <a class="nav-link active" href="#">Ingresos</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#">Egresos</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#">Por cobrar</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#">Por pagar</a>
        </li>
    </ul>

    {{-- ================= TABLA ================= --}}
    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Fecha</th>
                        <th>Concepto</th>
                        <th>Método</th>
                        <th>Estado</th>
                        <th class="text-end">Monto</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>

                @forelse ($movimientos ?? [] as $movimiento)
                    <tr>
                        <td>{{ $movimiento->fecha }}</td>
                        <td>{{ $movimiento->concepto }}</td>
                        <td>{{ ucfirst($movimiento->metodo_pago) }}</td>

                        <td>
                            @if($movimiento->estado === 'pagado')
                                <span class="badge bg-success">Pagado</span>
                            @else
                                <span class="badge bg-warning text-dark">Pendiente</span>
                            @endif
                        </td>

                        <td class="text-end fw-bold
                            {{ $movimiento->tipo === 'ingreso' ? 'text-success' : 'text-danger' }}">
                            {{ $movimiento->tipo === 'ingreso' ? '+' : '-' }}
                            S/ {{ number_format($movimiento->monto, 2) }}
                        </td>

                        <td class="text-center">
                            <button class="btn btn-sm btn-outline-secondary">👁</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">
                            No hay movimientos para mostrar
                        </td>
                    </tr>
                @endforelse

                </tbody>
            </table>
        </div>
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
   

@endpush



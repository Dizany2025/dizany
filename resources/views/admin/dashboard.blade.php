@extends('layouts.app')

@section('content')
<div class="container-fluid">

    {{-- ================= KPIs ================= --}}
    <div class="row mb-4">

        <div class="col-md-3">
            <div class="kpi-card kpi-primary">
                <small class="kpi-title">Balance actual</small>
                <div class="kpi-value">S/ {{ number_format($balance,2) }}</div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="kpi-card kpi-success">
                <small class="kpi-title">Ingresos hoy</small>
                <div class="kpi-value success">
                    + S/ {{ number_format($ingresosHoy,2) }}
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="kpi-card kpi-danger">
                <small class="kpi-title">Gastos hoy</small>
                <div class="kpi-value danger">
                    - S/ {{ number_format($egresosHoy,2) }}
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="kpi-card kpi-warning">
                <small class="kpi-title">Pendientes</small>
                <div class="kpi-sub warning">
                    Cobrar: S/ {{ number_format($porCobrar,2) }} <br>
                    Pagar: S/ {{ number_format($porPagar,2) }}
                </div>
            </div>
        </div>

    </div>

         {{-- ================= CONTENIDO ================= --}}
    <div class="row">

        {{-- ================= FLUJO ================= --}}
        <div class="col-md-8">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h6 class="section-title">Flujo de caja (7 días)</h6>
                    <div style="height:280px; position:relative;">
                        <div class="chart-wrapper">
                            <canvas id="flujoCajaChart"></canvas>
                        </div>
                    </div> 
                </div>
            </div>
        </div>

        {{-- ================= ÚLTIMOS MOVIMIENTOS ================= --}}
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h6 class="fw-bold mb-3">Últimos movimientos</h6>

                    @forelse($ultimosMovimientos as $m)
                        <div class="d-flex justify-content-between align-items-center ult-mov-item">

                            <div>
                                <div class="fw-semibold">
                                    {{ $m->concepto }}
                                </div>
                                <small class="text-muted">
                                    {{ \Carbon\Carbon::parse($m->fecha)->format('d/m/Y') }}
                                </small>
                            </div>

                            <div class="text-end">
                                <div class="fw-bold {{ $m->tipo === 'ingreso' ? 'text-success' : 'text-danger' }}">
                                    {{ $m->tipo === 'ingreso' ? '+' : '-' }}
                                    S/ {{ number_format($m->monto,2) }}
                                </div>

                                <span class="badge {{ $m->estado === 'pagado' ? 'bg-success' : 'bg-warning text-dark' }}">
                                    {{ ucfirst($m->estado) }}
                                </span>
                            </div>

                        </div>
                    @empty
                        <div class="text-center text-muted py-4">
                            No hay movimientos recientes
                        </div>
                    @endforelse

                </div>
            </div>
        </div>

    </div>

</div>


@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
window.dashboardLabels = @json($labels);
window.dashboardIngresos = @json($ingresosData);
window.dashboardEgresos = @json($egresosData);
</script>
<script src="{{ asset('js/dashboard.js') }}"></script>
@endpush

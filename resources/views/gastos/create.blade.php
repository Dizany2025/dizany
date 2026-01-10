@extends('layouts.app')
@push('styles')
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <!-- CSS personalizado para productos -->
@endpush
{{-- BOTÓN ATRÁS (opcional) --}}
@section('header-back')
<button class="btn-header-back" onclick="history.back()">
    <i class="fas fa-chevron-left"></i>
</button>
@endsection

{{-- TÍTULO --}}
@section('header-title')
Nuevo Gasto
@endsection

{{-- BOTONES DERECHA --}}
@section('header-buttons')
{{-- vacio --}}
@endsection

@section('content')
<div class="card mx-auto my-4" style="max-width: 900px;">
    <div class="card-header text-center bg-primary text-white">
        <h4 class="mb-0"><i class="fas fa-money-bill-wave"></i> Registrar Gasto</h4>
    </div>
    <div class="card-body">
        <div class="container">

            <form action="{{ route('gastos.store') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Usuario</label>
                    <input type="text"
                        class="form-control"
                        value="{{ auth()->user()->nombre }}"
                        disabled>

                    <input type="hidden"
                        name="usuario_id"
                        value="{{ auth()->id() }}">
                </div>

                <div class="mb-3">
                    <label>Descripción:</label>
                    <input type="text" name="descripcion" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label>Monto (S/):</label>
                    <input type="number" name="monto" class="form-control" step="0.01" min="0.01" required>
                </div>

                <div class="mb-3">
                    <label>Fecha:</label>
                    <input type="datetime-local" name="fecha" class="form-control" value="{{ now()->format('Y-m-d\TH:i') }}" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Método de pago</label>
                    <select name="metodo_pago" class="form-select" required>
                        <option value="">Seleccione un método</option>
                        <option value="efectivo">Efectivo</option>
                        <option value="yape">Yape</option>
                        <option value="plin">Plin</option>
                        <option value="transferencia">Transferencia</option>
                        <option value="tarjeta">Tarjeta</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-success">Registrar</button>
                <a href="{{ route('gastos.index') }}" class="btn btn-secondary">Cancelar</a>
            </form>
        </div>
    </div>
</div>
@endsection

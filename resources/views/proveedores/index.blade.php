@extends('layouts.app')

{{-- BOTÓN ATRÁS --}}
@section('header-back')
<button class="btn-header-back" onclick="history.back()">
    <i class="fas fa-arrow-left"></i>
</button>
@endsection

{{-- TÍTULO --}}
@section('header-title')
Proveedores
@endsection

{{-- ACCIONES DEL HEADER --}}
@section('header-buttons')
<button class="btn-gasto"
        data-bs-toggle="modal"
        data-bs-target="#modalProveedor">
        <i class="fas fa-plus me-1"></i> Nuevo proveedor
</button>
@endsection

@section('content')
<div class="container-fluid px-3 mt-4">

    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-industry me-2"></i>
                Lista de Proveedores
            </h5>
        </div>

        <div class="card-body p-0">

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Nombre</th>
                            <th>Documento</th>
                            <th>Contacto</th>
                            <th>Teléfono</th>
                            <th>Email</th>
                            <th>Estado</th>
                            <th width="120">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($proveedores as $proveedor)
                            <tr>
                                <td>{{ $proveedor->nombre }}</td>
                                <td>
                                    {{ $proveedor->tipo_documento }}
                                    {{ $proveedor->numero_documento }}
                                </td>
                                <td>{{ $proveedor->contacto ?? '—' }}</td>
                                <td>{{ $proveedor->telefono ?? '—' }}</td>
                                <td>{{ $proveedor->email ?? '—' }}</td>
                                <td>
                                    @if($proveedor->estado)
                                        <span class="badge bg-success">Activo</span>
                                    @else
                                        <span class="badge bg-secondary">Inactivo</span>
                                    @endif
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-pen"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    No hay proveedores registrados
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>

</div>


{{-- ================= MODAL ================= --}}
<div class="modal fade" id="modalProveedor" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">

            <form action="{{ route('proveedores.store') }}" method="POST">
                @csrf

                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-plus-circle me-2"></i>
                        Nuevo Proveedor
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <div class="row g-3">

                        <div class="col-md-6">
                            <label class="form-label">Nombre</label>
                            <input type="text" name="nombre" class="form-control" required>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Tipo Doc.</label>
                            <select name="tipo_documento" class="form-select" required>
                                <option value="RUC">RUC</option>
                                <option value="DNI">DNI</option>
                                <option value="OTRO">OTRO</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">N° Documento</label>
                            <input type="text" name="numero_documento" class="form-control" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Contacto</label>
                            <input type="text" name="contacto" class="form-control">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Teléfono</label>
                            <input type="text" name="telefono" class="form-control">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control">
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">Dirección</label>
                            <input type="text" name="direccion" class="form-control">
                        </div>

                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary"
                            data-bs-dismiss="modal">Cancelar</button>

                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save me-1"></i> Guardar proveedor
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>

@endsection

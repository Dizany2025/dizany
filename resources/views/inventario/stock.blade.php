@extends('layouts.app')

@section('content')
<style>
.toast-actualizado {
    position: fixed;
    bottom: 20px;
    right: 20px;
    background-color: #28a745;
    color: white;
    padding: 10px 16px;
    border-radius: 5px;
    font-size: 14px;
    opacity: 0;
    z-index: 9999;
    transition: opacity 0.4s ease;
}
.toast-actualizado.show {
    opacity: 1;
}
.mensaje-exito {
    white-space: nowrap;
}
</style>

<div class="container py-4">
    <h3 class="mb-4 text-primary"><i class="fas fa-warehouse me-2"></i> Panel de Inventario</h3>

    <div class="card shadow rounded-4">
        <div class="card-body">
            <!-- Pestañas -->
            <ul class="nav nav-tabs mb-3" id="inventarioTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="stock-tab" data-bs-toggle="tab" data-bs-target="#stock" type="button">
                        <i class="fas fa-boxes-stacked me-1"></i> Poco Stock
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="vencimiento-tab" data-bs-toggle="tab" data-bs-target="#vencimiento" type="button">
                        <i class="fas fa-calendar me-1"></i> Próximos a Vencer
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="inventarioTabsContent">
                <!-- TAB 1: Poco Stock -->
                <div class="tab-pane fade show active" id="stock">
                    <div class="mb-3">
                        <label class="fw-bold text-secondary">
                            <i class="fas fa-filter me-1"></i> Filtrar por Categoría:
                        </label>
                        <select id="filtro-categoria-stock" class="form-select w-auto d-inline-block">
                            <option value="">-- Todas --</option>
                            @foreach($categorias as $cat)
                                <option value="{{ $cat->nombre }}">{{ $cat->nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle table-bordered border-light-subtle">
                            <thead class="table-danger text-center">
                                <tr>
                                    <th><i class="fas fa-tag"></i> Nombre</th>
                                    <th><i class="fas fa-align-left"></i> Descripción</th>
                                    <th><i class="fas fa-box"></i> Stock</th>
                                    <th><i class="fas fa-layer-group"></i> Categoría</th>
                                    <th><i class="fas fa-map-pin"></i> Ubicación</th>
                                </tr>
                            </thead>
                            <tbody id="tabla-stock-bajo">
                                @foreach($stock_bajo as $producto)
                                    @php 
                                        $clase = '';
                                        if ($producto->stock < 5) {
                                            $clase = 'table-danger';
                                        } elseif ($producto->stock < 10) {
                                            $clase = 'table-warning';
                                        }
                                    @endphp
                                    <tr class="{{ $clase }}" data-categoria="{{ $producto->categoria->nombre ?? 'Sin categoría' }}">

                                        <td>{{ $producto->nombre }}</td>
                                        <td>{{ $producto->descripcion ?? '-' }}</td>
                                        <td class="d-flex align-items-center gap-2">
                                            <input  
                                                type="number"
                                                min="0"
                                                class="form-control form-control-sm stock-input text-danger fw-bold"
                                                data-id="{{ $producto->id }}"
                                                value="{{ $producto->stock }}"
                                                style="width: 80px;" />
                                            <span class="mensaje-exito text-success small fw-semibold d-none">
                                                <i class="fas fa-check-circle me-1"></i> Actualizado
                                            </span>
                                        </td>
                                        <td>{{ $producto->categoria->nombre ?? '-' }}</td>
                                        <td>{{ $producto->ubicacion }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- TAB 2: Próximos a Vencer -->
                <div class="tab-pane fade" id="vencimiento">
                    <div class="mb-3">
                        <label class="fw-bold text-secondary">
                            <i class="fas fa-filter me-1"></i> Filtrar por Categoría:
                        </label>
                        <select id="filtro-categoria-vencimiento" class="form-select w-auto d-inline-block">
                            <option value="">-- Todas --</option>
                            @foreach($categorias as $cat)
                                <option value="{{ $cat->nombre }}">{{ $cat->nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle table-bordered border-light-subtle">
                            <thead class="table-warning text-center">
                                <tr>
                                    <th><i class="fas fa-tag"></i> Nombre</th>
                                    <th><i class="fas fa-align-left"></i> Descripción</th>
                                    <th><i class="fas fa-calendar"></i> Vencimiento</th>
                                    <th><i class="fas fa-layer-group"></i> Categoría</th>
                                    <th><i class="fas fa-map-pin"></i> Ubicación</th>
                                </tr>
                            </thead>
                            <tbody id="tabla-vencimiento">
                                @foreach($proximos_a_vencer as $producto)
                                    @php
                                        $diasRestantes = \Carbon\Carbon::today()->diffInDays(\Carbon\Carbon::parse($producto->fecha_vencimiento));
                                    @endphp
                                    <tr data-categoria="{{ $producto->categoria->nombre ?? 'Sin categoría' }}">
                                        <td>
                                            {{ $producto->nombre }}
                                        </td>
                                        <td class="text-danger fw-bold">
                                            {{ \Carbon\Carbon::parse($producto->fecha_vencimiento)->format('d/m/Y') }}
                                            <br>
                                            <small class="text-muted">{{ $diasRestantes }} días restantes</small>
                                        </td>
                                        <td>{{ $producto->categoria->nombre ?? '-' }}</td>
                                        <td>{{ $producto->ubicacion }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div> <!-- end tab-content -->
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Filtro dinámico para poco stock
    document.getElementById('filtro-categoria-stock').addEventListener('change', function () {
        const categoria = this.value.toLowerCase();
        document.querySelectorAll('#tabla-stock-bajo tr').forEach(fila => {
            const filaCategoria = fila.dataset.categoria?.toLowerCase() || '';
            fila.style.display = !categoria || filaCategoria === categoria ? '' : 'none';
        });
    });

    // Filtro dinámico para vencimiento
    document.getElementById('filtro-categoria-vencimiento').addEventListener('change', function () {
        const categoria = this.value.toLowerCase();
        document.querySelectorAll('#tabla-vencimiento tr').forEach(fila => {
            const filaCategoria = fila.dataset.categoria?.toLowerCase() || '';
            fila.style.display = !categoria || filaCategoria === categoria ? '' : 'none';
        });
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.stock-input').forEach(input => {
            input.addEventListener('change', function () {
                const productoId = this.dataset.id;
                const nuevoStock = this.value;
                const mensaje = this.parentElement.querySelector('.mensaje-exito');

                fetch(`/inventario/actualizar-stock/${productoId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ stock: nuevoStock })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success && mensaje) {
                        mensaje.classList.remove('d-none');
                        setTimeout(() => {
                            mensaje.classList.add('d-none');
                        }, 1500);
                    } else {
                        alert('Error al actualizar el stock.');
                    }
                })
                .catch(error => {
                    console.error('Error en la petición:', error);
                    alert('Error al conectar con el servidor.');
                });
            });
        });
    });
</script>
@endpush

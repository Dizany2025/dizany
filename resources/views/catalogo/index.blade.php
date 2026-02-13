@extends('layouts.catalogo')

@section('title', 'Catálogo DIZANY')

@section('content')

<h4 class="mb-4">Nuestros Productos</h4>

<div class="row g-4">

@foreach($productos as $producto)

@php
    $stock = $producto->lotes->sum('stock_actual');

    $loteActivo = $producto->lotes
        ->where('stock_actual', '>', 0)
        ->sortBy('fecha_vencimiento')
        ->first();

    $precio = $loteActivo->precio_unidad ?? 0;
@endphp

<div class="col-6 col-md-4 col-lg-3">
    <div class="card shadow-sm border-0 h-100 product-card">

        <img src="{{ asset('uploads/productos/'.$producto->imagen) }}"
             class="card-img-top p-3"
             style="height:180px;object-fit:contain;">

        <div class="card-body text-center">

            <h6 class="fw-bold">{{ $producto->nombre }}</h6>

            <div class="text-success fw-bold fs-5">
                S/ {{ number_format($precio,2) }}
            </div>

            @if($stock > 0)
                <span class="badge bg-success mt-2">
                    Disponible ({{ $stock }})
                </span>

                <div class="mt-3 d-grid gap-2">

                    <button class="btn btn-dark btn-sm agregar-carrito"
                            data-id="{{ $producto->id }}"
                            data-nombre="{{ $producto->nombre }}"
                            data-precio="{{ $precio }}">
                        Agregar al carrito
                    </button>

                    <a target="_blank"
                       href="https://wa.me/51958196510?text=Hola quiero comprar {{ urlencode($producto->nombre) }}"
                       class="btn btn-success btn-sm">
                        Comprar por WhatsApp
                    </a>

                </div>

            @else
                <span class="badge bg-danger mt-2">
                    Sin stock
                </span>
            @endif

        </div>
    </div>
</div>

@endforeach

</div>

@endsection


@push('scripts')
<script>

let carrito = JSON.parse(localStorage.getItem('carrito')) || [];

function actualizarContador() {
    const total = carrito.reduce((acc, item) => acc + item.cantidad, 0);
    document.getElementById('contador-carrito').innerText = total;
}

actualizarContador();

document.querySelectorAll('.agregar-carrito').forEach(btn => {

    btn.addEventListener('click', () => {

        const id = btn.dataset.id;
        const nombre = btn.dataset.nombre;
        const precio = parseFloat(btn.dataset.precio);

        const existe = carrito.find(p => p.id == id);

        if (existe) {
            existe.cantidad++;
        } else {
            carrito.push({
                id,
                nombre,
                precio,
                cantidad: 1
            });
        }

        localStorage.setItem('carrito', JSON.stringify(carrito));
        actualizarContador();

        Swal.fire({
            icon: 'success',
            title: 'Producto agregado',
            timer: 1000,
            showConfirmButton: false
        });

    });

});

</script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush

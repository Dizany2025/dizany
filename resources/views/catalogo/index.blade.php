@extends('layouts.catalogo')

@section('title', 'Catálogo DIZANY')

@push('styles')
    <link href="{{ asset('css/catalago/catalago.css') }}" rel="stylesheet" />
@endpush

@section('content')

<div class="catalog-header">

    <div class="container">

        <div class="header-wrapper">

            <!-- BLOQUE IZQUIERDO -->
            <div class="store-block">

                <div class="brand-row">
                    @if(!empty($config->logo))
                        <img src="{{ asset('uploads/config/' . $config->logo) }}"
                             class="catalog-logo">
                    @endif

                    <div>
                        <h2>{{ $config->nombre_empresa }}</h2>
                        <div class="rubro">{{ $config->rubro }}</div>
                    </div>
                </div>

                <div class="store-details">
                    <div>📍 {{ $config->direccion }}</div>
                    <div>🕒 Abierto 8:00 a.m. - 11:00 p.m.</div>
                </div>

            </div>

            <!-- BUSCADOR -->
            <div class="search-block">
                <input type="text"
                       id="searchInput"
                       class="search-input"
                       placeholder="Buscar producto...">
            </div>

            <!-- CARRITO -->
            <div class="cart-block">
                <div class="cart-button">
                    <i class="fas fa-shopping-cart"></i>
                    <span id="contador-carrito" class="cart-badge">0</span>
                </div>
            </div>

        </div>

    </div>

</div>


<div class="container-fluid px-4 py-4">

    <div class="text-center category-filter mb-4">
        <button class="btn btn-outline-dark active" onclick="filterCategory('all')">Todos</button>
        @foreach($categorias as $categoria)
            <button class="btn btn-outline-dark"
                    onclick="filterCategory('{{ $categoria->id }}')">
                {{ $categoria->nombre }}
            </button>
        @endforeach
    </div>

    <div class="row" id="productContainer">
        @foreach($productos as $producto)

        @php
            $stock = $producto->stock_total ?? 0;
        @endphp

        <div class="col-md-3 col-sm-6 mb-4 product-item"
             data-name="{{ strtolower($producto->nombre) }}"
             data-category="{{ $producto->categoria_id }}">

            <div class="card product-card h-100 text-center p-3">

                <img src="{{ asset('uploads/productos/' . $producto->imagen) }}"
                     class="product-img mb-3"
                     alt="{{ $producto->nombre }}">

                <h5>{{ $producto->nombre }}</h5>

                <div class="price mb-2">
                    S/ {{ number_format($producto->precio_venta ?? 0, 2) }}
                </div>

                @if($stock > 0)
                    <span class="badge bg-success stock-badge">
                        Disponible ({{ $stock }})
                    </span>
                @else
                    <span class="badge bg-danger stock-badge">
                        Sin stock
                    </span>
                @endif

                @if($stock > 0)
                <a href="https://wa.me/{{ $config->telefono ?? '51958196510' }}?text=Hola,%20quiero%20comprar%20{{ urlencode($producto->nombre) }}"
                    target="_blank"
                    class="btn whatsapp-btn w-100 text-white">
                    {{ $config->texto_boton_whatsapp ?? 'Comprar por WhatsApp' }}
                </a>
                @endif

            </div>
        </div>

        @endforeach
    </div>
</div>

@endsection

@push('scripts')
<script>

// 🔎 Buscador en vivo
document.getElementById('searchInput').addEventListener('keyup', function() {
    let value = this.value.toLowerCase();
    document.querySelectorAll('.product-item').forEach(item => {
        let name = item.dataset.name;
        item.style.display = name.includes(value) ? 'block' : 'none';
    });
});

// 🏷️ Filtro por categoría
function filterCategory(categoryId) {
    document.querySelectorAll('.product-item').forEach(item => {
        if (categoryId === 'all') {
            item.style.display = 'block';
        } else {
            item.style.display =
                item.dataset.category == categoryId ? 'block' : 'none';
        }
    });
}

</script>
@endpush

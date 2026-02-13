@extends('layouts.catalogo')

@section('title', 'Catálogo DIZANY')

@push('styles')
    <style>
        .hero {
            background: linear-gradient(135deg, #0f172a, #1e293b);
            color: white;
            padding: 60px 20px;
            text-align: center;
        }

        .hero h1 {
            font-weight: 700;
            font-size: 2.5rem;
        }

        .search-box {
            max-width: 500px;
            margin: 20px auto;
        }

        .product-card {
            border-radius: 15px;
            transition: 0.3s ease;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }


        .product-img {
            width: 100%;
            height: 220px;
            object-fit: cover;   /* llena el espacio */
            border-radius: 10px;
        }

        .price {
            font-weight: bold;
            color: #16a34a;
            font-size: 1.2rem;
        }

        .stock-badge {
            font-size: 0.8rem;
        }

        .category-filter button {
            border-radius: 50px;
            margin: 5px;
        }

        .whatsapp-btn {
            background: #25D366;
            border: none;
        }

        .whatsapp-btn:hover {
            background: #1ebe5d;
        }
    </style>
@endpush

@section('content')


<div class="hero">
    <h1>{{ $config->nombre_empresa }}</h1>
    <p>Compra fácil y rápido por WhatsApp</p>

    <div class="search-box">
        <input type="text" id="searchInput" class="form-control form-control-lg"
               placeholder="Buscar producto...">
    </div>
</div>

<div class="container py-4">

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
                <div class="mt-3">
                    <a href="https://wa.me/51958196510?text=Hola,%20quiero%20comprar%20{{ urlencode($producto->nombre) }}"
                       target="_blank"
                       class="btn whatsapp-btn w-100 text-white">
                        Comprar por WhatsApp
                    </a>
                </div>
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

// ============================
// TOTALES / CÁLCULOS GLOBALES
// ============================

function calcularSubtotal() {
    const v = ventaActiva();
    return (v.productos || []).reduce(
        (s, it) =>
            s +
            (parseFloat(it.precio_unitario || 0) *
             (parseInt(it.cantidad) || 0)),
        0
    );
}

function obtenerIGVPercent() {
    const el = document.getElementById("igv-config");
    const val = el ? parseFloat(el.value) : 0;
    return isNaN(val) ? 0 : val;
}

function calcularTotal() {
    const subtotal = calcularSubtotal();
    const igvPercent = obtenerIGVPercent();
    const igv = subtotal * igvPercent / 100;

    return {
        subtotal,
        igv,
        total: subtotal + igv,
        igvPercent
    };
}

// 👉 EXPONER TOTALES
window.calcularTotal = calcularTotal;

// ===============================
// CARRITO / ITEMS / CANTIDADES
// ===============================

document.addEventListener("DOMContentLoaded", () => {

    // ============================
    // ESTILOS INPUT CANTIDAD (SIN FLECHAS)
    // ============================
    (function injectInputCantidadStyles() {
        if (document.getElementById("input-cantidad-style")) return;

        const st = document.createElement("style");
        st.id = "input-cantidad-style";
        st.innerHTML = `
            /* Quitar flechas de input number */
            input[type=number]::-webkit-inner-spin-button,
            input[type=number]::-webkit-outer-spin-button {
                -webkit-appearance: none;
                margin: 0;
            }

            input[type=number] {
                -moz-appearance: textfield;
            }
        `;
        document.head.appendChild(st);
    })();


    // ============================
    // ELEMENTOS
    // ============================
    const carritoLista = document.getElementById("carrito-lista");
    const buscarInput  = document.getElementById("buscar_producto");
    const btnIrStep2   = document.getElementById("btn-ir-step2");

    // ============================
    // AGREGAR PRODUCTO A VENTA ACTIVA
    // ============================
    function agregarProductoAVentaActiva(producto) {

        const v = ventaActiva();

        const item = {
            id: Number(producto.id),
            nombre: producto.nombre,
            imagen: producto.imagen || "",
            descripcion: producto.descripcion || "",
            stock: parseInt(producto.stock) || 0,

            cantidad: 1,
            tipo_venta: "unidad",
            precio_unitario: parseFloat(producto.precio_venta || 0),

            precio_venta: parseFloat(producto.precio_venta || 0),
            precio_paquete: parseFloat(producto.precio_paquete || 0),
            precio_caja: parseFloat(producto.precio_caja || 0),

            unidades_por_paquete: producto.unidades_por_paquete
                ? parseInt(producto.unidades_por_paquete)
                : 0,

            paquetes_por_caja: producto.paquetes_por_caja
                ? parseInt(producto.paquetes_por_caja)
                : 0
        };

        const prodActual =
            productosCache.get(item.id) || producto;

        if (stockDisponible(prodActual) < unidadesRealesDeItem(item)) {
            return mostrarAlerta("No hay stock suficiente.");
        }

        v.productos.push(item);

        posSaveDebounced(snapshotPOS, 10);
        renderTodo();
    }

    // ============================
    // COLOR BADGE STOCK
    // ============================
    function getStockBadgeColor(stock) {
        if (stock >= 20) return "bg-success";
        if (stock >= 6) return "bg-warning";
        return "bg-danger";
    }

    // ============================
    // RENDER CARRITO
    // ============================
    function renderCarritoTreinta() {
        if (!carritoLista) return;

        const v = ventaActiva();
        const items = v.productos || [];

        carritoLista.innerHTML = "";

        if (!items.length) {
            const btnActivo = document.querySelector(".btn-filtro-categoria.active");
            const nombreCat = btnActivo ? btnActivo.textContent.trim() : "productos";
            let textoCategoria = "tu catálogo";
            if (nombreCat && nombreCat.toLowerCase() !== "todos") textoCategoria = `la categoría ${nombreCat.toLowerCase()}`;

            carritoLista.innerHTML = `
                <div class="empty-cart-premium text-center py-5">
                    
                    <!-- ILUSTRACIÓN SVG -->
                    <div class="empty-illustration mb-3">
                        <svg viewBox="0 0 140 100" class="empty-svg">
                            <!-- Fondo círculo -->
                            <defs>
                                <linearGradient id="gradCart" x1="0%" y1="0%" x2="100%" y2="100%">
                                    <stop offset="0%" stop-color="#4A90E2"/>
                                    <stop offset="100%" stop-color="#6C5CE7"/>
                                </linearGradient>
                            </defs>
                            <circle cx="70" cy="40" r="36" fill="url(#gradCart)" opacity="0.15"/>

                            <!-- Carrito -->
                            <rect x="30" y="32" width="60" height="26" rx="6" ry="6" fill="#ffffff" stroke="#4A90E2" stroke-width="2"/>
                            <path d="M32 32 L26 20" stroke="#4A90E2" stroke-width="2" stroke-linecap="round"/>
                            <path d="M88 32 L96 20" stroke="#4A90E2" stroke-width="2" stroke-linecap="round"/>

                            <!-- Cajas dentro -->
                            <rect x="38" y="26" width="12" height="10" rx="2" fill="#4A90E2" opacity="0.9"/>
                            <rect x="54" y="24" width="12" height="12" rx="2" fill="#6C5CE7" opacity="0.9"/>
                            <rect x="70" y="27" width="12" height="9"  rx="2" fill="#00B894" opacity="0.9"/>

                            <!-- Ruedas -->
                            <circle cx="44" cy="62" r="5" fill="#ffffff" stroke="#4A90E2" stroke-width="2"/>
                            <circle cx="76" cy="62" r="5" fill="#ffffff" stroke="#4A90E2" stroke-width="2"/>

                            <!-- Brillito -->
                            <path d="M96 25 Q104 20 108 26" stroke="#4A90E2" stroke-width="2" stroke-linecap="round" opacity="0.6"/>
                        </svg>
                    </div>

                    <h5 class="fw-bold text-dark mb-1">Tu carrito está vacío</h5>
                    <p class="text-muted small mb-2">
                        Agrega productos desde <strong>${textoCategoria}</strong> para iniciar tu venta.
                    </p>

                    <button type="button" class="btn btn-primary btn-sm shadow-sm btn-empezar-compra">
                        <i class="fas fa-search"></i> Empezar a buscar productos
                    </button>
                </div>
            `;

            // Opcional: cuando hacen clic en el botón, enfocar el buscador
            const btnBuscar = carritoLista.querySelector(".btn-empezar-compra");
            if (btnBuscar && buscarInput) {
                btnBuscar.addEventListener("click", () => {
                    buscarInput.focus();
                    buscarInput.scrollIntoView({ behavior: "smooth", block: "center" });
                });
            }

            actualizarResumen();
            actualizarBotonCarrito();
            return;
        }

        items.forEach((p, index) => {
            const imgSrc = p.imagen ? `/uploads/productos/${p.imagen}` : "/img/sin-imagen.png";

            const precioUnitFinal = calcularPrecioFinal(p.precio_unitario);
            const subtotal = precioUnitFinal * (parseInt(p.cantidad) || 0);

            const unidades = unidadesRealesDeItem(p);
            const prodActual = productosCache.get(Number(p.id));
            const stockReal = prodActual ? (parseInt(prodActual.stock) || 0) : (parseInt(p.stock) || 0);

            const card = `
                <div class="carrito-item border-bottom pb-3 mb-3" data-index="${index}">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="d-flex align-items-start gap-2">
                            <img src="${imgSrc}" alt="${p.nombre}" class="carrito-thumb">
                            <div>
                                <div class="d-flex justify-content-between align-items-center" style="min-width:200px;">
                                    <span class="fw-semibold small">${p.nombre}</span>
                                    <span class="badge ${getStockBadgeColor(stockReal)} ms-2">Stock: ${stockReal}</span>
                                </div>
                                <div class="text-muted extra-small">${p.descripcion || ""}</div>
                            </div>
                        </div>
                        <button class="btn btn-outline-danger btn-sm rounded-circle eliminar-item" data-index="${index}">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>

                    <div class="d-flex align-items-center mt-2 gap-2">
                        <div class="flex-grow-1">
                            <span class="d-block extra-small text-muted mb-1">Tipo venta</span>
                            <select class="form-select form-select-sm tipo-venta" data-index="${index}">
                                <option value="unidad" ${p.tipo_venta === "unidad" ? "selected" : ""}>Unidad</option>
                                ${
                                    p.unidades_por_paquete > 0 && p.precio_paquete > 0
                                        ? `<option value="paquete" ${p.tipo_venta === "paquete" ? "selected" : ""}>Paquete (${p.unidades_por_paquete})</option>`
                                        : ""
                                }
                                ${
                                    p.precio_caja > 0
                                        ? (() => {
                                            let texto = "Caja";
                                            if (p.paquetes_por_caja > 0 && p.unidades_por_paquete > 0) {
                                                texto = `Caja (${p.paquetes_por_caja * p.unidades_por_paquete} und.)`;
                                            } else if (p.unidades_por_paquete > 0) {
                                                texto = `Caja (${p.unidades_por_paquete} und.)`;
                                            }
                                            return `<option value="caja" ${p.tipo_venta === "caja" ? "selected" : ""}>${texto}</option>`;
                                        })()
                                        : ""
                                }
                            </select>
                        </div>

                        <div class="d-flex align-items-center gap-1">
                            <button class="btn btn-light btn-sm btn-restar" data-index="${index}">−</button>
                            <input type="number" min="1" class="form-control form-control-sm text-center cambiar-cantidad"
                                data-index="${index}" value="${p.cantidad}">
                            <button class="btn btn-light btn-sm btn-sumar" data-index="${index}">+</button>
                        </div>

                        <div class="text-end" style="width:90px;">
                            <div class="fw-semibold small">S/ ${precioUnitFinal.toFixed(2)}</div>
                        </div>
                    </div>

                    <div class="mt-2 small">
                        <span class="text-muted">Precio por <strong>${unidades}</strong> unidades:</span>
                        <span class="fw-semibold"> S/ ${subtotal.toFixed(2)}</span>
                    </div>
                </div>
            `;
            carritoLista.insertAdjacentHTML("beforeend", card);
        });

        actualizarResumen();
        actualizarBotonCarrito();
    }

    // ============================
    // Carrito eventos (delegación) + validación stock con reservas
    // ============================
    if (carritoLista) {

        carritoLista.addEventListener("change", (e) => {
            if (!e.target.classList.contains("tipo-venta")) return;

            const v = ventaActiva();
            const i = Number(e.target.dataset.index);
            const it = v.productos[i];
            if (!it) return;

            const oldUnits = unidadesRealesDeItem(it);

            const nuevo = e.target.value;
            it.tipo_venta = nuevo;

            let precioBase = 0;
            if (nuevo === "unidad")  precioBase = parseFloat(it.precio_venta || 0);
            if (nuevo === "paquete") precioBase = parseFloat(it.precio_paquete || 0);
            if (nuevo === "caja")    precioBase = parseFloat(it.precio_caja || 0);

            it.precio_unitario = precioBase;
            it.cantidad = 1;

            const stockReal = stockRealProducto(it);
            const newUnits = unidadesRealesDeItem(it);

            if (newUnits > stockReal) {
                mostrarAlerta("Stock insuficiente para esta presentación");
                it.tipo_venta = "unidad";
                it.precio_unitario = parseFloat(it.precio_venta || 0);
                it.cantidad = 1;
            }

            renderTodo();
        });


        carritoLista.addEventListener("blur", (e) => {
            if (!e.target.classList.contains("cambiar-cantidad")) return;

            const v = ventaActiva();
            const i = Number(e.target.dataset.index);
            const it = v.productos[i];
            if (!it) return;

            let cant = parseInt(e.target.value);
            if (isNaN(cant) || cant < 1) cant = 1;

            it.cantidad = cant;

            const stockReal = stockRealProducto(it);
            const newUnits = unidadesRealesDeItem(it);

            if (newUnits > stockReal) {
                mostrarAlerta("Cantidad excede el stock disponible");
                it.cantidad = 1;
                e.target.value = 1;
            }

            renderTodo();
        }, true);

        carritoLista.addEventListener("click", (e) => {
            const v = ventaActiva();

            const btnSumar = e.target.closest(".btn-sumar");
            const btnRestar = e.target.closest(".btn-restar");
            const btnEliminar = e.target.closest(".eliminar-item");

            if (btnSumar) {
                const i = Number(btnSumar.dataset.index);
                const it = v.productos[i];
                if (!it) return;

                it.cantidad = (parseInt(it.cantidad) || 1) + 1;

                const stockReal = stockRealProducto(it);
                const newUnits = unidadesRealesDeItem(it);

                if (newUnits > stockReal) {
                    mostrarAlerta("No hay stock suficiente");
                    it.cantidad--;
                }

                renderTodo();
                return;
            }

            if (btnRestar) {
                const i = Number(btnRestar.dataset.index);
                const it = v.productos[i];
                if (!it) return;

                const c = parseInt(it.cantidad) || 1;
                if (c > 1) it.cantidad = c - 1;

                renderTodo();
                return;
            }

            if (btnEliminar) {
                const i = Number(btnEliminar.dataset.index);
                v.productos.splice(i, 1);
                renderTodo();
                return;
            }
        });

        // 👇 AQUÍ MISMO PEGAS ESTO
        carritoLista.addEventListener("keydown", (e) => {
            if (!e.target.classList.contains("cambiar-cantidad")) return;

            const teclasPermitidas = [
                "Backspace",
                "Delete",
                "ArrowLeft",
                "ArrowRight",
                "Tab"
            ];

            // permitir control básico
            if (teclasPermitidas.includes(e.key)) return;

            // permitir solo números
            if (!/^[0-9]$/.test(e.key)) {
                e.preventDefault(); // 🔥 no cambia el valor actual
            }
        });

    }

    // Vaciar canasta (solo venta activa)
    const btnVaciarCanasta = document.getElementById("vaciar-canasta");
    if (btnVaciarCanasta) {
        btnVaciarCanasta.addEventListener("click", (e) => {
            e.preventDefault();

            const v = ventaActiva();
            if (!v.productos.length) return;

            Swal.fire({
                icon: "question",
                title: "Vaciar canasta",
                text: "¿Seguro que deseas eliminar todos los productos?",
                showCancelButton: true,
                confirmButtonText: "Sí, vaciar",
                cancelButtonText: "Cancelar"
            }).then(r => {
                if (!r.isConfirmed) return;
                v.productos = [];
                renderTodo();
            });
        });
    }
    // ============================
    // EXPONER
    // ============================
    window.agregarProductoAVentaActiva = agregarProductoAVentaActiva;
    window.renderCarritoTreinta = renderCarritoTreinta;

});

function validarStockVentaActiva() {
    const v = ventaActiva();
    if (!v || !v.productos) return true;

    for (const it of v.productos) {
        const prod = productosCache.get(Number(it.id));
        const stockReal = prod
            ? parseInt(prod.stock) || 0
            : parseInt(it.stock) || 0;

        const unidadesNecesarias = unidadesRealesDeItem(it);

        if (unidadesNecesarias > stockReal) {
            mostrarAlerta(
                `El producto "${it.nombre}" se quedó sin stock disponible.`
            );
            return false;
        }
    }

    return true;
}

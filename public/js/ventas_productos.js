// ===============================
// IGV global desde configuración
// ===============================
const igvConfigInput = document.getElementById("igv-config");
const IGV_PERCENT = igvConfigInput ? parseFloat(igvConfigInput.value) || 0 : 0;
console.log("IGV leído:", IGV_PERCENT);

// Helper: dado un precio base (sin IGV) devuelve el precio final (con IGV)
function calcularPrecioFinal(precioBase) {
    const base = parseFloat(precioBase) || 0;
    if (IGV_PERCENT <= 0) return base;
    return base * (1 + IGV_PERCENT / 100);
}

document.addEventListener("DOMContentLoaded", () => {

    // ============================
    //   ELEMENTOS PRINCIPALES
    // ============================
    const modalOrdenarEl = document.getElementById("modalOrdenar");
    const modalOrdenar   = (window.bootstrap && modalOrdenarEl)
        ? new bootstrap.Modal(modalOrdenarEl)
        : null;

    const buscarInput   = document.getElementById("buscar_producto");
    const resultadosDiv = document.getElementById("resultados-busqueda"); // grilla de cards

    // Carrito Treinta (fase 1)
    const carritoLista  = document.getElementById("carrito-lista");

    // Botones FASES
    const btnIrStep2     = document.getElementById("btn-ir-step2");
    const btnVolverStep1 = document.getElementById("btn-volver-step1") || document.getElementById("btn-volver-carrito");
    const btnIrStep3     = document.getElementById("btn-ir-step3")      || document.getElementById("btn-ir-vuelto");
    const btnVolverStep2 = document.getElementById("btn-volver-step2")  || document.getElementById("btn-vuelto-atras");
    const btnConfirmar3  = document.getElementById("btn-confirmar-venta");

    const step1 = document.getElementById("step-1");
    const step2 = document.getElementById("step-2");
    const step3 = document.getElementById("step-3");

    // Modal de venta exitosa (si existe)
    let modalVentaExitosa = null;
    const modalVentaExitosaElement = document.getElementById('modalVentaExitosa');
    if (modalVentaExitosaElement && window.bootstrap) {
        modalVentaExitosa = new bootstrap.Modal(modalVentaExitosaElement);
    }

    // Otros datos
    const documentoInput   = document.getElementById('documento');
    const metodoPagoSelect = document.getElementById("estado_pago"); // select pagado/pendiente

    // Botones modal venta exitosa
    const btnImprimir   = document.getElementById("btnImprimir");
    const btnDescargar  = document.getElementById("btn-descargar");
    const btnNuevaVenta = document.getElementById("btnNuevaVenta");

    // Inputs FASE 3 (vuelto)
    const inputTotalVenta = document.getElementById("vuelto-total-venta");
    const inputPaga       = document.getElementById("vuelto-paga");
    const inputVuelto     = document.getElementById("vuelto-mostrar");

    // Sonidos
    const sonidoError = new Audio('/sonidos/error-alert.mp3');
    const sonidoExito = new Audio('/sonidos/success.mp3');

    // Evitar Enter en documento y buscar
    if (documentoInput) {
        documentoInput.addEventListener('keydown', e => e.key === 'Enter' && e.preventDefault());
    }
    if (buscarInput) {
        buscarInput.addEventListener('keydown', e => e.key === 'Enter' && e.preventDefault());
    }

    // Carrito (datos en memoria)
    let productosSeleccionados = [];

    // Cache de productos que se van mostrando en cards
    const productosCache = new Map(); // id => producto

    // ============================
    //   SERIE Y CORRELATIVO
    // ============================
    const tipoComprobanteSelect = document.getElementById("tipo_comprobante");
    const inputSerieCorrelativo = document.getElementById("serie_correlativo");

    if (tipoComprobanteSelect && inputSerieCorrelativo) {
        tipoComprobanteSelect.addEventListener("change", () => {
            fetch(`/ventas/obtener-serie-correlativo?tipo=${tipoComprobanteSelect.value}`)
                .then(res => res.json())
                .then(data => {
                    if (data.serie && data.correlativo) {
                        inputSerieCorrelativo.value =
                            `${data.serie}-${String(data.correlativo).padStart(6, '0')}`;
                    }
                })
                .catch(() => console.error("Error al obtener serie y correlativo"));
        });

        // Disparar al inicio
        tipoComprobanteSelect.dispatchEvent(new Event("change"));
    }

    // ============================
    //   HELPERS PARA GRILLA
    // ============================

    function cacheProductos(lista) {
        if (!Array.isArray(lista)) return;
        lista.forEach(p => {
            if (p && p.id != null) {
                productosCache.set(Number(p.id), p);
            }
        });
    }

    function crearCardProducto(prod) {
        let nombreImagen = String(prod.imagen || '').trim();

        // Sanitizar nombre de imagen
        if (
            nombreImagen.includes('<') ||
            nombreImagen.includes('>') ||
            nombreImagen.includes('=') ||
            nombreImagen.includes('"') ||
            nombreImagen.includes("'")
        ) {
            nombreImagen = '';
        }

        const imgSrc = nombreImagen
            ? `/uploads/productos/${nombreImagen}`
            : '/img/sin-imagen.png';

        const precioBase  = parseFloat(prod.precio_venta || 0) || 0;
        const precioFinal = calcularPrecioFinal(precioBase).toFixed(2);
        const descCorta =
            prod.descripcion && prod.descripcion.length > 35
                ? prod.descripcion.substring(0, 35) + "..."
                : (prod.descripcion || "");

        const stockClass = prod.stock > 0 ? 'stock-ok' : 'stock-low';
        const stockText  = prod.stock > 0 ? `${prod.stock} disponibles` : 'Sin stock';

        return `
        <div class="col-6 col-md-4 col-xl-3 mb-3">
            <div class="product-card agregar-carrito" data-id="${prod.id}">
                <div class="product-img-wrapper">
                    <img src="${imgSrc}" alt="${prod.nombre}" class="product-img">
                    <span class="product-price-badge">S/ ${precioFinal}</span>
                </div>
                <div class="product-info">
                    ${IGV_PERCENT > 0
                        ? '<small class="text-success fw-bold d-block" style="font-size: 12px;">Incl. IGV</small>'
                        : ''}
                    <div class="product-name" title="${prod.nombre}">${prod.nombre}</div>
                    <div class="product-desc" title="${prod.descripcion || ''}">
                        ${descCorta || '&nbsp;'}
                    </div>
                    <div class="product-stock ${stockClass}">
                        <i class="fas fa-box-open"></i> ${stockText}
                    </div>
                </div>
            </div>
        </div>
        `;
    }

    function renderGrillaProductos(lista) {
        if (!resultadosDiv) return;

        resultadosDiv.classList.remove("d-none");
        resultadosDiv.innerHTML = "";

        // --- TARJETA CREAR PRODUCTO (SIEMPRE PRIMERA) ---
        if (window.USUARIO_ES_ADMIN) {
            resultadosDiv.insertAdjacentHTML("beforeend", `
                <div class="col-6 col-md-4 col-xl-3 mb-3">
                    <div class="product-card crear-producto-card">
                        <div class="crear-producto-center">
                            <div class="crear-producto-icon">+</div>
                            <span>Crear producto</span>
                        </div>
                    </div>
                </div>
            `);
        }

        // Hacer visible la tarjeta de crear (animación)
        const cardCrear = resultadosDiv.querySelector(".crear-producto-card");
            if (cardCrear) {
                cardCrear.classList.add("show");

                if (window.USUARIO_ES_ADMIN) {
                    cardCrear.addEventListener("click", () => {
                        window.location.href = "/productos/create";
                    });
                } else {
                    cardCrear.style.pointerEvents = "none"; // Extra seguridad
                }
            }

        // --- SI NO HAY PRODUCTOS ---
        if (!lista || lista.length === 0) {
            resultadosDiv.insertAdjacentHTML("beforeend", `
                <div class="col-12 text-center text-muted py-3">
                    No se encontraron productos
                </div>
            `);
            return;
        }

        cacheProductos(lista);

        lista.forEach((prod, index) => {
            resultadosDiv.insertAdjacentHTML("beforeend", crearCardProducto(prod));

            const col = resultadosDiv.lastElementChild;        // <div class="col-6 ...">
            const card = col.querySelector(".product-card");   // tarjeta real

            card.style.transitionDelay = (index * 0.02) + "s";

            requestAnimationFrame(() => {
                card.classList.add("show");
            });
        });

        // Eventos de agregar al carrito SOLO para las cards de productos
        resultadosDiv.querySelectorAll(".product-card.agregar-carrito").forEach(card => {
            card.addEventListener("click", () => {
                const id = Number(card.dataset.id);
                const prod = productosCache.get(id);
                if (!prod) {
                    mostrarAlerta("No se pudo obtener la información del producto.");
                    return;
                }

                if (prod.stock <= 0) {
                    mostrarAlerta(`No hay stock para "${prod.nombre}".`);
                    return;
                }

                const yaExiste = productosSeleccionados.find(p => p.id === prod.id);
                if (yaExiste) {
                    mostrarAlerta(`El producto "${prod.nombre}" ya está en la canasta.`);
                    return;
                }

                agregarProducto(prod);
            });
        });
    }


    // Mostrar productos iniciales al cargar (si existen declarados en Blade)
    if (window.PRODUCTOS_INICIALES && Array.isArray(window.PRODUCTOS_INICIALES)) {
        renderGrillaProductos(window.PRODUCTOS_INICIALES);
    }

    // ============================
    //   ACTUALIZAR STOCK DESPUÉS DE VENTA
    // ============================

    function actualizarProductosStock() {
        if (!resultadosDiv) return;

        fetch('/productos/iniciales')
            .then(res => res.json())
            .then(lista => {
                if (Array.isArray(lista)) {
                    window.PRODUCTOS_INICIALES = lista;
                    renderGrillaProductos(lista);
                }
            })
            .catch(() => {
                if (window.PRODUCTOS_INICIALES && Array.isArray(window.PRODUCTOS_INICIALES)) {
                    renderGrillaProductos(window.PRODUCTOS_INICIALES);
                }
            });
    }

    // ============================
    //   FILTRO POR CATEGORÍA
    // ============================

    const botonesCategorias = document.querySelectorAll(".btn-filtro-categoria");

    if (botonesCategorias.length > 0) {
        botonesCategorias.forEach(btn => {
            btn.addEventListener("click", () => {

                botonesCategorias.forEach(b => b.classList.remove("active"));
                btn.classList.add("active");

                const catID = Number(btn.dataset.cat);

                if (catID === 0) {
                    if (window.PRODUCTOS_INICIALES && Array.isArray(window.PRODUCTOS_INICIALES)) {
                        renderGrillaProductos(window.PRODUCTOS_INICIALES);
                    }
                    return;
                }

                if (!window.PRODUCTOS_INICIALES || !Array.isArray(window.PRODUCTOS_INICIALES)) {
                    return;
                }

                const filtrados = window.PRODUCTOS_INICIALES.filter(prod =>
                    Number(prod.categoria_id) === catID
                );

                renderGrillaProductos(filtrados);
            });
        });
    }

    // ============================
    //   BUSCAR PRODUCTO (AJAX)
    // ============================

    if (buscarInput) {
        buscarInput.addEventListener("input", () => {
            const query = buscarInput.value.trim();

            if (query.length === 0) {
                // Si hay categoría activa, respetarla
                const btnActivo = document.querySelector(".btn-filtro-categoria.active");
                const catID = btnActivo ? Number(btnActivo.dataset.cat) : 0;

                if (window.PRODUCTOS_INICIALES && Array.isArray(window.PRODUCTOS_INICIALES)) {
                    if (catID === 0) {
                        renderGrillaProductos(window.PRODUCTOS_INICIALES);
                    } else {
                        const filtrados = window.PRODUCTOS_INICIALES.filter(prod =>
                            Number(prod.categoria_id) === catID
                        );
                        renderGrillaProductos(filtrados);
                    }
                } else {
                    resultadosDiv.innerHTML = "";
                    resultadosDiv.classList.add("d-none");
                }
                return;
            }

            fetch(`/buscar-producto?search=${encodeURIComponent(query)}`)
                .then(res => res.json())
                .then(productos => {
                    renderGrillaProductos(productos);
                })
                .catch(() => {
                    mostrarAlerta("Error al buscar productos");
                });
        });
    }

    // ============================
    //   AGREGAR PRODUCTO AL CARRITO
    // ============================

    function agregarProducto(producto) {
        productosSeleccionados.push({
            ...producto,
            cantidad: 1,
            tipo_venta: "unidad",
            precio_unitario: parseFloat(producto.precio_venta),

            // CAMPOS RELACIONADOS A PAQUETE / CAJA
            precio_venta: parseFloat(producto.precio_venta),
            precio_paquete: parseFloat(producto.precio_paquete || 0),
            precio_caja: parseFloat(producto.precio_caja || 0),

            unidades_por_paquete: parseInt(producto.unidades_por_paquete || 1),
            paquetes_por_caja: parseInt(producto.paquetes_por_caja || 1),

            producto_original: producto
        });

        renderCarritoTreinta();
    }

    // ============================
    //   RENDERIZAR CARRITO TIPO TREINTA
    // ============================

    function unidadesReales(p, cantidad) {
        if (p.tipo_venta === "unidad") {
            return cantidad;
        }
        if (p.tipo_venta === "paquete") {
            return cantidad * p.unidades_por_paquete;
        }
        if (p.tipo_venta === "caja") {
            return cantidad * p.unidades_por_paquete * p.paquetes_por_caja;
        }
        return cantidad;
    }

    // Helper color badge stock
    function getStockBadgeColor(stock) {
        if (stock >= 20) return "bg-success";   // verde
        if (stock >= 6)  return "bg-warning";   // amarillo
        return "bg-danger";                     // rojo
    }

    function renderCarritoTreinta() {
        if (!carritoLista) return;

        carritoLista.innerHTML = "";

        if (productosSeleccionados.length === 0) {

            // Detectar categoría activa en los filtros de arriba
            const btnActivo = document.querySelector(".btn-filtro-categoria.active");
            let nombreCat   = btnActivo ? btnActivo.textContent.trim() : "productos";
            
            // Mensaje según categoría
            let textoCategoria = "tu catálogo";
            if (nombreCat && nombreCat.toLowerCase() !== "todos") {
                textoCategoria = `la categoría ${nombreCat.toLowerCase()}`;
            }

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

        productosSeleccionados.forEach((p, index) => {

            const imgSrc = p.imagen
                ? `/uploads/productos/${p.imagen}`
                : '/img/sin-imagen.png';

            const precioUnitFinal = calcularPrecioFinal(p.precio_unitario);
            const subtotal        = precioUnitFinal * p.cantidad;

            const unidades = unidadesReales(p, p.cantidad);

            const card = `
                <div class="carrito-item border-bottom pb-3 mb-3" data-index="${index}">
                    
                    <div class="d-flex justify-content-between align-items-start">

                        <div class="d-flex align-items-start gap-2">

                            <img src="${imgSrc}" alt="${p.nombre}" class="carrito-thumb">

                            <div>

                                <!-- NOMBRE + BADGE DE STOCK -->
                                <div class="d-flex justify-content-between align-items-center" style="min-width: 200px;">
                                    <span class="fw-semibold small">${p.nombre}</span>
                                    <span class="badge ${getStockBadgeColor(p.stock)} ms-2">
                                        Stock: ${p.stock}
                                    </span>
                                </div>

                                <!-- DESCRIPCIÓN debajo del nombre -->
                                <div class="text-muted extra-small">
                                    ${p.producto_original.descripcion || ""}
                                </div>

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

                                <option value="unidad" ${p.tipo_venta === "unidad" ? "selected" : ""}>
                                    Unidad
                                </option>

                                <option value="paquete" ${p.tipo_venta === "paquete" ? "selected" : ""}>
                                    Paquete (${p.unidades_por_paquete})
                                </option>

                                <option value="caja" ${p.tipo_venta === "caja" ? "selected" : ""}>
                                    Caja (${p.paquetes_por_caja} paquetes)
                                </option>

                            </select>
                        </div>

                        <div class="d-flex align-items-center gap-1">
                            <button class="btn btn-light btn-sm btn-restar" data-index="${index}">−</button>
                            <input type="number"
                                min="1"
                                class="form-control form-control-sm text-center cambiar-cantidad"
                                data-index="${index}"
                                value="${p.cantidad}">
                            <button class="btn btn-light btn-sm btn-sumar" data-index="${index}">+</button>
                        </div>

                        <div class="text-end" style="width: 90px;">
                            <div class="fw-semibold small">S/ ${precioUnitFinal.toFixed(2)}</div>
                        </div>
                    </div>

                    <div class="mt-2 small">
                        <span class="text-muted">
                            Precio por <strong>${unidades}</strong> unidades:
                        </span>
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
    //   EVENTOS DEL CARRITO (delegación)
    // ============================

    if (carritoLista) {

        // CAMBIAR TIPO DE VENTA
        carritoLista.addEventListener("change", e => {
            if (!e.target.classList.contains("tipo-venta")) return;

            const i = e.target.dataset.index;
            const p = productosSeleccionados[i];
            const o = p.producto_original;

            const tipo = e.target.value;
            p.tipo_venta = tipo;

            let precioBase = 0;

            if (tipo === "unidad") {
                precioBase = parseFloat(o.precio_venta || 0);
            } else if (tipo === "paquete") {
                precioBase = parseFloat(o.precio_paquete || 0);
            } else if (tipo === "caja") {
                precioBase = parseFloat(o.precio_caja || 0);
            }

            const precioFinal = calcularPrecioFinal(precioBase);
            p.precio_unitario = parseFloat(precioFinal);

            renderCarritoTreinta();
        });

        // CANTIDAD ESCRITA MANUALMENTE (blur)
        carritoLista.addEventListener("blur", e => {
            if (!e.target.classList.contains("cambiar-cantidad")) return;

            const i = e.target.dataset.index;
            const p = productosSeleccionados[i];

            let cant = parseInt(e.target.value);
            if (isNaN(cant) || cant < 1) cant = 1;

            let unidades = unidadesReales(p, cant);

            if (unidades > p.stock) {
                const divisor =
                    p.tipo_venta === "unidad" ? 1 :
                    p.tipo_venta === "paquete" ? p.unidades_por_paquete :
                    p.unidades_por_paquete * p.paquetes_por_caja;

                const maxCant = Math.floor(p.stock / divisor);

                mostrarAlerta("Cantidad excede el stock disponible.");

                p.cantidad = maxCant || 1;
                e.target.value = p.cantidad;
            } else {
                p.cantidad = cant;
            }

            renderCarritoTreinta();
        }, true);

        // BOTONES + / - / ELIMINAR
        carritoLista.addEventListener("click", e => {
            const btnSumar    = e.target.closest(".btn-sumar");
            const btnRestar   = e.target.closest(".btn-restar");
            const btnEliminar = e.target.closest(".eliminar-item");

            // SUMAR
            if (btnSumar) {
                const i = btnSumar.dataset.index;
                const p = productosSeleccionados[i];

                let nuevaCantidad = p.cantidad + 1;
                let unidades = unidadesReales(p, nuevaCantidad);

                if (unidades > p.stock) {
                    mostrarAlerta("No hay stock suficiente");
                    return;
                }

                p.cantidad = nuevaCantidad;
                renderCarritoTreinta();
                return;
            }

            // RESTAR
            if (btnRestar) {
                const i = btnRestar.dataset.index;
                const p = productosSeleccionados[i];

                if (p.cantidad > 1) {
                    p.cantidad--;
                    renderCarritoTreinta();
                }
                return;
            }

            // ELIMINAR
            if (btnEliminar) {
                const i = btnEliminar.dataset.index;
                productosSeleccionados.splice(i, 1);
                renderCarritoTreinta();
                return;
            }
        });
    }

    // Vaciar canasta
    const btnVaciarCanasta = document.getElementById("vaciar-canasta");
    if (btnVaciarCanasta) {
        btnVaciarCanasta.addEventListener("click", e => {
            e.preventDefault();
            if (productosSeleccionados.length === 0) return;

            Swal.fire({
                icon: "question",
                title: "Vaciar canasta",
                text: "¿Seguro que deseas eliminar todos los productos?",
                showCancelButton: true,
                confirmButtonText: "Sí, vaciar",
                cancelButtonText: "Cancelar"
            }).then(r => {
                if (r.isConfirmed) {
                    productosSeleccionados = [];
                    renderCarritoTreinta();
                }
            });
        });
    }

    // ============================
    //   RESUMEN (SUBTOTAL / IGV / TOTAL)
    // ============================

    function calcularSubtotal() {
        return productosSeleccionados.reduce(
            (s, p) => s + (p.precio_unitario * p.cantidad),
            0
        );
    }

    function obtenerIGVPercent() {
        const igvInputElement = document.getElementById("igv-config");
        const val = igvInputElement ? parseFloat(igvInputElement.value) : 0;
        return isNaN(val) ? 0 : val;
    }

    function calcularTotal() {
        const subtotal  = calcularSubtotal();
        const igvPercent = obtenerIGVPercent();
        const igv       = subtotal * igvPercent / 100;
        return {
            subtotal,
            igv,
            total: subtotal + igv,
            igvPercent
        };
    }

    function actualizarResumen() {
        const { subtotal, igv, total, igvPercent } = calcularTotal();

        const opEl   = document.getElementById("resumen-op-gravadas");
        const igvEl  = document.getElementById("resumen-igv-monto");
        const totEl  = document.getElementById("resumen-total");
        const igvPEl = document.getElementById("resumen-igv-porcentaje");

        if (opEl)   opEl.innerText   = "S/ " + subtotal.toFixed(2);
        if (igvEl)  igvEl.innerText  = "S/ " + igv.toFixed(2);
        if (totEl)  totEl.innerText  = "S/ " + total.toFixed(2);
        if (igvPEl) igvPEl.innerText = igvPercent.toFixed(0) + "%";

        const totalFooter = document.getElementById("total-general-footer");
        if (totalFooter) totalFooter.innerText = total.toFixed(2);

        const opGravadasInput  = document.querySelector('[name="op_gravadas"]');
        const totalInput       = document.querySelector('[name="total"]');
        const montoPagadoInput = document.querySelector('[name="monto_pagado"]');

        if (opGravadasInput)  opGravadasInput.value  = subtotal.toFixed(2);
        if (totalInput)       totalInput.value       = total.toFixed(2);
        if (montoPagadoInput) montoPagadoInput.value = total.toFixed(2);
    }

    // ============================
    //   BOTÓN FASE 1 (Continuar estilo Treinta)
    // ============================

    function actualizarBotonCarrito() {
        if (!btnIrStep2) return;
        const { total } = calcularTotal();
        const cantidad = productosSeleccionados.reduce((s, p) => s + p.cantidad, 0);

        if (productosSeleccionados.length === 0) {
            btnIrStep2.innerHTML = `0 Continuar`;
            btnIrStep2.disabled = true;
            return;
        }

        btnIrStep2.disabled = false;
        btnIrStep2.innerHTML = `
            <span class="badge bg-dark me-2">${cantidad}</span>
            <span class="flex-grow-1 text-start">Continuar</span>
            <span class="fw-semibold">S/ ${total.toFixed(2)}</span>
            <i class="fas fa-arrow-right ms-2"></i>
        `;
    }

    // ============================
    //   NAVEGACIÓN ENTRE FASES
    // ============================

    if (btnIrStep2 && step1 && step2) {
        btnIrStep2.addEventListener("click", () => {
            if (productosSeleccionados.length === 0) {
                mostrarAlerta("Agrega al menos un producto antes de continuar.");
                return;
            }
            step1.classList.remove("is-active");
            step2.classList.add("is-active");
        });
    }

    if (btnVolverStep1 && step1 && step2) {
        btnVolverStep1.addEventListener("click", () => {
            step2.classList.remove("is-active");
            step1.classList.add("is-active");
        });
    }

    // Fase 2 → Fase 3
    if (btnIrStep3 && step2 && step3 && inputTotalVenta) {
        btnIrStep3.addEventListener("click", () => {
            const { total } = calcularTotal();
            inputTotalVenta.value = total.toFixed(2);

            step2.classList.remove("is-active");
            step3.classList.add("is-active");
        });
    }

    // Fase 3 → Fase 2
    if (btnVolverStep2 && step2 && step3) {
        btnVolverStep2.addEventListener("click", () => {
            step3.classList.remove("is-active");
            step2.classList.add("is-active");
        });
    }

    // ============================
    //   FASE 3: VUELTO
    // ============================

    if (inputPaga && inputTotalVenta && inputVuelto) {
        inputPaga.addEventListener("input", () => {
            const pagar = parseFloat(inputPaga.value || 0);
            const total = parseFloat(inputTotalVenta.value || 0);

            let vuelto = pagar - total;
            if (vuelto < 0) vuelto = 0;

            inputVuelto.value = "S/ " + vuelto.toFixed(2);
        });
    }

    // ============================
    //   CONFIGURAR BOTONES COMPROBANTE
    // ============================

    function configurarBotonesComprobante(data) {
        if (btnImprimir && data.pdf_url) {
            btnImprimir.href = data.pdf_url;
            btnImprimir.target = "_blank";
        }

        if (btnDescargar) {
            if (data.nombre_archivo) {
                btnDescargar.href = `/storage/comprobantes/${data.nombre_archivo}`;
                btnDescargar.download = data.nombre_archivo;
            } else if (data.pdf_url) {
                btnDescargar.href = data.pdf_url;
                btnDescargar.download = "";
            }
        }
    }

    // ============================
    //   REGISTRAR VENTA (FASE 3)
    // ============================

    function registrarVenta() {
        const { total } = calcularTotal();

        if (productosSeleccionados.length === 0) {
            mostrarAlerta("No hay productos en la venta.");
            return;
        }

        const tipoComprobante = document.getElementById("tipo_comprobante")?.value;
        const documento       = documentoInput?.value || '';
        const fecha           = document.getElementById("fecha_emision")?.value;
        const hora            = document.getElementById("hora_actual")?.value;
        const estadoPago      = document.getElementById("estado_pago")?.value || '';
        const metodoPago      = document.getElementById("metodo_pago")?.value || '';
        const formato         = document.getElementById("formato_pdf")?.value || 'a4';

        if (!metodoPago) {
            mostrarAlerta("Debes seleccionar un método de pago.");
            return;
        }

        const productosEnviar = productosSeleccionados.map(p => {
            let unidades_desc = p.cantidad;

            if (p.tipo_venta === "paquete") {
                unidades_desc = p.cantidad * p.unidades_por_paquete;
            }

            if (p.tipo_venta === "caja") {
                unidades_desc = p.cantidad * p.unidades_por_paquete * p.paquetes_por_caja;
            }

            return {
                producto_id: p.id,
                cantidad: p.cantidad,
                tipo_venta: p.tipo_venta,
                precio_unitario: p.precio_unitario,
                unidades_descuento: unidades_desc
            };
        });

        fetch('/ventas/registrar', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                tipo_comprobante: tipoComprobante,
                documento: documento,
                total_venta: total,
                fecha: fecha,
                hora: hora,
                estado_pago: estadoPago,
                metodo_pago: metodoPago,
                productos: productosEnviar,
                formato: formato
            })
        })
        .then(res => res.json())
        .then(data => {
            console.log(data);

            if (!data.success) {
                mostrarAlerta(data.message || "Error al registrar venta.");
                return;
            }

            configurarBotonesComprobante(data);

            if (modalVentaExitosa) {
                modalVentaExitosa.show();
            }

            sonidoExito.play().catch(() => {});
        })
        .catch(() => mostrarAlerta("Error inesperado al registrar la venta."));
    }

    if (btnConfirmar3) {
        btnConfirmar3.addEventListener("click", registrarVenta);
    }

    // ============================
    //   NUEVA VENTA (desde modal)
    // ============================

    if (btnNuevaVenta) {
        btnNuevaVenta.addEventListener("click", () => {

            productosSeleccionados = [];
            renderCarritoTreinta();

            if (documentoInput) documentoInput.value = "";
            const razon = document.getElementById("razon_social");
            const dir   = document.getElementById("direccion");
            if (razon) razon.value = "";
            if (dir)   dir.value   = "";

            if (buscarInput) buscarInput.value = "";

            if (resultadosDiv) {
                resultadosDiv.classList.remove("d-none");
            }

            actualizarProductosStock();

            if (tipoComprobanteSelect) tipoComprobanteSelect.selectedIndex = 0;
            if (metodoPagoSelect)      metodoPagoSelect.selectedIndex      = 0;
            if (inputSerieCorrelativo) inputSerieCorrelativo.value         = "";

            if (tipoComprobanteSelect) {
                tipoComprobanteSelect.dispatchEvent(new Event("change"));
            }

            if (inputTotalVenta) inputTotalVenta.value = "";
            if (inputPaga)       inputPaga.value       = "";
            if (inputVuelto)     inputVuelto.value     = "";

            if (modalVentaExitosa) modalVentaExitosa.hide();

            // Volver a la fase 1
            if (step1 && step2 && step3) {
                step2.classList.remove("is-active");
                step3.classList.remove("is-active");
                step1.classList.add("is-active");
            }

            window.scrollTo({ top: 0, behavior: "smooth" });
        });
    }

    // ============================
    //   MÉTODO DE PAGO (iconos)
    // ============================

    document.querySelectorAll(".metodo-pago-item").forEach(item => {
        item.addEventListener("click", () => {
            document.querySelectorAll(".metodo-pago-item")
                .forEach(i => i.classList.remove("active"));

            item.classList.add("active");

            const hidden = document.getElementById("metodo_pago");
            if (hidden) hidden.value = item.dataset.value;
        });
    });

    // ============================
    //   MODAL ORDENAR PRODUCTOS
    // ============================

    const btnOrdenar = document.getElementById("btn-ordenar");
    let ordenSeleccionada = null;

    // Abrir modal
    if (btnOrdenar && modalOrdenar) {
        btnOrdenar.addEventListener("click", () => {
            modalOrdenar.show();
        });
    }

    // SELECCIONAR OPCIÓN
    const ordenBtns = document.querySelectorAll(".orden-btn");
    if (ordenBtns.length > 0) {
        ordenBtns.forEach(btn => {
            btn.addEventListener("click", () => {
                ordenBtns.forEach(b => b.classList.remove("active"));
                btn.classList.add("active");
                ordenSeleccionada = btn.dataset.type;
            });
        });
    }

    function ordenarProductos(tipo) {
        if (!window.PRODUCTOS_INICIALES || !Array.isArray(window.PRODUCTOS_INICIALES)) return;

        // Respetar categoría activa
        const btnActivo = document.querySelector(".btn-filtro-categoria.active");
        const catID = btnActivo ? Number(btnActivo.dataset.cat) : 0;

        let base = [...window.PRODUCTOS_INICIALES];

        if (catID !== 0) {
            base = base.filter(p => Number(p.categoria_id) === catID);
        }

        switch (tipo) {

            case "az":
                base.sort((a, b) => a.nombre.localeCompare(b.nombre));
                break;

            case "za":
                base.sort((a, b) => b.nombre.localeCompare(a.nombre));
                break;

            case "precio_asc":
                base.sort((a, b) => (a.precio_venta || 0) - (b.precio_venta || 0));
                break;

            case "precio_desc":
                base.sort((a, b) => (b.precio_venta || 0) - (a.precio_venta || 0));
                break;

            case "stock_asc":
                base.sort((a, b) => (a.stock || 0) - (b.stock || 0));
                break;

            case "stock_desc":
                base.sort((a, b) => (b.stock || 0) - (a.stock || 0));
                break;

            case "menos_vendidos":
                base.sort((a, b) => (a.total_vendido || 0) - (b.total_vendido || 0));
                break;

            case "mas_vendidos":
                base.sort((a, b) => (b.total_vendido || 0) - (a.total_vendido || 0));
                break;

            case "fecha_asc":
                base.sort((a, b) => new Date(a.created_at) - new Date(b.created_at));
                break;

            case "fecha_desc":
                base.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
                break;
        }

        renderGrillaProductos(base);
    }

    // LIMPIAR
    const btnLimpiarOrden = document.getElementById("btn-limpiar-orden");
    if (btnLimpiarOrden) {
        btnLimpiarOrden.addEventListener("click", () => {
            ordenSeleccionada = null;
            document.querySelectorAll(".orden-btn").forEach(b => b.classList.remove("active"));

            // Volver a la lista original respetando categoría
            const btnActivo = document.querySelector(".btn-filtro-categoria.active");
            const catID = btnActivo ? Number(btnActivo.dataset.cat) : 0;

            if (window.PRODUCTOS_INICIALES && Array.isArray(window.PRODUCTOS_INICIALES)) {
                if (catID === 0) {
                    renderGrillaProductos(window.PRODUCTOS_INICIALES);
                } else {
                    const filtrados = window.PRODUCTOS_INICIALES.filter(prod =>
                        Number(prod.categoria_id) === catID
                    );
                    renderGrillaProductos(filtrados);
                }
            }
            // ⭐ CERRAR EL MODAL
            if (modalOrdenar) {
                modalOrdenar.hide();
            }
        });
    }

    // APLICAR
    const btnAplicarOrden = document.getElementById("btn-aplicar-orden");
    if (btnAplicarOrden) {
        btnAplicarOrden.addEventListener("click", () => {
            if (ordenSeleccionada) {
                ordenarProductos(ordenSeleccionada);
            }
            if (modalOrdenar) {
                modalOrdenar.hide();
            }
        });
    }

    // ============================
    //   ALERTA
    // ============================

    function mostrarAlerta(msg) {
        Swal.fire({
            icon: "warning",
            title: "¡Atención!",
            text: msg,
            timer: 2500,
            showConfirmButton: false
        });
        sonidoError.play().catch(() => {});
    }

    // Render inicial carrito
    renderCarritoTreinta();
});

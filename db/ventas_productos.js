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

// ===============================
// ✅ POS PRO: Persistencia
// ===============================
const POS_STORAGE_KEY = "dizany_pos_venta_activa_v1";

function posRead() {
    try {
        return JSON.parse(localStorage.getItem(POS_STORAGE_KEY)) || null;
    } catch {
        return null;
    }
}

function posWrite(data) {
    localStorage.setItem(POS_STORAGE_KEY, JSON.stringify(data));
}

function posClear() {
    localStorage.removeItem(POS_STORAGE_KEY);
}

let posSaveTimer = null;
function posSaveDebounced(fnSnapshot, ms = 250) {
    clearTimeout(posSaveTimer);
    posSaveTimer = setTimeout(() => {
        try { posWrite(fnSnapshot()); } catch {}
    }, ms);
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
    const razonInput       = document.getElementById('razon_social');
    const direccionInput   = document.getElementById('direccion');

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

    // ============================
    // ✅ Función showStep propia (sin depender del Blade)
    // ============================
    function showStep(n) {
        document.querySelectorAll(".step-panel").forEach(p => p.classList.remove("is-active"));
        document.getElementById("step-" + n)?.classList.add("is-active");
        // guardar fase
        posSaveDebounced(snapshotPOS, 10);
    }

    // ============================
    // Carrito (datos en memoria)
    // ============================
    let productosSeleccionados = [];

    // Cache de productos que se van mostrando en cards
    const productosCache = new Map(); // id => producto

    // ============================
    // ✅ Snapshot para persistencia
    // ============================
    function snapshotPOS() {
        const metodoPagoHidden = document.getElementById("metodo_pago");

        let fase = 1;
        if (step3?.classList.contains("is-active")) fase = 3;
        else if (step2?.classList.contains("is-active")) fase = 2;

        return {
            fase,
            productosSeleccionados,
            cliente: {
                documento: documentoInput?.value || "",
                razon: razonInput?.value || "",
                direccion: direccionInput?.value || "",
                // estado según icono-save visible (si está visible: nuevo sin guardar)
                no_guardado: (() => {
                    const iconoSave = document.getElementById("icono-save");
                    return iconoSave ? !iconoSave.classList.contains("d-none") : false;
                })()
            },
            metodo_pago: metodoPagoHidden?.value || ""
        };
    }

    function restaurarPOS() {
        const data = posRead();
        if (!data) return;

        // 1) Carrito
        if (Array.isArray(data.productosSeleccionados)) {
            productosSeleccionados = data.productosSeleccionados;
        }

        // 2) Cliente
        if (data.cliente) {
            if (documentoInput) documentoInput.value = data.cliente.documento || "";
            if (razonInput) razonInput.value = data.cliente.razon || "";
            if (direccionInput) direccionInput.value = data.cliente.direccion || "";
        }

        // 3) Método de pago
        const hidden = document.getElementById("metodo_pago");
        if (hidden) hidden.value = data.metodo_pago || "";

        document.querySelectorAll(".metodo-pago-item").forEach(item => {
            item.classList.toggle("active", (data.metodo_pago || "") === item.dataset.value);
        });

        // 4) Fase
        if (data.fase) {
            showStep(data.fase);
        }

        // re-render
        renderCarritoTreinta();
    }

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
                cardCrear.style.pointerEvents = "none";
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

            const col = resultadosDiv.lastElementChild;
            const card = col.querySelector(".product-card");

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

            unidades_por_paquete: producto.unidades_por_paquete
                ? parseInt(producto.unidades_por_paquete)
                : null,

            paquetes_por_caja: producto.paquetes_por_caja
                ? parseInt(producto.paquetes_por_caja)
                : null,

            producto_original: producto
        });

        renderCarritoTreinta();
        posSaveDebounced(snapshotPOS);
    }

    function unidadesReales(p, cantidad) {
        cantidad = parseInt(cantidad) || 0;
        if (cantidad <= 0) return 0;

        // UNIDAD
        if (p.tipo_venta === 'unidad') return cantidad;

        // PAQUETE
        if (p.tipo_venta === 'paquete') {
            return cantidad * (p.unidades_por_paquete || 0);
        }

        // CAJA
        if (p.tipo_venta === 'caja') {
            // Caja → paquetes → unidades
            if (p.paquetes_por_caja > 0 && p.unidades_por_paquete > 0) {
                return cantidad * p.paquetes_por_caja * p.unidades_por_paquete;
            }
            // Caja → unidades directas
            if (p.unidades_por_paquete > 0) {
                return cantidad * p.unidades_por_paquete;
            }
        }

        return 0;
    }

    // Helper color badge stock
    function getStockBadgeColor(stock) {
        if (stock >= 20) return "bg-success";
        if (stock >= 6)  return "bg-warning";
        return "bg-danger";
    }

    function renderCarritoTreinta() {
        if (!carritoLista) return;

        carritoLista.innerHTML = "";

        if (productosSeleccionados.length === 0) {

            const btnActivo = document.querySelector(".btn-filtro-categoria.active");
            let nombreCat   = btnActivo ? btnActivo.textContent.trim() : "productos";

            let textoCategoria = "tu catálogo";
            if (nombreCat && nombreCat.toLowerCase() !== "todos") {
                textoCategoria = `la categoría ${nombreCat.toLowerCase()}`;
            }

            carritoLista.innerHTML = `
                <div class="empty-cart-premium text-center py-5">
                    <div class="empty-illustration mb-3">
                        <svg viewBox="0 0 140 100" class="empty-svg">
                            <defs>
                                <linearGradient id="gradCart" x1="0%" y1="0%" x2="100%" y2="100%">
                                    <stop offset="0%" stop-color="#4A90E2"/>
                                    <stop offset="100%" stop-color="#6C5CE7"/>
                                </linearGradient>
                            </defs>
                            <circle cx="70" cy="40" r="36" fill="url(#gradCart)" opacity="0.15"/>
                            <rect x="30" y="32" width="60" height="26" rx="6" ry="6" fill="#ffffff" stroke="#4A90E2" stroke-width="2"/>
                            <path d="M32 32 L26 20" stroke="#4A90E2" stroke-width="2" stroke-linecap="round"/>
                            <path d="M88 32 L96 20" stroke="#4A90E2" stroke-width="2" stroke-linecap="round"/>
                            <rect x="38" y="26" width="12" height="10" rx="2" fill="#4A90E2" opacity="0.9"/>
                            <rect x="54" y="24" width="12" height="12" rx="2" fill="#6C5CE7" opacity="0.9"/>
                            <rect x="70" y="27" width="12" height="9"  rx="2" fill="#00B894" opacity="0.9"/>
                            <circle cx="44" cy="62" r="5" fill="#ffffff" stroke="#4A90E2" stroke-width="2"/>
                            <circle cx="76" cy="62" r="5" fill="#ffffff" stroke="#4A90E2" stroke-width="2"/>
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

            const btnBuscar = carritoLista.querySelector(".btn-empezar-compra");
            if (btnBuscar && buscarInput) {
                btnBuscar.addEventListener("click", () => {
                    buscarInput.focus();
                    buscarInput.scrollIntoView({ behavior: "smooth", block: "center" });
                });
            }

            actualizarResumen();
            actualizarBotonCarrito();
            posSaveDebounced(snapshotPOS);
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
                                <div class="d-flex justify-content-between align-items-center" style="min-width: 200px;">
                                    <span class="fw-semibold small">${p.nombre}</span>
                                    <span class="badge ${getStockBadgeColor(p.stock)} ms-2">
                                        Stock: ${p.stock}
                                    </span>
                                </div>
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

                                ${
                                    p.unidades_por_paquete > 0 && p.precio_paquete > 0
                                    ? `
                                    <option value="paquete" ${p.tipo_venta === "paquete" ? "selected" : ""}>
                                        Paquete (${p.unidades_por_paquete})
                                    </option>
                                    `
                                    : ""
                                }

                                ${
                                    p.precio_caja > 0
                                    ? (() => {
                                        let textoCaja = "Caja";

                                        if (p.paquetes_por_caja > 0 && p.unidades_por_paquete > 0) {
                                            const totalUnd = p.paquetes_por_caja * p.unidades_por_paquete;
                                            textoCaja = `Caja (${totalUnd} und.)`;
                                        } else if (p.unidades_por_paquete > 0) {
                                            textoCaja = `Caja (${p.unidades_por_paquete} und.)`;
                                        }

                                        return `
                                            <option value="caja" ${p.tipo_venta === "caja" ? "selected" : ""}>
                                                ${textoCaja}
                                            </option>
                                        `;
                                    })()
                                    : ""
                                }
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
        posSaveDebounced(snapshotPOS);
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

            const tipoNuevo = e.target.value;
            p.tipo_venta = tipoNuevo;

            let precioBase = 0;
            if (tipoNuevo === "unidad") precioBase = parseFloat(o.precio_venta || 0);
            if (tipoNuevo === "paquete") precioBase = parseFloat(o.precio_paquete || 0);
            if (tipoNuevo === "caja") precioBase = parseFloat(o.precio_caja || 0);

            p.precio_unitario = precioBase;

            // Reset cantidad
            p.cantidad = 1;

            // Validar stock
            const unidades = unidadesReales(p, p.cantidad);
            if (unidades > p.stock) {
                mostrarAlerta("Stock insuficiente para esta presentación");
                // volver a unidad
                p.tipo_venta = "unidad";
                p.precio_unitario = parseFloat(o.precio_venta || 0);
                p.cantidad = 1;
            }

            renderCarritoTreinta();
        });

        // CANTIDAD ESCRITA MANUALMENTE (blur)
        carritoLista.addEventListener("blur", e => {
            if (!e.target.classList.contains("cambiar-cantidad")) return;

            const i = e.target.dataset.index;
            const p = productosSeleccionados[i];

            let cant = parseInt(e.target.value);
            if (isNaN(cant) || cant < 1) cant = 1;

            const unidades = unidadesReales(p, cant);

            if (unidades > p.stock) {
                mostrarAlerta("Cantidad excede el stock disponible.");
                p.cantidad = 1;
                e.target.value = 1;
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

                const nuevaCantidad = p.cantidad + 1;
                const unidades = unidadesReales(p, nuevaCantidad);

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

    // ============================
    //   PREPARAR FASE 3 (VUELTO)
    // ============================
    function prepararFase3() {
        const { total } = calcularTotal();

        const inputTotalVenta = document.getElementById("vuelto-total-venta");
        const inputPaga       = document.getElementById("vuelto-paga");
        const inputVuelto     = document.getElementById("vuelto-mostrar");

        if (!inputTotalVenta) return;

        inputTotalVenta.value = total.toFixed(2);
        if (inputPaga) inputPaga.value = "";
        if (inputVuelto) inputVuelto.value = "";
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
    //   BOTÓN FASE 1 (Continuar)
    // ============================
    function actualizarBotonCarrito() {
        if (!btnIrStep2) return;

        const { total } = calcularTotal();

        // productos distintos
        const cantidad = productosSeleccionados.length;

        if (cantidad === 0) {
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
            showStep(2);
        });
    }

    if (btnVolverStep1 && step1 && step2) {
        btnVolverStep1.addEventListener("click", () => showStep(1));
    }

    if (btnVolverStep2 && step2 && step3) {
        btnVolverStep2.addEventListener("click", () => showStep(2));
    }

    // ============================
    // ✅ VALIDAR Y PASAR A FASE 3
    // ============================
    if (btnIrStep3) {
        btnIrStep3.addEventListener("click", function (e) {
            e.preventDefault();
            e.stopPropagation();

            const documento = documentoInput?.value.trim();
            const razon     = razonInput?.value.trim();
            const iconoSave = document.getElementById("icono-save");
            const metodo    = document.getElementById("metodo_pago")?.value;

            if (!documento || !razon) {
                Swal.fire("Cliente requerido", "Debes ingresar el DNI o RUC del cliente.", "warning");
                return;
            }

            if (iconoSave && !iconoSave.classList.contains("d-none")) {
                Swal.fire("Cliente no guardado", "Debes guardar el cliente antes de continuar la venta.", "warning");
                return;
            }

            if (!metodo) {
                Swal.fire("Método de pago", "Selecciona un método de pago.", "warning");
                return;
            }

            prepararFase3();
            showStep(3);
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

        const productosEnviar = productosSeleccionados.map(p => ({
            producto_id: p.id,
            cantidad: p.cantidad,
            presentacion: p.tipo_venta
        }));

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
            if (!data.success) {
                mostrarAlerta(data.message || "Error al registrar venta.");
                return;
            }

            configurarBotonesComprobante(data);

            if (modalVentaExitosa) modalVentaExitosa.show();

            // ✅ PRO: al confirmarse venta, limpiar POS
            posClear();

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
            if (razonInput) razonInput.value = "";
            if (direccionInput) direccionInput.value = "";

            if (buscarInput) buscarInput.value = "";

            if (resultadosDiv) resultadosDiv.classList.remove("d-none");

            actualizarProductosStock();

            if (tipoComprobanteSelect) tipoComprobanteSelect.selectedIndex = 0;
            if (metodoPagoSelect) metodoPagoSelect.selectedIndex = 0;
            if (inputSerieCorrelativo) inputSerieCorrelativo.value = "";

            if (tipoComprobanteSelect) tipoComprobanteSelect.dispatchEvent(new Event("change"));

            if (inputTotalVenta) inputTotalVenta.value = "";
            if (inputPaga) inputPaga.value = "";
            if (inputVuelto) inputVuelto.value = "";

            if (modalVentaExitosa) modalVentaExitosa.hide();

            // limpiar método pago UI
            document.querySelectorAll(".metodo-pago-item").forEach(i => i.classList.remove("active"));
            const hidden = document.getElementById("metodo_pago");
            if (hidden) hidden.value = "";

            // ✅ PRO: limpiar POS
            posClear();

            showStep(1);
            window.scrollTo({ top: 0, behavior: "smooth" });
        });
    }

    // ============================
    //   MÉTODO DE PAGO (iconos)
    // ============================
    document.querySelectorAll(".metodo-pago-item").forEach(item => {
        item.addEventListener("click", () => {
            document.querySelectorAll(".metodo-pago-item").forEach(i => i.classList.remove("active"));
            item.classList.add("active");

            const hidden = document.getElementById("metodo_pago");
            if (hidden) hidden.value = item.dataset.value;

            posSaveDebounced(snapshotPOS);
        });
    });

    // ============================
    //   MODAL ORDENAR PRODUCTOS
    // ============================
    const btnOrdenar = document.getElementById("btn-ordenar");
    let ordenSeleccionada = null;

    if (btnOrdenar && modalOrdenar) {
        btnOrdenar.addEventListener("click", () => modalOrdenar.show());
    }

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

        const btnActivo = document.querySelector(".btn-filtro-categoria.active");
        const catID = btnActivo ? Number(btnActivo.dataset.cat) : 0;

        let base = [...window.PRODUCTOS_INICIALES];

        if (catID !== 0) base = base.filter(p => Number(p.categoria_id) === catID);

        switch (tipo) {
            case "az": base.sort((a, b) => a.nombre.localeCompare(b.nombre)); break;
            case "za": base.sort((a, b) => b.nombre.localeCompare(a.nombre)); break;
            case "precio_asc": base.sort((a, b) => (a.precio_venta || 0) - (b.precio_venta || 0)); break;
            case "precio_desc": base.sort((a, b) => (b.precio_venta || 0) - (a.precio_venta || 0)); break;
            case "stock_asc": base.sort((a, b) => (a.stock || 0) - (b.stock || 0)); break;
            case "stock_desc": base.sort((a, b) => (b.stock || 0) - (a.stock || 0)); break;
            case "menos_vendidos": base.sort((a, b) => (a.total_vendido || 0) - (b.total_vendido || 0)); break;
            case "mas_vendidos": base.sort((a, b) => (b.total_vendido || 0) - (a.total_vendido || 0)); break;
            case "fecha_asc": base.sort((a, b) => new Date(a.created_at) - new Date(b.created_at)); break;
            case "fecha_desc": base.sort((a, b) => new Date(b.created_at) - new Date(a.created_at)); break;
        }

        renderGrillaProductos(base);
    }

    const btnLimpiarOrden = document.getElementById("btn-limpiar-orden");
    if (btnLimpiarOrden) {
        btnLimpiarOrden.addEventListener("click", () => {
            ordenSeleccionada = null;
            document.querySelectorAll(".orden-btn").forEach(b => b.classList.remove("active"));

            const btnActivo = document.querySelector(".btn-filtro-categoria.active");
            const catID = btnActivo ? Number(btnActivo.dataset.cat) : 0;

            if (window.PRODUCTOS_INICIALES && Array.isArray(window.PRODUCTOS_INICIALES)) {
                if (catID === 0) renderGrillaProductos(window.PRODUCTOS_INICIALES);
                else renderGrillaProductos(window.PRODUCTOS_INICIALES.filter(prod => Number(prod.categoria_id) === catID));
            }

            if (modalOrdenar) modalOrdenar.hide();
        });
    }

    const btnAplicarOrden = document.getElementById("btn-aplicar-orden");
    if (btnAplicarOrden) {
        btnAplicarOrden.addEventListener("click", () => {
            if (ordenSeleccionada) ordenarProductos(ordenSeleccionada);
            if (modalOrdenar) modalOrdenar.hide();
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

    // ============================
    // ✅ Guardar inputs cliente mientras escribe
    // ============================
    documentoInput?.addEventListener("input", () => posSaveDebounced(snapshotPOS));
    razonInput?.addEventListener("input", () => posSaveDebounced(snapshotPOS));
    direccionInput?.addEventListener("input", () => posSaveDebounced(snapshotPOS));

    // ============================
    // ✅ Restaurar POS antes del render inicial
    // ============================
    restaurarPOS();

    // Render inicial carrito (por si no había POS)
    renderCarritoTreinta();

    // Guardar al salir/recargar
    window.addEventListener("beforeunload", () => {
        try { posWrite(snapshotPOS()); } catch {}
    });
});

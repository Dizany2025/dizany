// ===============================
// IGV global desde configuración
// ===============================
const igvConfigInput = document.getElementById("igv-config");
const IGV_PERCENT = igvConfigInput ? parseFloat(igvConfigInput.value) || 0 : 0;

// Helper: dado un precio base (sin IGV) devuelve el precio final (con IGV)
function calcularPrecioFinal(precioBase) {
    const base = parseFloat(precioBase) || 0;
    if (IGV_PERCENT <= 0) return base;
    return base * (1 + IGV_PERCENT / 100);
}

// ===============================
// ✅ POS PRO: Multi-venta + Persistencia + Reservas
// ===============================
const POS_STORE_KEY = "dizany_pos_store_v3";

function posLoad() {
    try { return JSON.parse(localStorage.getItem(POS_STORE_KEY)) || null; }
    catch { return null; }
}
function posSave(data) {
    localStorage.setItem(POS_STORE_KEY, JSON.stringify(data));
}
function posClear() {
    localStorage.removeItem(POS_STORE_KEY);
}
let posTimer = null;
function posSaveDebounced(getSnapshot, ms = 250) {
    clearTimeout(posTimer);
    posTimer = setTimeout(() => {
        try { posSave(getSnapshot()); } catch {}
    }, ms);
}

// ===============================
// ✅ Estado POS (ventas múltiples)
// ===============================
let POS = posLoad() || {
    version: 3,
    ventaActivaId: null,
    ventas: {} // { id: { id, fase, cliente, metodo_pago, productos[] } }
};

function uidVenta() {
    return "V" + Date.now().toString(36) + Math.random().toString(36).slice(2, 6);
}

function crearVentaVacia(id) {
    return {
        id,
        fase: 1,
        cliente: { documento: "", razon: "", direccion: "", no_guardado: false },
        metodo_pago: "",
        productos: []
    };
}

function asegurarVentaActiva() {
    const ids = Object.keys(POS.ventas || {});
    if (!ids.length) {
        const id = uidVenta();
        POS.ventas[id] = crearVentaVacia(id);
        POS.ventaActivaId = id;
    }
    if (!POS.ventaActivaId || !POS.ventas[POS.ventaActivaId]) {
        POS.ventaActivaId = Object.keys(POS.ventas)[0];
    }
}
function ventaActiva() {
    asegurarVentaActiva();
    return POS.ventas[POS.ventaActivaId];
}
// ============================
// ✅ Alias de venta (nombre visible)
// ============================
function actualizarAliasVentaDesdeCliente(forzar = false) {
    const v = ventaActiva();
    if (!v || !v.cliente) return;

    const nombre = (v.cliente.razon || "").trim();
    if (!nombre) return;

    // 🔥 FORZAR alias siempre
    v.alias = nombre;

    if (typeof snapshotPOS === "function") {
        posSaveDebounced(snapshotPOS, 10);
    }
}

// ===============================
// ✅ Reservas globales de stock
// ===============================
function unidadesRealesDeItem(item) {
    const cantidad = parseInt(item.cantidad) || 0;
    if (cantidad <= 0) return 0;

    if (item.tipo_venta === "unidad") return cantidad;

    if (item.tipo_venta === "paquete") {
        return cantidad * (parseInt(item.unidades_por_paquete) || 0);
    }

    if (item.tipo_venta === "caja") {
        const pxc = parseInt(item.paquetes_por_caja) || 0;
        const upp = parseInt(item.unidades_por_paquete) || 0;
        if (pxc > 0 && upp > 0) return cantidad * pxc * upp;
        if (upp > 0) return cantidad * upp;
    }

    return 0;
}

function stockRealProducto(it) {
    const prod = productosCache.get(Number(it.id));
    return prod
        ? (parseInt(prod.stock) || 0)
        : (parseInt(it.stock) || 0);
}


// ===============================
// ⛔ RESERVAS DESACTIVADAS
// ===============================
// Esta función se usaba para reservar stock en frontend.
// Se deja comentada por si se requiere reactivar en el futuro.
//
// function reservasTotalesPorProducto() {
//     const reserved = {};
//     for (const v of Object.values(POS.ventas || {})) {
//         for (const it of (v.productos || [])) {
//             const id = Number(it.id);
//             if (!id) continue;
//             reserved[id] = (reserved[id] || 0) + unidadesRealesDeItem(it);
//     }
//     return reserved;
// }


function stockDisponible(prod) {
    // 🔥 SIN RESERVAS: siempre devolver stock real
    return parseInt(prod.stock) || 0;
}


// ===============================
// DOMContentLoaded
// ===============================
document.addEventListener("DOMContentLoaded", () => {

    // ============================
    // ELEMENTOS PRINCIPALES
    // ============================
    const modalOrdenarEl = document.getElementById("modalOrdenar");
    const modalOrdenar = (window.bootstrap && modalOrdenarEl) ? new bootstrap.Modal(modalOrdenarEl) : null;

    const buscarInput   = document.getElementById("buscar_producto");
    const resultadosDiv = document.getElementById("resultados-busqueda");
    const carritoLista  = document.getElementById("carrito-lista");

    const btnIrStep2     = document.getElementById("btn-ir-step2");
    const btnVolverStep1 = document.getElementById("btn-volver-step1") || document.getElementById("btn-volver-carrito");
    const btnIrStep3     = document.getElementById("btn-ir-step3") || document.getElementById("btn-ir-vuelto");
    const btnVolverStep2 = document.getElementById("btn-volver-step2") || document.getElementById("btn-vuelto-atras");
    const btnConfirmar3  = document.getElementById("btn-confirmar-venta");

    const step1 = document.getElementById("step-1");
    const step2 = document.getElementById("step-2");
    const step3 = document.getElementById("step-3");

    // Panel multi-venta (tu HTML)
    const btnPosEspera   = document.getElementById("btn-pos-espera");
    const posEsperaCount = document.getElementById("pos-espera-count");
    const posEsperaPanel = document.getElementById("pos-espera-panel");

    // Modal venta exitosa
    let modalVentaExitosa = null;
    const modalVentaExitosaElement = document.getElementById("modalVentaExitosa");
    if (modalVentaExitosaElement && window.bootstrap) modalVentaExitosa = new bootstrap.Modal(modalVentaExitosaElement);

    // Cliente
    const documentoInput = document.getElementById("documento");
    const razonInput     = document.getElementById("razon_social");
    const direccionInput = document.getElementById("direccion");
    const hiddenMetodoPago = document.getElementById("metodo_pago");

    // Comprobante
    const tipoComprobanteSelect = document.getElementById("tipo_comprobante");
    const inputSerieCorrelativo = document.getElementById("serie_correlativo");
    const estadoPagoSelect      = document.getElementById("estado_pago");
    estadoPagoSelect?.addEventListener("change", manejarEstadoVenta);
    manejarEstadoVenta(); // al cargar la pantalla

    const formatoSelect         = document.getElementById("formato_pdf");

    // Fase 3 vuelto
    const inputTotalVenta = document.getElementById("vuelto-total-venta");
    const inputPaga       = document.getElementById("vuelto-paga");
    const inputVuelto     = document.getElementById("vuelto-mostrar");

    // Botones comprobante
    const btnImprimir   = document.getElementById("btnImprimir");
    const btnDescargar  = document.getElementById("btn-descargar");
    const btnNuevaVenta = document.getElementById("btnNuevaVenta");

    // Sonidos
    const sonidoError = new Audio("/sonidos/error-alert.mp3"); // si tu ruta real es mp3, corrígelo
    const sonidoExito = new Audio("/sonidos/success.mp3");

    // Cache productos
    window.productosCache = new Map(); // id => producto

    // ============================
    // FIX SONIDO (si ruta estaba mal)
    // ============================
    // Si tu archivo es /sonidos/error-alert.mp3 (como antes), descomenta:
    // const sonidoError = new Audio('/sonidos/error-alert.mp3');

    // ============================
    // Estilos mínimos para el panel
    // ============================
    (function injectStyles() {
        if (document.getElementById("pos-espera-style")) return;
        const st = document.createElement("style");
        st.id = "pos-espera-style";
        st.innerHTML = `
            .pos-espera-wrapper{ position:relative; }
            .pos-espera-panel{
                position:absolute;
                top: calc(100% + 8px);
                right: 0;
                width: 340px;
                max-height: 420px;
                overflow:auto;
                border-radius: 14px;
                background: #fff;
                box-shadow: 0 18px 40px rgba(0,0,0,.18);
                transform: translateY(-8px);
                opacity: 0;
                transition: .18s ease;
                z-index: 9999;
                padding: 10px;
            }
            .pos-espera-panel.show{
                transform: translateY(0);
                opacity: 1;
            }
            .pos-espera-item{
                display:flex;
                align-items:center;
                justify-content:space-between;
                gap:10px;
                padding:10px;
                border-radius:12px;
                border:1px solid rgba(0,0,0,.06);
                margin-bottom:8px;
            }
            .pos-espera-item.active{
                border-color: rgba(0,123,255,.35);
                background: rgba(0,123,255,.06);
            }
            .pos-espera-item .info{ cursor:pointer; flex:1; }
            .pos-espera-item .info strong{ display:block; font-size:13px; }
            .pos-espera-item .info span{ color:#666; font-size:12px; }
            .pos-espera-item .delete{
                border:none;
                background: rgba(220,53,69,.1);
                color:#dc3545;
                width:34px; height:34px;
                border-radius:10px;
                cursor:pointer;
            }
            .pos-espera-empty{
                padding:14px;
                text-align:center;
                color:#777;
                font-size:13px;
            }
            .product-card.agotado{ opacity:.45; pointer-events:none; }
            .product-card.en-carrito{ outline:2px solid rgba(0, 123, 255, .25); }
        `;
        document.head.appendChild(st);
    })();

    // ============================
    // Snapshot + persistencia
    // ============================
    function snapshotPOS() {
        return POS;
    }

    function persistNow() {
        try { posSave(snapshotPOS()); } catch {}
    }

    // ============================
    // showStep propio
    // ============================
    function showStep(n) {
        document.querySelectorAll(".step-panel").forEach(p => p.classList.remove("is-active"));
        document.getElementById("step-" + n)?.classList.add("is-active");

        const v = ventaActiva();
        v.fase = n;

        posSaveDebounced(snapshotPOS, 10);
    }

    // ============================
    // ALERTAS
    // ============================
    function mostrarAlerta(msg) {
        Swal.fire({
            icon: "warning",
            title: "¡Atención!",
            text: msg,
            timer: 2500,
            showConfirmButton: false
        });
        try { sonidoError.play().catch(() => {}); } catch {}
    }

    // ============================
    // Serie y correlativo
    // ============================
    if (tipoComprobanteSelect && inputSerieCorrelativo) {
        tipoComprobanteSelect.addEventListener("change", () => {
            fetch(`/ventas/obtener-serie-correlativo?tipo=${tipoComprobanteSelect.value}`)
                .then(res => res.json())
                .then(data => {
                    if (data.serie && data.correlativo != null) {
                        inputSerieCorrelativo.value = `${data.serie}-${String(data.correlativo).padStart(6, "0")}`;
                    }
                })
                .catch(() => console.error("Error al obtener serie y correlativo"));
        });
        tipoComprobanteSelect.dispatchEvent(new Event("change"));
    }

    // ============================
    // Cliente / método pago <-> venta activa
    // ============================
    function leerEstadoClienteNoGuardado() {
        const iconoSave = document.getElementById("icono-save");
        return iconoSave ? !iconoSave.classList.contains("d-none") : false;
    }

    function volcarUIaVentaActiva() {
        const v = ventaActiva();
        if (!v) return;

        if (!v.cliente) {
            v.cliente = {
                documento: "",
                razon: "",
                direccion: "",
                no_guardado: false
            };
        }

        v.cliente.documento   = documentoInput?.value || "";
        v.cliente.razon       = razonInput?.value || "";
        v.cliente.direccion   = direccionInput?.value || "";
        v.cliente.no_guardado = leerEstadoClienteNoGuardado();

        v.metodo_pago = hiddenMetodoPago?.value || "";

        // 🔥🔥🔥 ESTA ES LA LÍNEA QUE FALTABA
        if (window.actualizarAliasVentaDesdeCliente) {
            actualizarAliasVentaDesdeCliente();
        }

        if (typeof snapshotPOS === "function") {
            posSaveDebounced(snapshotPOS, 50);
        }

        renderVentasEsperaPanel();
    }


    function restaurarVentaActivaEnUI() {
        const v = ventaActiva();

        if (documentoInput) documentoInput.value = v.cliente?.documento || "";
        if (razonInput) razonInput.value = v.cliente?.razon || "";
        if (direccionInput) direccionInput.value = v.cliente?.direccion || "";

        if (hiddenMetodoPago) hiddenMetodoPago.value = v.metodo_pago || "";

        // marcar método pago visual
        document.querySelectorAll(".metodo-pago-item").forEach(item => {
            item.classList.toggle("active", (v.metodo_pago || "") === item.dataset.value);
        });

        // opcional: efectivo por defecto
        if (!v.metodo_pago) {
            const efectivo = document.querySelector('.metodo-pago-item[data-value="efectivo"]');
            if (efectivo && hiddenMetodoPago) {
                efectivo.classList.add("active");
                hiddenMetodoPago.value = "efectivo";
                v.metodo_pago = "efectivo";
            }
        }

        showStep(v.fase || 1);
    }

    // ============================
    // ✅ Cliente: guardar + renombrar venta en tiempo real
    // ============================
    documentoInput?.addEventListener("input", () => {
        volcarUIaVentaActiva();
        renderVentasEsperaPanel();
    });

    razonInput?.addEventListener("input", () => {
    console.log("🔥 INPUT RAZON DISPARADO:", razonInput.value);
    volcarUIaVentaActiva();
    renderVentasEsperaPanel();
});

    direccionInput?.addEventListener("input", () => {
        volcarUIaVentaActiva();
    });


    document.querySelectorAll(".metodo-pago-item").forEach(item => {
        item.addEventListener("click", () => {
            document.querySelectorAll(".metodo-pago-item").forEach(i => i.classList.remove("active"));
            item.classList.add("active");
            if (hiddenMetodoPago) hiddenMetodoPago.value = item.dataset.value;
            volcarUIaVentaActiva();
        });
    });

    // ============================
    // Helpers: grilla
    // ============================
    function cacheProductos(lista) {
        if (!Array.isArray(lista)) return;
        lista.forEach(p => {
            if (p && p.id != null) productosCache.set(Number(p.id), p);
        });
    }

    function cortar(txt, n = 35) {
        if (!txt) return "";
        return txt.length > n ? txt.substring(0, n) + "..." : txt;
    }

    function crearCardProducto(prod) {
        let nombreImagen = String(prod.imagen || "").trim();
        if (nombreImagen.includes("<") || nombreImagen.includes(">") || nombreImagen.includes("=") || nombreImagen.includes('"') || nombreImagen.includes("'")) {
            nombreImagen = "";
        }
        const imgSrc = nombreImagen ? `/uploads/productos/${nombreImagen}` : "/img/sin-imagen.png";

        const precioBase  = parseFloat(prod.precio_venta || 0) || 0;
        const precioFinal = calcularPrecioFinal(precioBase).toFixed(2);

        const disponible = stockDisponible(prod);
        const stockText  = disponible > 0 ? `${disponible} disponibles` : "Sin stock";

        const v = ventaActiva();
        const enCarrito = (v.productos || []).some(it => Number(it.id) === Number(prod.id));

        return `
            <div class="col-6 col-md-4 col-xl-3 mb-3">
                <div class="product-card agregar-carrito ${disponible <= 0 ? "agotado" : ""} ${enCarrito ? "en-carrito" : ""}" data-id="${prod.id}">
                    <div class="product-img-wrapper">
                        <img src="${imgSrc}" alt="${prod.nombre}" class="product-img">
                        <span class="product-price-badge">S/ ${precioFinal}</span>
                    </div>
                    <div class="product-info">
                        ${IGV_PERCENT > 0 ? '<small class="text-success fw-bold d-block" style="font-size:12px;">Incl. IGV</small>' : ''}
                        <div class="product-name" title="${prod.nombre}">${prod.nombre}</div>
                        <div class="product-desc" title="${prod.descripcion || ""}">
                            ${cortar(prod.descripcion, 35) || "&nbsp;"}
                        </div>
                        <div class="product-stock ${disponible > 0 ? "stock-ok" : "stock-low"}">
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

        // tarjeta crear producto
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

            const cardCrear = resultadosDiv.querySelector(".crear-producto-card");
            if (cardCrear) {
                cardCrear.classList.add("show");
                cardCrear.addEventListener("click", () => window.location.href = "/productos/create");
            }
        }

        if (!lista || lista.length === 0) {
            resultadosDiv.insertAdjacentHTML("beforeend", `
                <div class="col-12 text-center text-muted py-3">No se encontraron productos</div>
            `);
            return;
        }

        cacheProductos(lista);

        lista.forEach((prod, idx) => {
            resultadosDiv.insertAdjacentHTML("beforeend", crearCardProducto(prod));
            const col = resultadosDiv.lastElementChild;
            const card = col?.querySelector(".product-card");
            if (card) {
                card.style.transitionDelay = (idx * 0.02) + "s";
                requestAnimationFrame(() => card.classList.add("show"));
            }
        });

        resultadosDiv.querySelectorAll(".product-card.agregar-carrito").forEach(card => {
            card.addEventListener("click", () => {
                const id = Number(card.dataset.id);
                const prod = productosCache.get(id);
                if (!prod) return mostrarAlerta("No se pudo obtener la información del producto.");

                const disp = stockDisponible(prod);
                if (disp <= 0) return mostrarAlerta(`No hay stock para "${prod.nombre}".`);

                const v = ventaActiva();
                if (v.productos.some(it => Number(it.id) === id)) {
                    return mostrarAlerta(`El producto "${prod.nombre}" ya está en la canasta.`);
                }

                agregarProductoAVentaActiva(prod);
            });
        });
    }

    // ============================
    // Stock refresh backend
    // ============================
    function actualizarProductosStock() {
        fetch("/productos/iniciales")
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
    // Filtro categoría
    // ============================
    const botonesCategorias = document.querySelectorAll(".btn-filtro-categoria");
    if (botonesCategorias.length) {
        botonesCategorias.forEach(btn => {
            btn.addEventListener("click", () => {
                botonesCategorias.forEach(b => b.classList.remove("active"));
                btn.classList.add("active");

                const catID = Number(btn.dataset.cat);
                if (!window.PRODUCTOS_INICIALES || !Array.isArray(window.PRODUCTOS_INICIALES)) return;

                if (catID === 0) return renderGrillaProductos(window.PRODUCTOS_INICIALES);

                const filtrados = window.PRODUCTOS_INICIALES.filter(p => Number(p.categoria_id) === catID);
                renderGrillaProductos(filtrados);
            });
        });
    }

    // ============================
    // Buscar producto (AJAX)
    // ============================
    if (buscarInput) {
        buscarInput.addEventListener("input", () => {
            const q = buscarInput.value.trim();

            if (!q) {
                const btnActivo = document.querySelector(".btn-filtro-categoria.active");
                const catID = btnActivo ? Number(btnActivo.dataset.cat) : 0;

                if (window.PRODUCTOS_INICIALES && Array.isArray(window.PRODUCTOS_INICIALES)) {
                    if (catID === 0) renderGrillaProductos(window.PRODUCTOS_INICIALES);
                    else renderGrillaProductos(window.PRODUCTOS_INICIALES.filter(p => Number(p.categoria_id) === catID));
                } else {
                    resultadosDiv.innerHTML = "";
                    resultadosDiv.classList.add("d-none");
                }
                return;
            }

            fetch(`/buscar-producto?search=${encodeURIComponent(q)}`)
                .then(res => res.json())
                .then(list => renderGrillaProductos(list))
                .catch(() => mostrarAlerta("Error al buscar productos"));
        });
    }

    // ============================
    // Agregar producto a venta activa (RESERVA)
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

            unidades_por_paquete: producto.unidades_por_paquete ? parseInt(producto.unidades_por_paquete) : 0,
            paquetes_por_caja: producto.paquetes_por_caja ? parseInt(producto.paquetes_por_caja) : 0
        };

        const prodActual = productosCache.get(item.id) || producto;
        if (stockDisponible(prodActual) < unidadesRealesDeItem(item)) {
            return mostrarAlerta("No hay stock suficiente.");
        }

        v.productos.push(item);

        posSaveDebounced(snapshotPOS, 10);
        renderTodo();
    }

    // ============================
    // Carrito render
    // ============================
    function getStockBadgeColor(stock) {
        if (stock >= 20) return "bg-success";
        if (stock >= 6) return "bg-warning";
        return "bg-danger";
    }

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

            if (["e", "E", "+", "-", "."].includes(e.key)) {
                e.preventDefault();
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
    // Totales / resumen (venta activa)
    // ============================
    function calcularSubtotal() {
        const v = ventaActiva();
        return (v.productos || []).reduce((s, it) => s + (parseFloat(it.precio_unitario || 0) * (parseInt(it.cantidad) || 0)), 0);
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
        return { subtotal, igv, total: subtotal + igv, igvPercent };
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
// Estado de venta: PAGADO / PENDIENTE / CREDITO
// ============================
function manejarEstadoVenta() {

    const estado = (estadoPagoSelect?.value || "pagado").toLowerCase();

    const items = document.querySelectorAll(".metodo-pago-item");
    const btnIrStep3 = document.getElementById("btn-ir-step3");
    const btnConfirmarDirecto = document.getElementById("btn-confirmar-venta-directo");

    console.log("ESTADO ACTUAL:", estado);
    /* ==========================
       RESET GENERAL (CLAVE)
    ========================== */
    items.forEach(i => i.classList.remove("d-none", "active"));

    if (btnIrStep3) {
        btnIrStep3.style.display = "";
        btnIrStep3.innerHTML = `Continuar venta <i class="fas fa-arrow-right ms-2"></i>`;
    }

    if (btnConfirmarDirecto) {
        btnConfirmarDirecto.style.display = "none";
    }

    if (hiddenMetodoPago) hiddenMetodoPago.value = "";

    /* ==========================
       🟡 PENDIENTE
       - NO step 3
       - Confirmar directo
    ========================== */
    if (estado === "pendiente") {
        items.forEach(i => {
            if (i.dataset.value !== "otro") {
                i.classList.add("d-none");
            } else {
                i.classList.add("active");
            }
        });

        hiddenMetodoPago.value = "otro";

        if (btnIrStep3) btnIrStep3.style.display = "none";
        if (btnConfirmarDirecto) btnConfirmarDirecto.style.display = "block";

        return;
    }

    /* ==========================
       🔵 CRÉDITO
       - SOLO método "otro"
       - CONTINUAR venta (NO confirmar)
       - PASA A STEP 3 (adelanto)
    ========================== */
  if (estado === "credito") {
    items.forEach(i => {
        if (i.dataset.value !== "otro") {
            i.classList.add("d-none");
        } else {
            i.classList.add("active");
        }
    });

    hiddenMetodoPago.value = "otro";

    // 🔥 FORZAR TEXTO CORRECTO
    if (btnIrStep3) {
        btnIrStep3.style.display = "";
        btnIrStep3.innerHTML = `Continuar venta <i class="fas fa-arrow-right ms-2"></i>`;
    }

    if (btnConfirmarDirecto) {
        btnConfirmarDirecto.style.display = "none";
    }

    return;
}

}

/* ==========================
   EVENTOS
========================== */
estadoPagoSelect?.addEventListener("change", manejarEstadoVenta);
manejarEstadoVenta(); // ejecutar al cargar


    function actualizarBotonCarrito() {
        if (!btnIrStep2) return;

        const v = ventaActiva();
        const { total } = calcularTotal();
        const cantidad = (v.productos || []).length;

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

    function prepararFase3() {
        const { total } = calcularTotal();
        if (inputTotalVenta) inputTotalVenta.value = total.toFixed(2);
        if (inputPaga) inputPaga.value = "";
        if (inputVuelto) inputVuelto.value = "";
    }

    // ============================
    // Navegación fases
    // ============================
    if (btnIrStep2) {
        btnIrStep2.addEventListener("click", () => {
            const v = ventaActiva();
            if (!v.productos.length) return mostrarAlerta("Agrega al menos un producto antes de continuar.");
            showStep(2);
        });
    }
    if (btnVolverStep1) btnVolverStep1.addEventListener("click", () => showStep(1));
    if (btnVolverStep2) btnVolverStep2.addEventListener("click", () => showStep(2));

    if (btnIrStep3) {
  btnIrStep3.addEventListener("click", (e) => {
  e.preventDefault();

  volcarUIaVentaActiva();
  const v = ventaActiva();

  const estado = (estadoPagoSelect?.value || "pagado").toLowerCase();

  const documento  = (v.cliente?.documento || "").trim();
  const razon      = (v.cliente?.razon || "").trim();
  const noGuardado = !!v.cliente?.no_guardado;
  const metodo     = (v.metodo_pago || "").trim();

  if (!documento || !razon) {
    Swal.fire("Cliente requerido", "Debes ingresar el cliente.", "warning");
    return;
  }

  if (noGuardado) {
    Swal.fire("Cliente no guardado", "Debes guardar el cliente.", "warning");
    return;
  }

  // 🟡 PENDIENTE → SE REGISTRA AQUÍ MISMO
  if (estado === "pendiente") {
    registrarVenta();
    return;
  }

  // 🔵 CRÉDITO → FASE 3 (ADELANTO)
  if (estado === "credito") {
    prepararFase3Credito(); // aquí irá el input de adelanto
    showStep(3);
    return;
  }

  // 🟢 PAGADO → FASE 3 (VUELTO)
  if (!metodo) {
    Swal.fire("Método de pago", "Selecciona un método de pago.", "warning");
    return;
  }

  prepararFase3(); // vuelto
  showStep(3);
});

}


function prepararFase3Credito() {
  const { total } = calcularTotal();

  if (inputTotalVenta) inputTotalVenta.value = total.toFixed(2);

  if (inputPaga) {
    inputPaga.value = "";
    inputPaga.placeholder = "Ingrese adelanto";
  }

  // ✅ Mostrar el campo de "Vuelto" pero lo usaremos como "Saldo pendiente"
  // (si lo ocultas, no podrás mostrar el saldo)
  if (inputVuelto) {
    inputVuelto.value = "";
  }

  // Opcional: cambia el título si quieres
  const titulo = document.querySelector("#step-3 .modal-title, #step-3 h5, #step-3 h6");
  if (titulo && titulo.textContent.toLowerCase().includes("cambio")) {
    titulo.textContent = "Registra el adelanto";
  }
}


    // Vuelto
    if (inputPaga && inputTotalVenta && inputVuelto) {
        inputPaga.addEventListener("input", () => {
            const monto = parseFloat(inputPaga.value || 0);
            const total = parseFloat(inputTotalVenta.value || 0);
            const estado = (estadoPagoSelect?.value || "pagado").toLowerCase();

            if (estado === "credito") {
                let saldo = total - monto;
                if (saldo < 0) saldo = 0;

                inputVuelto.value = `Saldo pendiente: S/ ${saldo.toFixed(2)}`;
                return;
            }

            // 🟢 pagado normal
            let vuelto = monto - total;
            if (vuelto < 0) vuelto = 0;

            inputVuelto.value = `S/ ${vuelto.toFixed(2)}`;
        });
}


    // ============================
    // Panel: Ventas en espera (TU HTML)
    // ============================
    function totalVentaRapido(v) {
        return (v.productos || []).reduce((s, it) => s + (parseFloat(it.precio_unitario || 0) * (parseInt(it.cantidad) || 0)), 0);
    }
    function nombreVenta(v) {
        if (v.cliente?.razon && v.cliente.razon.trim() !== "") {
            return v.cliente.razon.trim();
        }
        return `Venta ${v.id.slice(-4)}`;
    }

    function eliminarVenta(id) {
        if (!POS.ventas[id]) return;
        delete POS.ventas[id];
        asegurarVentaActiva();
        restaurarVentaActivaEnUI();
        renderTodo();
    }

    function renderVentasEsperaPanel() {
    if (!posEsperaPanel || !posEsperaCount) return;

    // ✅ solo ventas que tengan productos
    const ventasConItems = Object.values(POS.ventas || {})
        .filter(v => (v.productos || []).length > 0);

        // contador correcto
        posEsperaCount.innerText = ventasConItems.length;

        // estado vacío
        if (ventasConItems.length === 0) {
            posEsperaPanel.innerHTML = `
                <div class="pos-espera-empty">
                    No hay ventas en espera
                </div>
            `;
            return;
        }

        posEsperaPanel.innerHTML = "";

        ventasConItems.forEach(v => {
            const total = totalVentaRapido(v);

            const cantidad = (v.productos || []).length;
            const label = cantidad === 1 ? "producto" : "productos";

            const item = document.createElement("div");
            item.className =
                "pos-espera-item" +
                (v.id === POS.ventaActivaId ? " active" : "");

            item.innerHTML = `
                <div class="info">
                    <strong>${nombreVenta(v)}</strong>
                    <span>S/ ${total.toFixed(2)} • ${cantidad} ${label}</span>
                </div>
                <button class="delete" type="button" title="Eliminar venta">
                    <i class="fas fa-trash"></i>
                </button>
            `;

            // activar venta
            item.querySelector(".info").addEventListener("click", () => {
                POS.ventaActivaId = v.id;
                restaurarVentaActivaEnUI();
                renderTodo();
                cerrarPanelEspera();
            });

            // eliminar venta
            item.querySelector(".delete").addEventListener("click", (e) => {
                e.stopPropagation();

                Swal.fire({
                    title: "Eliminar venta",
                    text: "Se perderán los productos reservados",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Eliminar",
                    cancelButtonText: "Cancelar"
                }).then(r => {
                    if (!r.isConfirmed) return;

                    eliminarVenta(v.id);
                    renderVentasEsperaPanel();
                });
            });

            posEsperaPanel.appendChild(item);
        });
        

        // Botón “Nueva venta”
        const add = document.createElement("button");
        add.type = "button";
        add.className = "btn btn-sm btn-primary w-100 mt-2";
        add.innerHTML = `<i class="fas fa-plus-circle me-1"></i> Nueva venta`;
        add.addEventListener("click", () => {
            const id = uidVenta();
            POS.ventas[id] = crearVentaVacia(id);
            POS.ventaActivaId = id;

            // por defecto efectivo
            POS.ventas[id].metodo_pago = "efectivo";

            restaurarVentaActivaEnUI();
            renderTodo();
            cerrarPanelEspera();
        });

        posEsperaPanel.appendChild(add);
    }

    function abrirPanelEspera() {
        if (!posEsperaPanel) return;
        posEsperaPanel.classList.remove("d-none");
        requestAnimationFrame(() => posEsperaPanel.classList.add("show"));
    }
    function cerrarPanelEspera() {
        if (!posEsperaPanel) return;
        posEsperaPanel.classList.remove("show");
        setTimeout(() => posEsperaPanel.classList.add("d-none"), 180);
    }

    if (btnPosEspera && posEsperaPanel) {
        btnPosEspera.addEventListener("click", (e) => {
            e.preventDefault();
            e.stopPropagation();

            // re-render siempre antes de abrir
            renderVentasEsperaPanel();

            if (posEsperaPanel.classList.contains("d-none")) abrirPanelEspera();
            else cerrarPanelEspera();
        });

        // cerrar si click fuera
        document.addEventListener("click", () => cerrarPanelEspera());

        // no cerrar al click dentro del panel
        posEsperaPanel.addEventListener("click", (e) => e.stopPropagation());
    }

    // ============================
    // Ordenar (modal)
    // ============================
    const btnOrdenar = document.getElementById("btn-ordenar");
    let ordenSeleccionada = null;

    if (btnOrdenar && modalOrdenar) btnOrdenar.addEventListener("click", () => modalOrdenar.show());

    const ordenBtns = document.querySelectorAll(".orden-btn");
    if (ordenBtns.length) {
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
            case "az": base.sort((a,b) => a.nombre.localeCompare(b.nombre)); break;
            case "za": base.sort((a,b) => b.nombre.localeCompare(a.nombre)); break;
            case "precio_asc": base.sort((a,b) => (a.precio_venta||0) - (b.precio_venta||0)); break;
            case "precio_desc": base.sort((a,b) => (b.precio_venta||0) - (a.precio_venta||0)); break;
            case "stock_asc": base.sort((a,b) => (a.stock||0) - (b.stock||0)); break;
            case "stock_desc": base.sort((a,b) => (b.stock||0) - (a.stock||0)); break;
            case "menos_vendidos": base.sort((a,b) => (a.total_vendido||0) - (b.total_vendido||0)); break;
            case "mas_vendidos": base.sort((a,b) => (b.total_vendido||0) - (a.total_vendido||0)); break;
            case "fecha_asc": base.sort((a,b) => new Date(a.created_at) - new Date(b.created_at)); break;
            case "fecha_desc": base.sort((a,b) => new Date(b.created_at) - new Date(a.created_at)); break;
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
                else renderGrillaProductos(window.PRODUCTOS_INICIALES.filter(p => Number(p.categoria_id) === catID));
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
    // Comprobante botones
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
    // Registrar venta
    // ============================
    function registrarVenta() {
    volcarUIaVentaActiva();
    const v = ventaActiva();
    const { total } = calcularTotal();

    if (!v.productos.length) {
        return mostrarAlerta("No hay productos en la venta.");
    }

    const tipoComprobante = tipoComprobanteSelect?.value || "boleta";
    const documento = v.cliente?.documento || "";
    const fecha = document.getElementById("fecha_emision")?.value;
    const hora  = document.getElementById("hora_actual")?.value;

    const estadoPago = estadoPagoSelect?.value || "pagado";
    const metodoPago = v.metodo_pago || "";
    const formato = formatoSelect?.value || "a4";

    /* ==================================================
       🔥 BLOQUE CLAVE: determinar monto_pagado
    ================================================== */
    let montoPagado = 0;

    if (estadoPago === "pagado") {
        montoPagado = total;
    } else if (estadoPago === "credito") {
        montoPagado = parseFloat(inputPaga?.value || 0);
    }
    // pendiente => montoPagado = 0
    /* ================================================== */

    if (montoPagado > 0 && !metodoPago) {
        return mostrarAlerta("Debes seleccionar un método de pago.");
    }

    const productosEnviar = v.productos.map(it => ({
        producto_id: it.id,
        cantidad: it.cantidad,
        presentacion: it.tipo_venta
    }));

    fetch("/ventas/registrar", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            tipo_comprobante: tipoComprobante,
            documento: documento,
            fecha: fecha,
            hora: hora,
            monto_pagado: montoPagado,   // ✅ LO QUE EL BACKEND ESPERA
            metodo_pago: metodoPago,
            productos: productosEnviar,
            formato: formato
        })
    })
    .then(res => res.json())
    .then(data => {
        if (!data.success) {
            return mostrarAlerta(data.message || "Error al registrar venta.");
        }

        configurarBotonesComprobante(data);

        // liberar venta confirmada
        const idConfirmada = POS.ventaActivaId;
        delete POS.ventas[idConfirmada];

        asegurarVentaActiva();
        actualizarProductosStock();

        if (modalVentaExitosa) modalVentaExitosa.show();
        try { sonidoExito.play().catch(() => {}); } catch {}

        renderTodo();
    })
    .catch(() => mostrarAlerta("Error inesperado al registrar la venta."));
}
estadoPagoSelect?.addEventListener("change", manejarEstadoVenta);

    if (btnConfirmar3) btnConfirmar3.addEventListener("click", registrarVenta);

    const btnConfirmarDirecto = document.getElementById("btn-confirmar-venta-directo");

    if (btnConfirmarDirecto) {
        btnConfirmarDirecto.addEventListener("click", () => {
            registrarVenta();
        });
    }


    // Nueva venta desde modal
    if (btnNuevaVenta) {
        btnNuevaVenta.addEventListener("click", () => {
            if (modalVentaExitosa) modalVentaExitosa.hide();

            const v = ventaActiva();
            v.productos = [];
            v.cliente = { documento: "", razon: "", direccion: "", no_guardado: false };
            v.metodo_pago = "efectivo";
            v.fase = 1;

            restaurarVentaActivaEnUI();

            if (buscarInput) buscarInput.value = "";
            if (tipoComprobanteSelect) tipoComprobanteSelect.dispatchEvent(new Event("change"));

            if (inputTotalVenta) inputTotalVenta.value = "";
            if (inputPaga) inputPaga.value = "";
            if (inputVuelto) inputVuelto.value = "";

            renderTodo();
            window.scrollTo({ top: 0, behavior: "smooth" });
        });
    }

    // ============================
    // Render general
    // ============================
    function renderTodo() {
        posSaveDebounced(snapshotPOS, 10);

        renderVentasEsperaPanel();
        renderCarritoTreinta();
        actualizarResumen();
        actualizarBotonCarrito();

        // grilla reflejando reservas globales
        if (window.PRODUCTOS_INICIALES && Array.isArray(window.PRODUCTOS_INICIALES)) {
            renderGrillaProductos(window.PRODUCTOS_INICIALES);
        }
    }

    // ============================
    // Inicialización
    // ============================
    asegurarVentaActiva();
    restaurarVentaActivaEnUI();

    if (window.PRODUCTOS_INICIALES && Array.isArray(window.PRODUCTOS_INICIALES)) {
        renderGrillaProductos(window.PRODUCTOS_INICIALES);
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

    renderTodo();

    window.addEventListener("beforeunload", () => {
        try { posSave(snapshotPOS()); } catch {}
    });
    // ============================
    // EXPONER FUNCIONES PARA OTROS ARCHIVOS
    // ============================
    window.volcarUIaVentaActiva = volcarUIaVentaActiva;
    window.actualizarAliasVentaDesdeCliente = actualizarAliasVentaDesdeCliente;
    window.renderVentasEsperaPanel = renderVentasEsperaPanel;

    window.setClienteVentaPOS = function (cliente) {
    const v = ventaActiva();
    if (!v) return;

    v.cliente = {
        documento: cliente.documento || "",
        razon: cliente.razon || "",
        direccion: cliente.direccion || "",
        no_guardado: false
    };

    // refrescar panel inmediatamente
    renderVentasEsperaPanel();

    // persistir
    if (typeof snapshotPOS === "function") posSaveDebounced(snapshotPOS, 50);
    };
    window.ventaActiva = ventaActiva;
    window.renderVentasEsperaPanel = renderVentasEsperaPanel;

    

});

// ===============================
// PRODUCTOS / GRILLA / BÚSQUEDA
// ===============================

document.addEventListener("DOMContentLoaded", () => {

    // ============================
    // ELEMENTOS
    // ============================
    const buscarInput   = document.getElementById("buscar_producto");
    const resultadosDiv = document.getElementById("resultados-busqueda");

    // ============================
    // CACHE DE PRODUCTOS
    // ============================
    window.productosCache = new Map(); // id => producto

    function cacheProductos(lista) {
        if (!Array.isArray(lista)) return;
        lista.forEach(p => {
            if (p && p.id != null) productosCache.set(Number(p.id), p);
        });
    }

    // ============================
    // CARD PRODUCTO
    // ============================
    function crearCardProducto(prod) {

        let nombreImagen = String(prod.imagen || "").trim();
        if (
            nombreImagen.includes("<") ||
            nombreImagen.includes(">") ||
            nombreImagen.includes("=") ||
            nombreImagen.includes('"') ||
            nombreImagen.includes("'")
        ) {
            nombreImagen = "";
        }

        const imgSrc = nombreImagen
            ? `/uploads/productos/${nombreImagen}`
            : "/img/sin-imagen.png";

        const precioBase  = parseFloat(prod.precio_venta || 0) || 0;
        const precioFinal = calcularPrecioFinal(precioBase).toFixed(2);

        const disponible = stockDisponible(prod);
        const stockText  = disponible > 0
            ? `${disponible} disponibles`
            : "Sin stock";

        const v = ventaActiva();
        const enCarrito = (v.productos || [])
            .some(it => Number(it.id) === Number(prod.id));

        return `
            <div class="col-6 col-md-4 col-xl-3 mb-3">
                <div class="product-card agregar-carrito
                    ${disponible <= 0 ? "agotado" : ""}
                    ${enCarrito ? "en-carrito" : ""}"
                    data-id="${prod.id}">

                    <div class="product-img-wrapper">
                        <img src="${imgSrc}" alt="${prod.nombre}" class="product-img">
                        <span class="product-price-badge">S/ ${precioFinal}</span>
                    </div>

                    <div class="product-info">
                        ${IGV_PERCENT > 0
                            ? `<small class="text-success fw-bold d-block" style="font-size:12px;">Incl. IGV</small>`
                            : ""
                        }

                        <div class="product-name" title="${prod.nombre}">
                            ${prod.nombre}
                        </div>

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

    // ============================
    // RENDER GRILLA
    // ============================
    function renderGrillaProductos(lista) {
        if (!resultadosDiv) return;

        resultadosDiv.classList.remove("d-none");
        resultadosDiv.innerHTML = "";

        // tarjeta crear producto (admin)
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
                cardCrear.addEventListener("click", () =>
                    window.location.href = "/productos/create"
                );
            }
        }

        if (!lista || lista.length === 0) {
            resultadosDiv.insertAdjacentHTML("beforeend", `
                <div class="col-12 text-center text-muted py-3">
                    No se encontraron productos
                </div>
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

        // ============================
        // CLICK AGREGAR AL CARRITO
        // ============================
        resultadosDiv
            .querySelectorAll(".product-card.agregar-carrito")
            .forEach(card => {

                card.addEventListener("click", () => {
                    const id = Number(card.dataset.id);
                    const prod = productosCache.get(id);

                    if (!prod) {
                        return mostrarAlerta(
                            "No se pudo obtener la información del producto."
                        );
                    }

                    const disp = stockDisponible(prod);
                    if (disp <= 0) {
                        return mostrarAlerta(
                            `No hay stock para "${prod.nombre}".`
                        );
                    }

                    const v = ventaActiva();
                    if (v.productos.some(it => Number(it.id) === id)) {
                        return mostrarAlerta(
                            `El producto "${prod.nombre}" ya está en la canasta.`
                        );
                    }

                    agregarProductoAVentaActiva(prod);
                });
            });
    }

    // ============================
    // ACTUALIZAR STOCK DESDE BACKEND
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
                if (
                    window.PRODUCTOS_INICIALES &&
                    Array.isArray(window.PRODUCTOS_INICIALES)
                ) {
                    renderGrillaProductos(window.PRODUCTOS_INICIALES);
                }
            });
    }

    // ============================
    // FILTRO CATEGORÍAS
    // ============================
    const botonesCategorias =
        document.querySelectorAll(".btn-filtro-categoria");

    if (botonesCategorias.length) {
        botonesCategorias.forEach(btn => {
            btn.addEventListener("click", () => {

                botonesCategorias.forEach(b =>
                    b.classList.remove("active")
                );
                btn.classList.add("active");

                const catID = Number(btn.dataset.cat);

                if (
                    !window.PRODUCTOS_INICIALES ||
                    !Array.isArray(window.PRODUCTOS_INICIALES)
                ) return;

                if (catID === 0) {
                    return renderGrillaProductos(window.PRODUCTOS_INICIALES);
                }

                const filtrados =
                    window.PRODUCTOS_INICIALES.filter(
                        p => Number(p.categoria_id) === catID
                    );

                renderGrillaProductos(filtrados);
            });
        });
    }

    // ============================
    // BUSCAR PRODUCTO (AJAX)
    // ============================
    if (buscarInput) {
        buscarInput.addEventListener("input", () => {

            const q = buscarInput.value.trim();

            if (!q) {

                const btnActivo =
                    document.querySelector(".btn-filtro-categoria.active");

                const catID = btnActivo
                    ? Number(btnActivo.dataset.cat)
                    : 0;

                if (
                    window.PRODUCTOS_INICIALES &&
                    Array.isArray(window.PRODUCTOS_INICIALES)
                ) {
                    if (catID === 0) {
                        renderGrillaProductos(window.PRODUCTOS_INICIALES);
                    } else {
                        renderGrillaProductos(
                            window.PRODUCTOS_INICIALES.filter(
                                p => Number(p.categoria_id) === catID
                            )
                        );
                    }
                } else {
                    resultadosDiv.innerHTML = "";
                    resultadosDiv.classList.add("d-none");
                }
                return;
            }

            fetch(`/buscar-producto?search=${encodeURIComponent(q)}`)
                .then(res => res.json())
                .then(list => renderGrillaProductos(list))
                .catch(() =>
                    mostrarAlerta("Error al buscar productos")
                );
        });
    }

    // ============================
    // EXPONER
    // ============================
    window.renderGrillaProductos = renderGrillaProductos;
    window.actualizarProductosStock = actualizarProductosStock;

});

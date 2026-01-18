// ===============================
// ORDENAR PRODUCTOS / MODAL
// ===============================

document.addEventListener("DOMContentLoaded", () => {

    // ============================
    // MODAL ORDENAR
    // ============================
    const modalOrdenarEl = document.getElementById("modalOrdenar");
    const modalOrdenar =
        (window.bootstrap && modalOrdenarEl)
            ? new bootstrap.Modal(modalOrdenarEl)
            : null;

    const btnOrdenar = document.getElementById("btn-ordenar");
    let ordenSeleccionada = null;

    if (btnOrdenar && modalOrdenar) {
        btnOrdenar.addEventListener("click", () => modalOrdenar.show());
    }

    // ============================
    // BOTONES DE ORDEN
    // ============================
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

    // ============================
    // ORDENAR PRODUCTOS
    // ============================
    function ordenarProductos(tipo) {

        if (
            !window.PRODUCTOS_INICIALES ||
            !Array.isArray(window.PRODUCTOS_INICIALES)
        ) return;

        const btnActivo =
            document.querySelector(".btn-filtro-categoria.active");

        const catID =
            btnActivo ? Number(btnActivo.dataset.cat) : 0;

        let base = [...window.PRODUCTOS_INICIALES];

        if (catID !== 0) {
            base = base.filter(
                p => Number(p.categoria_id) === catID
            );
        }

        switch (tipo) {
            case "az":
                base.sort((a,b) => a.nombre.localeCompare(b.nombre));
                break;
            case "za":
                base.sort((a,b) => b.nombre.localeCompare(a.nombre));
                break;
            case "precio_asc":
                base.sort((a,b) => (a.precio_venta||0) - (b.precio_venta||0));
                break;
            case "precio_desc":
                base.sort((a,b) => (b.precio_venta||0) - (a.precio_venta||0));
                break;
            case "stock_asc":
                base.sort((a,b) => (a.stock||0) - (b.stock||0));
                break;
            case "stock_desc":
                base.sort((a,b) => (b.stock||0) - (a.stock||0));
                break;
            case "menos_vendidos":
                base.sort((a,b) => (a.total_vendido||0) - (b.total_vendido||0));
                break;
            case "mas_vendidos":
                base.sort((a,b) => (b.total_vendido||0) - (a.total_vendido||0));
                break;
            case "fecha_asc":
                base.sort((a,b) => new Date(a.created_at) - new Date(b.created_at));
                break;
            case "fecha_desc":
                base.sort((a,b) => new Date(b.created_at) - new Date(a.created_at));
                break;
        }

        renderGrillaProductos(base);
    }

    // ============================
    // LIMPIAR ORDEN
    // ============================
    const btnLimpiarOrden = document.getElementById("btn-limpiar-orden");

    if (btnLimpiarOrden) {
        btnLimpiarOrden.addEventListener("click", () => {

            ordenSeleccionada = null;

            document
                .querySelectorAll(".orden-btn")
                .forEach(b => b.classList.remove("active"));

            const btnActivo =
                document.querySelector(".btn-filtro-categoria.active");

            const catID =
                btnActivo ? Number(btnActivo.dataset.cat) : 0;

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
            }

            if (modalOrdenar) modalOrdenar.hide();
        });
    }

    // ============================
    // APLICAR ORDEN
    // ============================
    const btnAplicarOrden = document.getElementById("btn-aplicar-orden");

    if (btnAplicarOrden) {
        btnAplicarOrden.addEventListener("click", () => {
            if (ordenSeleccionada) {
                ordenarProductos(ordenSeleccionada);
            }
            if (modalOrdenar) modalOrdenar.hide();
        });
    }

    // ============================
    // EXPONER
    // ============================
    window.ordenarProductos = ordenarProductos;

});

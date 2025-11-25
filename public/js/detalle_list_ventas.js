// Instancia global del modal
let modal;

document.addEventListener("DOMContentLoaded", function () {
    const modalEl = document.getElementById('detalleVentaModal');
    modal = new bootstrap.Modal(modalEl);
    setupEventListeners();
});

function setupEventListeners() {
    document.querySelectorAll('.venta-row').forEach(fila => {
        fila.addEventListener('click', function () {
            const ventaId = this.getAttribute('data-id');
            mostrarDetallesVenta(ventaId);
        });
    });

    

    document.getElementById('eliminarVentaBtn').addEventListener('click', function () {
        const ventaId = this.getAttribute('data-venta-id');
        confirmarEliminacion(ventaId);
    });
}

function mostrarDetallesVenta(ventaId) {
    if (!ventaId || isNaN(ventaId)) {
        console.error('ID de venta inválido:', ventaId);
        return;
    }

    fetch(`/ventas/${ventaId}`)
        .then(res => {
            if (!res.ok) throw new Error(`Error ${res.status}: ${res.statusText}`);
            return res.json();
        })
        .then(data => {
            actualizarModalConDatos(data, ventaId);

            // 👇 Aquí guardas el ID en el input oculto
            document.getElementById('modalId').value = ventaId;

            modal.show();
        })
        .catch(err => {
            console.error('Error al cargar detalles:', err);
            Swal.fire({
                title: 'Error',
                text: 'No se pudieron cargar los detalles de la venta',
                icon: 'error'
            });
        });
}


function actualizarModalConDatos(data, ventaId) {
    document.getElementById('modalCliente').textContent = data.cliente || 'Sin cliente';
    document.getElementById('modalTipoComprobante').textContent = data.tipo_comprobante || 'No especificado';
    document.getElementById('modalTotal').textContent = `S/ ${parseFloat(data.total || 0).toFixed(2)}`;
    document.getElementById('modalFecha').textContent = data.fecha || 'Fecha no disponible';
    document.getElementById('modalUsuario').textContent = data.usuario || 'Usuario desconocido';

    // ✅ Usar la ganancia ya calculada
    let gananciaTotal = 0;
    if (data.productos && data.productos.length > 0) {
        data.productos.forEach(p => {
            gananciaTotal += parseFloat(p.ganancia || 0);
        });
    }
    document.getElementById('modalGanancia').textContent = `S/ ${gananciaTotal.toFixed(2)}`;

    // Mostrar estado con estilo
    const estadoElement = document.getElementById('modalEstado');
    estadoElement.textContent = data.estado || 'pagada';
    estadoElement.className = 'badge rounded-pill ' +
        (data.estado === 'pagada' ? 'bg-success' : 'bg-warning');

    // Lista de productos
    const ul = document.getElementById('modalProductos');
    ul.innerHTML = '';
    if (data.productos && data.productos.length > 0) {
        data.productos.forEach(p => {
            const li = document.createElement('li');
            li.className = 'list-group-item';
            const tipo = p.tipo_venta === 'mayor' ? ' (mayor)' : '';
            li.textContent = `${p.nombre} – S/ ${parseFloat(p.precio_unitario).toFixed(2)} x ${p.cantidad}${tipo}`;
            ul.appendChild(li);
        });
    } else {
        const li = document.createElement('li');
        li.className = 'list-group-item text-muted';
        li.textContent = 'No hay productos registrados';
        ul.appendChild(li);
    }

    document.getElementById('editarVentaBtn').setAttribute('data-venta-id', ventaId);
    document.getElementById('eliminarVentaBtn').setAttribute('data-venta-id', ventaId);
}

async function confirmarEliminacion(ventaId) {
    if (!ventaId || isNaN(ventaId)) {
        Swal.fire('Error', 'ID de venta no válido', 'error');
        return;
    }

    const result = await Swal.fire({
        title: '¿Eliminar venta?',
        text: "¡Esta acción no se puede deshacer!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    });

    if (result.isConfirmed) {
        try {
            Swal.showLoading();
            await eliminarVenta(ventaId);

            Swal.fire({
                title: '¡Eliminada!',
                text: 'La venta se eliminó correctamente',
                icon: 'success',
                timer: 1500,
                showConfirmButton: false
            });

            setTimeout(() => {
                if (modal) modal.hide();
                window.location.reload();
            }, 1500);
        } catch (error) {
            console.error('Error al eliminar:', error);
            Swal.fire({
                title: 'Error',
                text: error.message || 'No se pudo eliminar la venta',
                icon: 'error'
            });
        }
    }
}

async function eliminarVenta(ventaId) {
    const response = await fetch(`/ventas/${ventaId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    });

    const data = await response.json();

    if (!response.ok) {
        throw new Error(data.message || 'Error al eliminar la venta');
    }

    return data;
}

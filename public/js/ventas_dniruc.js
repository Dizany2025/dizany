document.addEventListener('DOMContentLoaded', function () {
    const token = "65380308035e6817f4baf2770fd241dfde303c06eec3376cc7694c53bcc8e83d"; // REEMPLAZA ESTO CON TU TOKEN

    const documento = document.getElementById('documento');
    const razon = document.getElementById('razon_social');
    const direccion = document.getElementById('direccion');
    const estado = document.getElementById('estado_ruc');
    const guardarClienteBtn = document.getElementById('guardar-cliente-btn');

    // Función para verificar si el cliente existe en la base de datos (consulta en tiempo real)
    function buscarClienteEnBaseDeDatos(dniRuc) {
        return fetch(`/buscar-cliente/${dniRuc}`)
            .then(response => response.json())
            .then(data => data);
    }

    documento.addEventListener('input', () => {
        const valor = documento.value.trim();

        if (valor.length >= 8) {
            // Primero, verificamos si el cliente ya está registrado en la base de datos
            buscarClienteEnBaseDeDatos(valor)
                .then(cliente => {
                    if (cliente.encontrado) {
                        // Si el cliente existe en la base de datos
                        razon.value = cliente.nombre;
                        direccion.value = cliente.direccion;
                        estado.textContent = '';
                        guardarClienteBtn.style.display = 'none'; // Ocultamos el botón si ya existe
                    } else {
                        // Si el cliente no está en la base de datos, consultamos la API de RENIEC
                        if (valor.length === 8) {
                            // Consulta DNI
                            fetch(`https://apiperu.dev/api/dni/${valor}`, {
                                headers: {
                                    Authorization: `Bearer ${token}`
                                }
                            })
                            .then(res => res.json())
                            .then(data => {
                                if (data.success) {
                                    razon.value = `${data.data.nombres} ${data.data.apellido_paterno} ${data.data.apellido_materno}`;
                                    direccion.value = 'No disponible'; // Si no hay dirección
                                    estado.textContent = '';
                                    guardarClienteBtn.style.display = 'inline-block'; // Mostramos el botón de guardar
                                } else {
                                    razon.value = '';
                                    direccion.value = '';
                                    estado.textContent = '❌ DNI no encontrado';
                                    guardarClienteBtn.style.display = 'inline-block'; // Mostramos el botón para guardar
                                }
                            })
                            .catch(() => estado.textContent = '⚠️ Error al consultar DNI');
                        } else if (valor.length === 11) {
                            // Consulta RUC
                            fetch(`https://apiperu.dev/api/ruc/${valor}`, {
                                headers: {
                                    Authorization: `Bearer ${token}`
                                }
                            })
                            .then(res => res.json())
                            .then(data => {
                                if (data.success) {
                                    razon.value = data.data.nombre_o_razon_social;
                                    direccion.value = data.data.direccion || 'Sin dirección';
                                    estado.textContent = `✅ ${data.data.estado}`;
                                    guardarClienteBtn.style.display = 'inline-block'; // Mostramos el botón de guardar
                                } else {
                                    razon.value = '';
                                    direccion.value = '';
                                    estado.textContent = '❌ RUC no encontrado';
                                    guardarClienteBtn.style.display = 'inline-block'; // Mostramos el botón para guardar
                                }
                            })
                            .catch(() => estado.textContent = '⚠️ Error al consultar RUC');
                        }
                    }
                });
        } else {
            estado.textContent = 'ℹ️ Ingresa DNI (8) o RUC (11)';
            razon.value = '';
            direccion.value = '';
            estado.textContent = '';
        }
    });

    // Función para guardar el cliente en la base de datos
    guardarClienteBtn.addEventListener('click', function() {
    const dniRuc = documento.value.trim();
    const razonSocial = razon.value;
    const direccionTexto = direccion.value; // ✅ usamos otro nombre

    fetch('/guardar-cliente', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            dni_ruc: dniRuc,
            razon_social: razonSocial,
            direccion: direccionTexto
        })
    })
    .then(response => response.json())
    .then(data => {
    if (data.exito) {
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title: 'Cliente guardado correctamente',
            showConfirmButton: false,
            timer: 2000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        });

        guardarClienteBtn.style.display = 'none'; // Ocultar botón
    } else {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: data.mensaje || 'Error al guardar el cliente.'
        });
    }
})

});

});

document.addEventListener("DOMContentLoaded", () => {
    const input = document.getElementById("codigo_barras");
    const errorDiv = document.getElementById("codigo_barras_error");
    let timer;

    // Escucha del input
    input.addEventListener("input", () => {
        const codigo = input.value.trim();

        clearTimeout(timer); // Detiene las peticiones anteriores
        if (!codigo) {
            input.classList.remove("is-invalid");
            errorDiv.classList.add("d-none"); // Oculta el error
            return;
        }

        // Hacer la consulta después de 300ms para evitar peticiones demasiado rápidas
        timer = setTimeout(() => {
            fetch(`/productos/validar-codigo-barras?codigo_barras=${encodeURIComponent(codigo)}`)
                .then(res => res.json())
                .then(data => {
                    if (data.exists) {
                        // Si el código existe, muestra el error
                        input.classList.add("is-invalid");
                        errorDiv.classList.remove("d-none");
                    } else {
                        // Si no existe, elimina el error
                        input.classList.remove("is-invalid");
                        errorDiv.classList.add("d-none");
                    }
                })
                .catch(error => {
                    console.error("Error al validar el código de barras:", error);
                });
        }, 300); // El retraso de 300ms es el "debounce"
    });
});

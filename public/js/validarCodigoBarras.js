document.addEventListener("DOMContentLoaded", () => {
    const input = document.getElementById("codigo_barras");
    const errorDiv = document.getElementById("codigo_barras_error");
    let timer;

    input.addEventListener("input", () => {
        const codigo = input.value.trim();

        clearTimeout(timer); // Detiene llamadas anteriores
        if (!codigo) {
            input.classList.remove("is-invalid");
            errorDiv.classList.add("d-none");
            return;
        }

        // Esperar 300ms antes de hacer la petición (evita demasiadas consultas al escribir rápido)
        timer = setTimeout(() => {
            fetch(`/productos/validar-codigo-barras?codigo_barras=${encodeURIComponent(codigo)}`)
                .then(res => res.json())
                .then(data => {
                    if (data.exists) {
                        input.classList.add("is-invalid");
                        errorDiv.classList.remove("d-none");
                    } else {
                        input.classList.remove("is-invalid");
                        errorDiv.classList.add("d-none");
                    }
                })
                .catch(error => {
                    console.error("Error al validar el código de barras:", error);
                });
        }, 300);
    });
});

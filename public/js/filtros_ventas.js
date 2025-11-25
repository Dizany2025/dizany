document.addEventListener("DOMContentLoaded", function () {
    const inputFecha = document.getElementById("filter-date");
    const filtroRango = document.getElementById("filter-type");

    if (!inputFecha || !filtroRango) return;

    flatpickr("#filter-date", {
        dateFormat: "Y-m-d",
        onDayCreate: function (dObj, dStr, fp, dayElem) {
            const selectedDate = new Date(fp.input.value);
            const rango = filtroRango.value;
            const currentDate = dayElem.dateObj;
            const today = new Date();

            if (rango === "semanal") {
                const startOfWeek = new Date(selectedDate);
                startOfWeek.setDate(selectedDate.getDate() - selectedDate.getDay()); // domingo
                const endOfWeek = new Date(startOfWeek);
                endOfWeek.setDate(startOfWeek.getDate() + 6); // sábado

                if (currentDate >= startOfWeek && currentDate <= today && currentDate <= endOfWeek) {
                    dayElem.classList.add("highlight-yellow");
                }

            } else if (rango === "mensual") {
                const startOfMonth = new Date(selectedDate.getFullYear(), selectedDate.getMonth(), 1);
                const endOfMonth = new Date(today); // solo hasta hoy, no todo el mes

                if (
                    currentDate >= startOfMonth &&
                    currentDate <= endOfMonth &&
                    currentDate.getMonth() === selectedDate.getMonth()
                ) {
                    dayElem.classList.add("highlight-green");
                }
            }
        }

    });
});
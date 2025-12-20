(() => {
    // ⛔ Evita ejecutar 2 veces el script
    if (window.__dashboardLoaded) return;
    window.__dashboardLoaded = true;

    document.addEventListener('DOMContentLoaded', () => {

        // 1️⃣ Chart.js debe existir
        if (typeof window.Chart === 'undefined') {
            console.warn('Chart.js no está cargado (dashboard)');
            return;
        }

        // 2️⃣ Canvas debe existir (solo dashboard)
        const canvas = document.getElementById('flujoCajaChart');
        if (!canvas) return;

        // 3️⃣ Datos deben existir
        if (
            !Array.isArray(window.dashboardLabels) ||
            !Array.isArray(window.dashboardIngresos) ||
            !Array.isArray(window.dashboardEgresos)
        ) {
            console.warn('Datos del dashboard incompletos');
            return;
        }

        const ctx = canvas.getContext('2d');

        // 4️⃣ Destruir gráfico previo si existe
        if (window.flujoChart instanceof Chart) {
            window.flujoChart.destroy();
        }

        // 5️⃣ Crear gráfico
        window.flujoChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: window.dashboardLabels,
                datasets: [
                    {
                        label: 'Ingresos',
                        data: window.dashboardIngresos,
                        borderColor: '#16a34a',
                        backgroundColor: 'rgba(22,163,74,0.15)',
                        tension: 0.4,
                        fill: true,
                        pointRadius: 3
                    },
                    {
                        label: 'Egresos',
                        data: window.dashboardEgresos,
                        borderColor: '#dc2626',
                        backgroundColor: 'rgba(220,38,38,0.15)',
                        tension: 0.4,
                        fill: true,
                        pointRadius: 3
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            boxWidth: 14,
                            usePointStyle: true
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => ` S/ ${ctx.parsed.y.toFixed(2)}`
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: (value) => `S/ ${value}`
                        }
                    }
                }
            }
        });
    });
})();

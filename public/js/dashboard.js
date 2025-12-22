document.addEventListener('DOMContentLoaded', () => {
    if (typeof Chart === 'undefined') return;

    const canvas = document.getElementById('flujoCajaChart');
    if (!canvas) return;

    const ctx = canvas.getContext('2d');

    if (window.flujoChart) {
        window.flujoChart.destroy();
    }

    window.flujoChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: window.dashboardLabels,
            datasets: [
                {
                    label: 'Ingresos',
                    data: window.dashboardIngresos,
                    backgroundColor: '#22c55e',
                    borderRadius: 8,
                    barThickness: 24
                },
                {
                    label: 'Egresos',
                    data: window.dashboardEgresos,
                    backgroundColor: '#ef4444',
                    borderRadius: 8,
                    barThickness: 24
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        boxWidth: 10
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function (ctx) {
                            return `${ctx.dataset.label}: S/ ${ctx.parsed.y.toFixed(2)}`;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: '#e5e7eb'
                    },
                    ticks: {
                        callback: value => `S/ ${value}`
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
});

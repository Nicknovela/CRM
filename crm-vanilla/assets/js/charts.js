/**
 * CRM — Dashboard charts using Chart.js
 */
document.addEventListener('DOMContentLoaded', () => {
    const canvas = document.getElementById('funnelChart');
    if (!canvas || !window.funnelData) return;

    const labels = window.funnelData.map(d => d.name);
    const counts = window.funnelData.map(d => parseInt(d.count));
    const colors = window.funnelData.map(d => d.color || '#6B7280');

    new Chart(canvas, {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                label: 'Negocios por etapa',
                data: counts,
                backgroundColor: colors.map(c => c + 'CC'),
                borderColor: colors,
                borderWidth: 2,
                borderRadius: 6,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        afterLabel: (ctx) => {
                            const d = window.funnelData[ctx.dataIndex];
                            if (d && d.total > 0) {
                                const amount = parseFloat(d.total).toLocaleString('es-BO', { minimumFractionDigits: 2 });
                                return `Valor: ${amount}`;
                            }
                            return '';
                        },
                    },
                },
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1, precision: 0 },
                    grid: { color: '#f3f4f6' },
                },
                x: {
                    grid: { display: false },
                },
            },
        },
    });
});

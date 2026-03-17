import Chart from 'chart.js/auto';

// Alpine.js data component for funnel/bar chart
window.funnelChart = function (data) {
    return {
        chart: null,
        init() {
            this.chart = new Chart(this.$refs.chart, {
                type: 'bar',
                data: {
                    labels: data.labels,
                    datasets: [{
                        label: 'Negocios',
                        data: data.values,
                        backgroundColor: data.colors ?? data.values.map(() => '#6366f1'),
                        borderRadius: 6,
                        borderSkipped: false,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, ticks: { stepSize: 1 } },
                    },
                },
            });
        },
        destroy() {
            this.chart?.destroy();
        },
    };
};

// Timeline line chart
window.timelineChart = function (data) {
    return {
        chart: null,
        init() {
            this.chart = new Chart(this.$refs.chart, {
                type: 'line',
                data: {
                    labels: data.labels,
                    datasets: [{
                        label: 'Ingresos',
                        data: data.amounts,
                        borderColor: '#6366f1',
                        backgroundColor: 'rgba(99, 102, 241, 0.1)',
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#6366f1',
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true },
                    },
                },
            });
        },
        destroy() {
            this.chart?.destroy();
        },
    };
};

// Donut chart for vertical breakdown
window.donutChart = function (data) {
    return {
        chart: null,
        init() {
            this.chart = new Chart(this.$refs.chart, {
                type: 'doughnut',
                data: {
                    labels: data.labels,
                    datasets: [{
                        data: data.values,
                        backgroundColor: data.colors ?? ['#6366f1', '#f59e0b', '#10b981', '#ef4444', '#3b82f6'],
                        borderWidth: 2,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'right', labels: { usePointStyle: true, padding: 16 } },
                    },
                    cutout: '65%',
                },
            });
        },
        destroy() {
            this.chart?.destroy();
        },
    };
};

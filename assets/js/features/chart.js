// Import Chart.js
import Chart from 'chart.js/auto';

export const initCharts = () => {
    // Registration Stats Chart
    const initRegistrationChart = () => {
        const ctx = document.getElementById('registrationChart');
        if (!ctx) return;

        return new Chart(ctx, {
            type: 'line',
            data: {
                labels: [], // Will be populated with dates
                datasets: [{
                    label: 'Daily Registrations',
                    data: [], // Will be populated with counts
                    borderColor: '#3b82f6',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Registration Trends'
                    }
                }
            }
        });
    };

    // Payment Stats Chart
    const initPaymentChart = () => {
        const ctx = document.getElementById('paymentChart');
        if (!ctx) return;

        return new Chart(ctx, {
            type: 'bar',
            data: {
                labels: [], // Will be populated with dates
                datasets: [{
                    label: 'Daily Payments',
                    data: [], // Will be populated with amounts
                    backgroundColor: '#10b981'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Payment Statistics'
                    }
                }
            }
        });
    };

    // Update chart data
    const updateChartData = async (chart, endpoint) => {
        try {
            const response = await fetch(endpoint);
            const data = await response.json();
            
            chart.data.labels = data.labels;
            chart.data.datasets[0].data = data.values;
            chart.update();
        } catch (error) {
            console.error('Error updating chart:', error);
        }
    };

    // Initialize all charts
    const charts = {
        registration: initRegistrationChart(),
        payment: initPaymentChart()
    };

    // Set up real-time updates
    if (charts.registration || charts.payment) {
        setInterval(() => {
            if (charts.registration) {
                updateChartData(charts.registration, '/api/stats/registrations');
            }
            if (charts.payment) {
                updateChartData(charts.payment, '/api/stats/payments');
            }
        }, 60000); // Update every minute
    }
};

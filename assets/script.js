// Sengaja tidak memakai auto-refresh agar tombol verifikasi tidak hilang setelah 1 detik.
console.log('Berani Cerdas dashboard ready');

document.addEventListener("DOMContentLoaded", () => {
    if (typeof chartData !== 'undefined') {
        const ctx = document.getElementById('statusChart').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Penerima Aktif', 'Menunggu', 'Ditolak/Exception'],
                datasets: [{
                    data: [chartData.aktif, chartData.menunggu, chartData.ditolak],
                    backgroundColor: [
                        '#10b981', // green
                        '#f59e0b', // yellow/orange
                        '#ef4444'  // red
                    ],
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            font: {
                                family: 'Inter',
                                weight: 'bold'
                            }
                        }
                    }
                },
                cutout: '70%'
            }
        });
    }
});

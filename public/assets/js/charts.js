document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('[data-chart]').forEach((canvas) => {
    const labels = JSON.parse(canvas.dataset.labels || '[]');
    const values = JSON.parse(canvas.dataset.values || '[]');
    const label = canvas.dataset.label || 'Wartosc';

    if (!labels.length || !values.length || typeof Chart === 'undefined') {
      return;
    }

    new Chart(canvas, {
      type: 'line',
      data: {
        labels,
        datasets: [{
          label,
          data: values,
          borderColor: '#1d4ed8',
          backgroundColor: 'rgba(29, 78, 216, 0.16)',
          borderWidth: 3,
          tension: 0.3,
          fill: true,
          pointRadius: 4,
          pointHoverRadius: 6
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: true
          }
        },
        scales: {
          y: {
            beginAtZero: false,
            ticks: {
              color: '#475569'
            },
            grid: {
              color: 'rgba(148, 163, 184, 0.2)'
            }
          },
          x: {
            ticks: {
              color: '#475569'
            },
            grid: {
              display: false
            }
          }
        }
      }
    });
  });
});

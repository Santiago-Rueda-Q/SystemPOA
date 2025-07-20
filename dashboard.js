document.getElementById('logoutBtn').addEventListener('click', () => {
  alert('Sesión cerrada');
  window.location.href = 'login.html';
});

function crearGrafica(id, label, porcentaje) {
  const canvas = document.getElementById(id);
  if (!canvas) return;

  const ctx = canvas.getContext('2d');
  new Chart(ctx, {
    type: 'doughnut',
    data: {
      labels: ['Avance', 'Faltante'],
      datasets: [{
        data: [porcentaje, 100 - porcentaje],
        backgroundColor: ['#3b8cde', '#e2e8f0'],
        borderWidth: 1
      }]
    },
    options: {
      plugins: {
        legend: {
          position: 'bottom'
        },
        tooltip: {
          callbacks: {
            label: function (tooltipItem) {
              return `${tooltipItem.label}: ${tooltipItem.raw}%`;
            }
          }
        }
      },
      cutout: '70%'
    }
  });
}

// Valores simulados de ejemplo
crearGrafica('graficaComponente1', 'Componente 1', 45);
crearGrafica('graficaComponente2', 'Componente 2', 62);
crearGrafica('graficaComponente3', 'Componente 3', 30);
crearGrafica('graficaComponente4', 'Componente 4', 75);
crearGrafica('graficaComponente5', 'Componente 5', 90);

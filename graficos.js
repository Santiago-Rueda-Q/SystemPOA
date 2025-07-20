const componentes = [65, 80, 50, 90, 70]; // Porcentajes de avance

componentes.forEach((valor, i) => {
  new Chart(document.getElementById(`componente${i + 1}`), {
    type: 'doughnut',
    data: {
      labels: ['Completado', 'Faltante'],
      datasets: [{
        label: `Componente ${i + 1}`,
        data: [valor, 100 - valor],
        backgroundColor: ['#3b8cde', '#e0e0e0'],
        borderWidth: 1
      }]
    },
    options: {
      plugins: {
        title: {
          display: true,
          text: `Componente ${i + 1}`
        },
        legend: {
          display: false
        }
      },
      cutout: '70%',
      responsive: false,
      maintainAspectRatio: false
    }
  });
});

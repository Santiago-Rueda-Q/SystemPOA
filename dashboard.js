// Configuración de colores del tema
const colors = {
  primary: '#3b8cde',
  secondary: '#10b981',
  tertiary: '#f59e0b',
  quaternary: '#8b5cf6',
  light: '#e2e8f0',
  dark: '#07396b'
};

// Función para crear gráficas mejoradas
function crearGrafica(id, label, porcentaje, color = colors.primary) {
  const canvas = document.getElementById(id);
  if (!canvas) return;

  const ctx = canvas.getContext('2d');
  
  // Gradiente para el progreso
  const gradient = ctx.createConicGradient(0, canvas.width / 2, canvas.height / 2);
  gradient.addColorStop(0, color);
  gradient.addColorStop(porcentaje / 100, color);
  gradient.addColorStop(porcentaje / 100, colors.light);
  gradient.addColorStop(1, colors.light);

  new Chart(ctx, {
    type: 'doughnut',
    data: {
      labels: ['Completado', 'Pendiente'],
      datasets: [{
        data: [porcentaje, 100 - porcentaje],
        backgroundColor: [color, colors.light],
        borderWidth: 0,
        cutout: '75%'
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          display: false
        },
        tooltip: {
          callbacks: {
            label: function(context) {
              return `${context.label}: ${context.raw}%`;
            }
          },
          backgroundColor: 'rgba(0, 0, 0, 0.8)',
          titleColor: '#ffffff',
          bodyColor: '#ffffff',
          cornerRadius: 8
        }
      },
      animation: {
        animateRotate: true,
        animateScale: true,
        duration: 2000,
        easing: 'easeOutQuart'
      }
    }
  });
}

// Crear las gráficas con colores específicos
document.addEventListener('DOMContentLoaded', function() {
  // Animación de entrada para las tarjetas de estadísticas
  animateStatsCards();
  
  // Crear gráficas con diferentes colores
  setTimeout(() => {
    crearGrafica('graficaComponente1', 'Componente Docencia', 45, colors.primary);
    crearGrafica('graficaComponente2', 'Componente Investigación', 62, colors.secondary);
    crearGrafica('graficaComponente3', 'Componente Extensión', 30, colors.tertiary);
    crearGrafica('graficaComponente4', 'Componente Administrativo', 75, colors.quaternary);
  }, 500);
  
  // Animación de números contador
  animateCounters();
});

// Animación para las tarjetas de estadísticas
function animateStatsCards() {
  const statCards = document.querySelectorAll('.stat-card');
  
  statCards.forEach((card, index) => {
    card.style.opacity = '0';
    card.style.transform = 'translateY(30px)';
    card.style.transition = 'all 0.6s ease';
    
    setTimeout(() => {
      card.style.opacity = '1';
      card.style.transform = 'translateY(0)';
    }, index * 150);
  });
}

// Animación contador para los números
function animateCounters() {
  const counters = document.querySelectorAll('.stat-number');
  
  counters.forEach(counter => {
    const target = parseInt(counter.textContent.replace(/,/g, ''));
    const duration = 2000;
    const increment = target / (duration / 16);
    let current = 0;
    
    const updateCounter = () => {
      current += increment;
      if (current < target) {
        counter.textContent = Math.floor(current).toLocaleString();
        requestAnimationFrame(updateCounter);
      } else {
        counter.textContent = target.toLocaleString();
      }
    };
    
    setTimeout(updateCounter, 500);
  });
}

// Manejo del botón de logout
document.getElementById('logoutBtn').addEventListener('click', function(e) {
  e.preventDefault();
  
  // Mostrar confirmación con estilo personalizado
  if (confirm('¿Estás seguro de que deseas cerrar sesión?')) {
    // Crear efecto de fade out
    document.body.style.transition = 'opacity 0.5s ease';
    document.body.style.opacity = '0';
    
    setTimeout(() => {
      // Redirigir al script de logout
      window.location.href = 'logout.php';
    }, 500);
  }
});

// Efectos de hover mejorados para las tarjetas
document.querySelectorAll('.stat-card, .chart-card').forEach(card => {
  card.addEventListener('mouseenter', function() {
    this.style.transform = 'translateY(-5px) scale(1.02)';
    this.style.transition = 'all 0.3s ease';
  });
  
  card.addEventListener('mouseleave', function() {
    this.style.transform = 'translateY(0) scale(1)';
  });
});

// Actualización en tiempo real de la hora
function updateTime() {
  const now = new Date();
  const timeString = now.toLocaleTimeString('es-CO', {
    hour: '2-digit',
    minute: '2-digit'
  });
  
  // Si tienes un elemento para mostrar la hora
  const timeElement = document.getElementById('current-time');
  if (timeElement) {
    timeElement.textContent = timeString;
  }
}

// Actualizar cada minuto
setInterval(updateTime, 60000);
updateTime(); // Llamar inmediatamente

// Función para cargar datos dinámicos (simulada)
function loadDynamicData() {
  // Simular carga de datos del servidor
  setTimeout(() => {
    // Actualizar estadísticas
    updateStats();
    // Actualizar gráficas
    updateCharts();
  }, 1000);
}

function updateStats() {
  // Simular actualización de estadísticas en tiempo real
  const statNumbers = document.querySelectorAll('.stat-number');
  statNumbers.forEach(stat => {
    const currentValue = parseInt(stat.textContent.replace(/,/g, ''));
    const randomChange = Math.floor(Math.random() * 10) - 5; // -5 a +5
    const newValue = Math.max(0, currentValue + randomChange);
    stat.textContent = newValue.toLocaleString();
  });
}

function updateCharts() {
  // Esta función se puede usar para actualizar las gráficas con datos reales
  console.log('Actualizando gráficas con datos del servidor...');
}

// Cargar datos dinámicos al inicio
loadDynamicData();

// Sistema de notificaciones (opcional)
function showNotification(message, type = 'info') {
  const notification = document.createElement('div');
  notification.className = `notification notification-${type}`;
  notification.textContent = message;
  notification.style.cssText = `
    position: fixed;
    top: 20px;
    right: 20px;
    background: ${type === 'success' ? colors.secondary : colors.primary};
    color: white;
    padding: 16px 24px;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    z-index: 1000;
    transform: translateX(100%);
    transition: transform 0.3s ease;
  `;
  
  document.body.appendChild(notification);
  
  setTimeout(() => {
    notification.style.transform = 'translateX(0)';
  }, 100);
  
  setTimeout(() => {
    notification.style.transform = 'translateX(100%)';
    setTimeout(() => {
      document.body.removeChild(notification);
    }, 300);
  }, 3000);
}

// Ejemplo de uso de notificaciones
// showNotification('Dashboard cargado correctamente', 'success');
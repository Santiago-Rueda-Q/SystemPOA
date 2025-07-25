// Configuración de colores del tema
const colors = {
  primary: '#3b8cde',
  secondary: '#10b981',
  tertiary: '#f59e0b',
  quaternary: '#8b5cf6',
  light: '#e2e8f0',
  dark: '#07396b'
};

// Variables globales
let currentEditType = '';
let statsData = {};

// Función para mostrar notificaciones
function mostrarNotificacion(mensaje, tipo = 'info') {
  const notification = document.createElement('div');
  notification.className = `notification notification-${tipo}`;
  notification.textContent = mensaje;
  notification.style.cssText = `
    position: fixed;
    top: 20px;
    right: 20px;
    background: ${tipo === 'success' ? colors.secondary : tipo === 'error' ? '#ef4444' : colors.primary};
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
      if (document.body.contains(notification)) {
        document.body.removeChild(notification);
      }
    }, 300);
  }, 3000);
}

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

// Función para cargar estadísticas desde la base de datos
async function cargarEstadisticas() {
  try {
    const response = await fetch('get_stats.php', {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
      },
      credentials: 'same-origin'
    });
    
    // Verificar si la respuesta es exitosa
    if (!response.ok) {
      throw new Error(`HTTP ${response.status}: ${response.statusText}`);
    }
    
    // Verificar que el contenido sea JSON
    const contentType = response.headers.get('Content-Type');
    if (!contentType || !contentType.includes('application/json')) {
      throw new Error('La respuesta del servidor no es JSON válido');
    }
    
    const result = await response.json();
    
    if (result.success) {
      statsData = result.data;
      actualizarInterfaz(statsData);
      mostrarNotificacion('Estadísticas cargadas correctamente', 'success');
    } else {
      console.error('Error al cargar estadísticas:', result.error);
      mostrarNotificacion(result.error || 'Error al cargar las estadísticas', 'error');
    }
  } catch (error) {
    console.error('Error de conexión:', error);
    mostrarNotificacion('Error de conexión al servidor: ' + error.message, 'error');
    
    // Cargar datos de respaldo
    statsData = {
      total: 0,
      tecnico: 0,
      tecnologia: 0,
      profesional: 0
    };
    actualizarInterfaz(statsData);
  }
}

// Función para actualizar la interfaz con los datos
function actualizarInterfaz(data) {
  // Actualizar contadores con animación
  animateCounter('total-count', data.total);
  animateCounter('tecnico-count', data.tecnico);
  animateCounter('tecnologia-count', data.tecnologia);
  animateCounter('profesional-count', data.profesional);
}

// Función para animar contadores
function animateCounter(elementId, targetValue) {
  const element = document.getElementById(elementId);
  if (!element) return;
  
  const currentValue = parseInt(element.textContent.replace(/,/g, '')) || 0;
  const increment = (targetValue - currentValue) / 30;
  let current = currentValue;
  
  const updateCounter = () => {
    current += increment;
    if ((increment > 0 && current < targetValue) || (increment < 0 && current > targetValue)) {
      element.textContent = Math.floor(current).toLocaleString();
      requestAnimationFrame(updateCounter);
    } else {
      element.textContent = targetValue.toLocaleString();
    }
  };
  
  updateCounter();
}

// Función para abrir modal de edición
function openEditModal(type, title) {
  if (type === 'total') {
    // Para el total, mostrar desglose en lugar de editar
    mostrarDesglose();
    return;
  }
  
  currentEditType = type; // Mantener el tipo interno (tecnico, tecnologia, profesional)
  document.getElementById('modalTitle').textContent = `Editar ${title}`;
  document.getElementById('cantidadInput').value = statsData[type] || 0;
  document.getElementById('editModal').style.display = 'block';
  
  // Limpiar alertas
  document.getElementById('alertContainer').innerHTML = '';
}

// Función para cerrar modal
function closeEditModal() {
  document.getElementById('editModal').style.display = 'none';
  currentEditType = '';
}

// Función para mostrar desglose del total
function mostrarDesglose() {
  const desglose = `
    <div class="space-y-4">
      <div class="text-lg font-semibold text-gray-800 mb-4">Desglose de estudiantes inscritos:</div>
      
      <div class="flex items-center space-x-3">
        <i class="fas fa-tools text-green-600"></i>
        <span class="font-medium">Técnico:</span>
        <span class="font-bold text-gray-900">${statsData.tecnico.toLocaleString()} estudiantes</span>
      </div>
      
      <div class="flex items-center space-x-3">
        <i class="fas fa-laptop-code text-yellow-600"></i>
        <span class="font-medium">Tecnología:</span>
        <span class="font-bold text-gray-900">${statsData.tecnologia.toLocaleString()} estudiantes</span>
      </div>
      
      <div class="flex items-center space-x-3">
        <i class="fas fa-university text-purple-600"></i>
        <span class="font-medium">Profesional:</span>
        <span class="font-bold text-gray-900">${statsData.profesional.toLocaleString()} estudiantes</span>
      </div>
      
      <div class="border-t pt-3 mt-4">
        <div class="flex items-center space-x-3">
          <i class="fas fa-chart-bar text-blue-600"></i>
          <span class="font-semibold">Total:</span>
          <span class="font-bold text-blue-600 text-lg">${statsData.total.toLocaleString()} estudiantes</span>
        </div>
      </div>
    </div>
  `;
  
  // Crear modal personalizado en lugar de alert
  const modal = document.createElement('div');
  modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
  modal.innerHTML = `
    <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4 shadow-xl">
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-semibold text-gray-900">Estadísticas Detalladas</h3>
        <button onclick="this.closest('.fixed').remove()" class="text-gray-400 hover:text-gray-600">
          <i class="fas fa-times text-xl"></i>
        </button>
      </div>
      ${desglose}
      <div class="mt-6">
        <button onclick="this.closest('.fixed').remove()" class="w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition-colors">
          <i class="fas fa-check mr-2"></i>Cerrar
        </button>
      </div>
    </div>
  `;
  
  document.body.appendChild(modal);
}

// Función para mostrar alertas en el modal
function mostrarAlerta(mensaje, tipo) {
  const alertContainer = document.getElementById('alertContainer');
  const alertClass = tipo === 'success' ? 'alert-success' : 'alert-error';
  
  alertContainer.innerHTML = `
    <div class="alert ${alertClass}" style="display: block;">
      ${mensaje}
    </div>
  `;
  
  // Ocultar alerta después de 5 segundos
  setTimeout(() => {
    alertContainer.innerHTML = '';
  }, 5000);
}

// Función para actualizar estadística
// Función para actualizar estadística
async function actualizarEstadistica(nivel, cantidad) {
  const loadingEl = document.getElementById('loading');
  const submitBtn = document.querySelector('#editForm button[type="submit"]');
  
  // Mostrar loading
  loadingEl.style.display = 'block';
  submitBtn.disabled = true;
  
  // Mapear el tipo de la interfaz al nombre real en la base de datos
  const nivelMapping = {
    'tecnico': 'Técnico',
    'tecnologia': 'Tecnología', 
    'profesional': 'Profesional'
  };
  
  try {
    const response = await fetch('update_stats.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      credentials: 'same-origin',
      body: JSON.stringify({
        nivel_formacion: nivelMapping[nivel] || nivel,
        cantidad: cantidad
      })
    });
    
    // Verificar si la respuesta es exitosa
    if (!response.ok) {
      throw new Error(`HTTP ${response.status}: ${response.statusText}`);
    }
    
    // Verificar que el contenido sea JSON
    const contentType = response.headers.get('Content-Type');
    if (!contentType || !contentType.includes('application/json')) {
      // Intentar obtener el texto de respuesta para debug
      const responseText = await response.text();
      console.error('Respuesta no JSON:', responseText);
      throw new Error('La respuesta del servidor no es JSON válido');
    }
    
    const result = await response.json();
    
    if (result.success) {
      // Actualizar datos locales
      statsData = result.data;
      
      // Actualizar interfaz
      actualizarInterfaz(statsData);
      
      // Mostrar mensaje de éxito
      mostrarAlerta('Estadística actualizada correctamente', 'success');
      mostrarNotificacion('Estadística actualizada correctamente', 'success');
      
      // Cerrar modal después de 2 segundos
      setTimeout(() => {
        closeEditModal();
      }, 2000);
      
    } else {
      mostrarAlerta(result.error || 'Error al actualizar', 'error');
    }
    
  } catch (error) {
    console.error('Error:', error);
    mostrarAlerta('Error de conexión al servidor: ' + error.message, 'error');
    mostrarNotificacion('Error de conexión al servidor: ' + error.message, 'error');
  } finally {
    // Ocultar loading
    loadingEl.style.display = 'none';
    submitBtn.disabled = false;
  }
}

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
  // Cargar estadísticas iniciales
  cargarEstadisticas();
  
  // Animación de entrada para las tarjetas de estadísticas
  animateStatsCards();
  
  // Crear gráficas con diferentes colores
  setTimeout(() => {
    crearGrafica('graficaComponente1', 'Componente Docencia', 45, colors.primary);
    crearGrafica('graficaComponente2', 'Componente Investigación', 62, colors.secondary);
    crearGrafica('graficaComponente3', 'Componente Extensión', 30, colors.tertiary);
    crearGrafica('graficaComponente4', 'Componente Administrativo', 75, colors.quaternary);
  }, 500);
});

// Manejar envío del formulario de edición
document.getElementById('editForm').addEventListener('submit', function(e) {
  e.preventDefault();
  
  const cantidad = parseInt(document.getElementById('cantidadInput').value);
  
  if (isNaN(cantidad) || cantidad < 0) {
    mostrarAlerta('La cantidad debe ser un número válido y no puede ser negativa', 'error');
    return;
  }
  
  actualizarEstadistica(currentEditType, cantidad);
});

// Cerrar modal al hacer clic fuera de él
window.addEventListener('click', function(event) {
  const modal = document.getElementById('editModal');
  if (event.target === modal) {
    closeEditModal();
  }
});

// Cerrar modal con tecla Escape
document.addEventListener('keydown', function(event) {
  if (event.key === 'Escape') {
    closeEditModal();
  }
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
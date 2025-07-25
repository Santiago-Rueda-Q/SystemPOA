// Manejar el formulario de edición - JavaScript mejorado
document.getElementById('editForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const loading = document.getElementById('loading');
    const submitBtn = e.target.querySelector('button[type="submit"]');
    
    loading.style.display = 'block';
    submitBtn.disabled = true;
    hideAlert();
    
    const formData = new FormData(e.target);
    
    try {
        const response = await fetch('update_inscrito.php', {
            method: 'POST',
            body: formData
        });
        
        // Verificar si la respuesta es exitosa
        if (!response.ok) {
            throw new Error(`Error HTTP: ${response.status}`);
        }
        
        const result = await response.json();
        
        if (result.success) {
            showAlert('Cantidad actualizada correctamente', 'success');
            
            // Actualizar la interfaz con los nuevos totales
            if (result.data && result.data.totales) {
                const totales = result.data.totales;
                
                // Actualizar los contadores con animación
                document.getElementById('total-count').textContent = totales.total.toLocaleString();
                document.getElementById('tecnico-count').textContent = totales.tecnico.toLocaleString();
                document.getElementById('tecnologia-count').textContent = totales.tecnologia.toLocaleString();
                document.getElementById('profesional-count').textContent = totales.profesional.toLocaleString();
                
                // Actualizar los datos globales si existen
                if (typeof datosInscritos !== 'undefined') {
                    // Actualizar el array datosInscritos
                    const index = datosInscritos.findIndex(d => d.id == result.data.id);
                    if (index !== -1) {
                        datosInscritos[index].cantidad = result.data.cantidad;
                    }
                }
            }
            
            // Cerrar modal después de 2 segundos
            setTimeout(() => {
                closeModal();
            }, 2000);
            
        } else {
            showAlert(result.message || 'Error al actualizar', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showAlert('Error de conexión: ' + error.message, 'error');
    } finally {
        loading.style.display = 'none';
        submitBtn.disabled = false;
    }
});

// Función para mostrar alertas mejorada
function showAlert(message, type) {
    const alertContainer = document.getElementById('alertContainer');
    const iconClass = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
    const bgColor = type === 'success' ? 'bg-green-50 border-green-200 text-green-800' : 'bg-red-50 border-red-200 text-red-800';
    
    alertContainer.innerHTML = `
        <div class="alert ${type === 'success' ? 'alert-success' : 'alert-error'} ${bgColor} border rounded-lg p-4 mb-4" style="display: block;">
            <div class="flex items-center">
                <i class="fas ${iconClass} mr-2"></i>
                <span>${message}</span>
            </div>
        </div>
    `;
    
    // Auto-hide después de 5 segundos
    setTimeout(() => {
        hideAlert();
    }, 5000);
}

// Función para ocultar alertas
function hideAlert() {
    const alertContainer = document.getElementById('alertContainer');
    alertContainer.innerHTML = '';
}

// Funciones existentes actualizadas
function openViewModal(tipo, titulo) {
    document.getElementById('modalTitle').textContent = titulo;
    document.getElementById('tableView').style.display = 'block';
    document.getElementById('editView').style.display = 'none';
    
    // Llenar la tabla
    const tableBody = document.getElementById('tableBody');
    tableBody.innerHTML = '';
    
    if (typeof datosInscritos !== 'undefined') {
        datosInscritos.forEach(dato => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td class="capitalize font-medium text-gray-900">${dato.nivel_formacion}</td>
                <td class="font-semibold text-blue-600">${parseInt(dato.cantidad).toLocaleString()}</td>
                <td>
                    <button onclick="editSingle(${dato.id}, '${dato.nivel_formacion}', ${dato.cantidad})" 
                            class="inline-flex items-center px-3 py-1 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors text-sm">
                        <i class="fas fa-edit mr-1"></i> Editar
                    </button>
                </td>
            `;
            tableBody.appendChild(row);
        });
        
        // Actualizar total general
        const totalGeneral = datosInscritos.reduce((sum, dato) => sum + parseInt(dato.cantidad), 0);
        document.getElementById('totalGeneral').textContent = totalGeneral.toLocaleString();
    }
    
    document.getElementById('editModal').style.display = 'block';
}

function openEditModal(tipo, titulo) {
    if (typeof datosInscritos !== 'undefined') {
        const dato = datosInscritos.find(d => d.nivel_formacion.toLowerCase() === tipo);
        if (dato) {
            editSingle(dato.id, dato.nivel_formacion, dato.cantidad);
        }
    }
}

function editSingle(id, nivel, cantidad) {
    document.getElementById('modalTitle').textContent = `Editar ${nivel}`;
    document.getElementById('tableView').style.display = 'none';
    document.getElementById('editView').style.display = 'block';
    
    document.getElementById('editId').value = id;
    document.getElementById('editTipo').value = nivel;
    document.getElementById('cantidadInput').value = cantidad;
    
    document.getElementById('editModal').style.display = 'block';
    
    // Limpiar alertas previas
    hideAlert();
}

function cancelEdit() {
    openViewModal('total', 'Total Inscritos');
}

function closeModal() {
    document.getElementById('editModal').style.display = 'none';
    hideAlert();
}

// Cerrar modal al hacer clic fuera
window.onclick = function(event) {
    const modal = document.getElementById('editModal');
    if (event.target === modal) {
        closeModal();
    }
};

// Cerrar modal con tecla Escape
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeModal();
    }
});

// Sistema de notificaciones toast
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    const bgColor = type === 'success' ? 'bg-green-500' : type === 'error' ? 'bg-red-500' : 'bg-blue-500';
    
    toast.className = `fixed top-4 right-4 ${bgColor} text-white px-6 py-3 rounded-lg shadow-lg z-50 transform translate-x-full transition-transform duration-300`;
    toast.innerHTML = `
        <div class="flex items-center">
            <i class="fas fa-${type === 'success' ? 'check' : type === 'error' ? 'exclamation-triangle' : 'info'} mr-2"></i>
            <span>${message}</span>
        </div>
    `;
    
    document.body.appendChild(toast);
    
    // Mostrar toast
    setTimeout(() => {
        toast.classList.remove('translate-x-full');
    }, 100);
    
    // Ocultar toast después de 4 segundos
    setTimeout(() => {
        toast.classList.add('translate-x-full');
        setTimeout(() => {
            if (document.body.contains(toast)) {
                document.body.removeChild(toast);
            }
        }, 300);
    }, 4000);
}

// Logout functionality mejorado
document.getElementById('logoutBtn').addEventListener('click', function() {
    // Crear modal de confirmación personalizado
    const confirmModal = document.createElement('div');
    confirmModal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
    confirmModal.innerHTML = `
        <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4 shadow-xl">
            <div class="flex items-center mb-4">
                <i class="fas fa-sign-out-alt text-red-500 text-2xl mr-3"></i>
                <h3 class="text-lg font-semibold text-gray-900">Cerrar Sesión</h3>
            </div>
            <p class="text-gray-600 mb-6">¿Estás seguro de que deseas cerrar sesión?</p>
            <div class="flex gap-3">
                <button onclick="confirmLogout()" class="flex-1 bg-red-600 text-white py-2 px-4 rounded-lg hover:bg-red-700 transition-colors">
                    <i class="fas fa-check mr-2"></i>Sí, cerrar sesión
                </button>
                <button onclick="this.closest('.fixed').remove()" class="flex-1 bg-gray-300 text-gray-700 py-2 px-4 rounded-lg hover:bg-gray-400 transition-colors">
                    <i class="fas fa-times mr-2"></i>Cancelar
                </button>
            </div>
        </div>
    `;
    
    document.body.appendChild(confirmModal);
});

function confirmLogout() {
    // Crear efecto de fade out
    document.body.style.transition = 'opacity 0.5s ease';
    document.body.style.opacity = '0.5';
    
    // Mostrar loading
    showToast('Cerrando sesión...', 'info');
    
    setTimeout(() => {
        window.location.href = 'logout.php';
    }, 1000);
}
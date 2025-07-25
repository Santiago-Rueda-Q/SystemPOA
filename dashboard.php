<?php
session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_nombre'])) {
    header("Location: index.php");
    exit;
}

// Obtener datos del usuario de la sesión
$nombre_usuario = $_SESSION['usuario_nombre'];
$rol_usuario = $_SESSION['usuario_rol'];
$email_usuario = $_SESSION['usuario_email'];

// Conexión a la base de datos
require_once 'conexion.php'; 

// Función para obtener los datos de inscritos
function obtenerDatosInscritos($conn) {
    try {
        $sql = "SELECT id, nivel_formacion, cantidad FROM public.total_inscritos ORDER BY nivel_formacion";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error al obtener datos de inscritos: " . $e->getMessage());
        return [];
    }
}

// Obtener los datos
$datosInscritos = obtenerDatosInscritos($conn);

// Calcular totales y organizar por nivel
$totales = [
    'total' => 0,
    'tecnico' => 0,
    'tecnologia' => 0,
    'profesional' => 0
];

foreach ($datosInscritos as $dato) {
    $cantidad = (int)$dato['cantidad'];
    $totales['total'] += $cantidad;
    
    $nivel = trim($dato['nivel_formacion']); // Sin strtolower para mantener formato original
    
    // Mapear basado en el nombre exacto de la base de datos
    switch ($nivel) {
        case 'Técnico':
            $totales['tecnico'] = $cantidad;
            break;
        case 'Tecnología':
            $totales['tecnologia'] = $cantidad;
            break;
        case 'Profesional':
            $totales['profesional'] = $cantidad;
            break;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - SystemPOA</title>
    <link rel="stylesheet" href="dashboard.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" type="image/png" href="resource/fesc.png">
    <style>
        .edit-btn {
            background: var(--azul1);
            color: white;
            border: none;
            border-radius: 50%;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-left: auto;
        }
        
        .edit-btn:hover {
            background: var(--azul2);
            transform: scale(1.1);
        }
        
        .stat-card {
            position: relative;
        }
        
        .stat-actions {
            position: absolute;
            top: 16px;
            right: 16px;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .stat-card:hover .stat-actions {
            opacity: 1;
        }
        
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        
        .modal-content {
            background-color: white;
            margin: 10% auto;
            padding: 32px;
            border-radius: 16px;
            width: 90%;
            max-width: 800px;
            box-shadow: var(--shadow-lg);
            max-height: 80vh;
            overflow-y: auto;
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }
        
        .modal-header h2 {
            color: var(--azul4);
            margin: 0;
        }
        
        .close {
            color: var(--gris-oscuro);
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            border: none;
            background: none;
        }
        
        .close:hover {
            color: var(--rojo);
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--negro);
        }
        
        .form-group input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid var(--gris-medio);
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: var(--azul1);
        }
        
        .btn-primary {
            background: var(--azul1);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
        }
        
        .btn-primary:hover {
            background: var(--azul2);
            transform: translateY(-2px);
        }
        
        .loading {
            display: none;
            text-align: center;
            color: var(--azul1);
            margin-top: 16px;
        }
        
        .alert {
            padding: 12px 16px;
            margin-bottom: 20px;
            border-radius: 8px;
            display: none;
        }
        
        .alert-success {
            background-color: rgba(16, 185, 129, 0.1);
            color: var(--verde);
            border: 1px solid rgba(16, 185, 129, 0.3);
        }
        
        .alert-error {
            background-color: rgba(239, 68, 68, 0.1);
            color: var(--rojo);
            border: 1px solid rgba(239, 68, 68, 0.3);
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        .data-table th,
        .data-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .data-table th {
            background-color: #f9fafb;
            font-weight: 600;
            color: #374151;
        }
        
        .data-table tr:hover {
            background-color: #f9fafb;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="logo">
                <i class="fas fa-graduation-cap"></i>
                SystemPOA
            </div>
            
            <nav class="menu">
                <ul>
                    <li class="menu-item active">
                        <a href="dashboard.php">
                            <i class="fas fa-home"></i>
                            <span>Inicio</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="llenar-poa.php">
                            <i class="fas fa-edit"></i>
                            <span>Llenar POA</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="ver-poa.php">
                            <i class="fas fa-eye"></i>
                            <span>Ver POA</span>
                        </a>
                    </li>
                    <?php if ($rol_usuario === 'Director'): ?>
                    <li class="menu-item">
                        <a href="asignar-tareas.php">
                            <i class="fas fa-tasks"></i>
                            <span>Asignar Tareas</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="reportes.php">
                            <i class="fas fa-chart-bar"></i>
                            <span>Reportes</span>
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
            </nav>

            <div class="user-section">
                <div class="user-info">
                    <div class="user-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="user-details">
                        <h4><?php echo htmlspecialchars($nombre_usuario); ?></h4>
                        <span class="user-role"><?php echo htmlspecialchars($rol_usuario); ?></span>
                    </div>
                </div>
                <button id="logoutBtn" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i>
                    Cerrar sesión
                </button>
            </div>
        </aside>

        <main class="main-content">
            <header class="main-header">
                <h1>Bienvenido, <?php echo htmlspecialchars($nombre_usuario); ?>!</h1>
                <p class="subtitle">Panel de control - <?php echo htmlspecialchars($rol_usuario); ?></p>
            </header>

            <section class="stats-container">
                <div class="stat-card primary">
                    <?php if ($rol_usuario === 'Director'): ?>
                    <div class="stat-actions">
                        <button class="edit-btn" onclick="openViewModal('total', 'Total Inscritos')" title="Ver desglose">
                            <i class="w-4 h-4 fas fa-eye"></i>
                        </button>
                    </div>
                    <?php endif; ?>
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Total Inscritos</h3>
                        <p class="stat-number" id="total-count"><?php echo $totales['total']; ?></p>
                    </div>
                </div>

                <div class="stat-card secondary">
                    <?php if ($rol_usuario === 'Director'): ?>
                    <div class="stat-actions">
                        <button class="edit-btn" onclick="openEditModal('tecnico', 'Técnico')" title="Editar cantidad">
                            <i class="w-4 h-4 fas fa-edit"></i>
                        </button>
                    </div>
                    <?php endif; ?>
                    <div class="stat-icon">
                        <i class="fas fa-tools"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Técnico</h3>
                        <p class="stat-number" id="tecnico-count"><?php echo $totales['tecnico']; ?></p>
                    </div>
                </div>

                <div class="stat-card tertiary">
                    <?php if ($rol_usuario === 'Director'): ?>
                    <div class="stat-actions">
                        <button class="edit-btn" onclick="openEditModal('tecnologia', 'Tecnología')" title="Editar cantidad">
                            <i class="w-4 h-4 fas fa-edit"></i>
                        </button>
                    </div>
                    <?php endif; ?>
                    <div class="stat-icon">
                        <i class="fas fa-laptop-code"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Tecnología</h3>
                        <p class="stat-number" id="tecnologia-count"><?php echo $totales['tecnologia']; ?></p>
                    </div>
                </div>

                <div class="stat-card quaternary">
                    <?php if ($rol_usuario === 'Director'): ?>
                    <div class="stat-actions">
                        <button class="edit-btn" onclick="openEditModal('profesional', 'Profesional')" title="Editar cantidad">
                            <i class="w-4 h-4 fas fa-edit"></i>
                        </button>
                    </div>
                    <?php endif; ?>
                    <div class="stat-icon">
                        <i class="fas fa-university"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Profesional</h3>
                        <p class="stat-number" id="profesional-count"><?php echo $totales['profesional']; ?></p>
                    </div>
                </div>
            </section>

            <section class="charts-section">
                <h2>Progreso por Componentes</h2>
                <div class="charts-container">
                    <div class="chart-card">
                        <h3>Componente Docencia</h3>
                        <canvas id="graficaComponente1"></canvas>
                        <div class="progress-info">45% completado</div>
                    </div>
                    <div class="chart-card">
                        <h3>Componente Investigación</h3>
                        <canvas id="graficaComponente2"></canvas>
                        <div class="progress-info">62% completado</div>
                    </div>
                    <div class="chart-card">
                        <h3>Componente Extensión</h3>
                        <canvas id="graficaComponente3"></canvas>
                        <div class="progress-info">30% completado</div>
                    </div>
                    <div class="chart-card">
                        <h3>Componente Administrativo</h3>
                        <canvas id="graficaComponente4"></canvas>
                        <div class="progress-info">75% completado</div>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <!-- Modal para ver/editar estadísticas -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">Editar Estadística</h2>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>
            
            <div id="alertContainer"></div>
            
            <!-- Contenido para vista de tabla -->
            <div id="tableView" style="display: none;">
                <div class="mb-4 p-4 bg-blue-50 rounded-lg border border-blue-200">
                    <div class="flex items-center">
                        <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                        <span class="text-blue-800 font-medium">Desglose detallado por nivel de formación</span>
                    </div>
                </div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th class="flex items-center">
                                <i class="fas fa-graduation-cap mr-2 text-gray-600"></i>
                                Nivel de Formación
                            </th>
                            <th class="flex items-center">
                                <i class="fas fa-users mr-2 text-gray-600"></i>
                                Cantidad de Estudiantes
                            </th>
                            <th class="flex items-center">
                                <i class="fas fa-cogs mr-2 text-gray-600"></i>
                                Acciones
                            </th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        <!-- Se llenará dinámicamente -->
                    </tbody>
                </table>
                <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                    <div class="flex items-center justify-between">
                        <span class="font-semibold text-gray-700 flex items-center">
                            <i class="fas fa-calculator mr-2"></i>
                            Total General:
                        </span>
                        <span class="text-2xl font-bold text-blue-600" id="totalGeneral"><?php echo $totales['total']; ?></span>
                    </div>
                </div>
            </div>
            
            <!-- Contenido para edición individual -->
            <div id="editView" style="display: none;">
                <form id="editForm">
                    <input type="hidden" id="editId" name="id">
                    <input type="hidden" id="editTipo" name="tipo">
                    
                    <div class="form-group">
                        <label for="cantidadInput" class="flex items-center">
                            <i class="fas fa-hashtag mr-2 text-gray-600"></i>
                            Cantidad de estudiantes:
                        </label>
                        <input type="number" id="cantidadInput" name="cantidad" min="0" required 
                               class="focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    
                    <div class="flex gap-3">
                        <button type="submit" class="btn-primary flex items-center justify-center">
                            <i class="fas fa-save mr-2"></i> Actualizar
                        </button>
                        <button type="button" onclick="closeModal()" 
                            class="px-6 py-3 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors flex items-center">
                            <i class="fas fa-times mr-2"></i> Cancelar
                        </button>
                    </div>
                    
                    <div class="loading" id="loading">
                        <i class="fas fa-spinner fa-spin mr-2"></i> Actualizando...
                    </div>
                </form>
            </div>
        </div>
    </div>

<script>
    // Datos de inscritos desde PHP
    const datosInscritos = <?php echo json_encode($datosInscritos); ?>;

    // Función para debugging
    console.log('Datos cargados:', datosInscritos);
    console.log('Rol usuario:', '<?php echo $rol_usuario; ?>');

    function openViewModal(tipo, titulo) {
        console.log('openViewModal llamado:', tipo, titulo);
        
        document.getElementById('modalTitle').textContent = titulo;
        document.getElementById('tableView').style.display = 'block';
        document.getElementById('editView').style.display = 'none';
        
        // Llenar la tabla
        const tableBody = document.getElementById('tableBody');
        tableBody.innerHTML = '';
        
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
        
        // Calcular y mostrar total
        const totalGeneral = datosInscritos.reduce((sum, dato) => sum + parseInt(dato.cantidad), 0);
        document.getElementById('totalGeneral').textContent = totalGeneral.toLocaleString();
        
        document.getElementById('editModal').style.display = 'block';
    }

    function openEditModal(tipo, titulo) {
        console.log('openEditModal llamado:', tipo, titulo);
        
        // Mapear el tipo de la interfaz al nombre exacto en la base de datos
        const tipoMapping = {
            'tecnico': 'Técnico',
            'tecnologia': 'Tecnología',
            'profesional': 'Profesional'
        };
        
        const nombreBD = tipoMapping[tipo];
        const dato = datosInscritos.find(d => d.nivel_formacion === nombreBD);
        
        if (dato) {
            editSingle(dato.id, dato.nivel_formacion, dato.cantidad);
        } else {
            console.error('No se encontró dato para tipo:', tipo);
            console.log('Nombre BD buscado:', nombreBD);
            console.log('Datos disponibles:', datosInscritos.map(d => d.nivel_formacion));
        }
    }

    function editSingle(id, nivel, cantidad) {
        console.log('editSingle llamado:', id, nivel, cantidad);
        
        document.getElementById('modalTitle').textContent = `Editar ${nivel}`;
        document.getElementById('tableView').style.display = 'none';
        document.getElementById('editView').style.display = 'block';
        
        document.getElementById('editId').value = id;
        document.getElementById('editTipo').value = nivel;
        document.getElementById('cantidadInput').value = cantidad;
        
        document.getElementById('editModal').style.display = 'block';
        
        // Limpiar alertas previas
        hideAlert();
        
        // Focus en el input
        setTimeout(() => {
            document.getElementById('cantidadInput').focus();
            document.getElementById('cantidadInput').select();
        }, 100);
    }

    function closeModal() {
        console.log('closeModal llamado');
        document.getElementById('editModal').style.display = 'none';
        hideAlert();
    }

    function showAlert(message, type) {
        console.log('showAlert:', message, type);
        const alertContainer = document.getElementById('alertContainer');
        const iconClass = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
        
        alertContainer.innerHTML = `
            <div class="alert alert-${type}" style="display: block;">
                <i class="fas ${iconClass} mr-2"></i>
                ${message}
            </div>
        `;
        
        // Auto-hide después de 5 segundos solo para mensajes de éxito
        if (type === 'success') {
            setTimeout(() => {
                hideAlert();
            }, 5000);
        }
    }

    function hideAlert() {
        const alertContainer = document.getElementById('alertContainer');
        alertContainer.innerHTML = '';
    }

    // Función para actualizar la interfaz después de una actualización exitosa
    function actualizarInterfaz(nuevaData) {
        console.log('Actualizando interfaz con:', nuevaData);
        
        if (nuevaData && nuevaData.totales) {
            const totales = nuevaData.totales;
            
            // Actualizar contadores en las tarjetas
            document.getElementById('total-count').textContent = totales.total.toLocaleString();
            document.getElementById('tecnico-count').textContent = totales.tecnico.toLocaleString();
            document.getElementById('tecnologia-count').textContent = totales.tecnologia.toLocaleString();
            document.getElementById('profesional-count').textContent = totales.profesional.toLocaleString();
            
            // Actualizar datos locales
            const index = datosInscritos.findIndex(d => d.id == nuevaData.id);
            if (index !== -1) {
                datosInscritos[index].cantidad = nuevaData.cantidad;
                console.log('Datos actualizados localmente:', datosInscritos[index]);
            }
            
            // Si estamos en la vista de tabla, actualizarla también
            const tableView = document.getElementById('tableView');
            if (tableView.style.display !== 'none') {
                // Actualizar total general en la tabla
                document.getElementById('totalGeneral').textContent = totales.total.toLocaleString();
                
                // Actualizar la fila específica en la tabla
                const tableBody = document.getElementById('tableBody');
                const rows = tableBody.querySelectorAll('tr');
                rows.forEach(row => {
                    const button = row.querySelector('button');
                    if (button && button.onclick.toString().includes(`editSingle(${nuevaData.id},`)) {
                        const cantidadCell = row.children[1];
                        cantidadCell.textContent = parseInt(nuevaData.cantidad).toLocaleString();
                        
                        // Actualizar el onclick del botón
                        button.setAttribute('onclick', `editSingle(${nuevaData.id}, '${nuevaData.tipo}', ${nuevaData.cantidad})`);
                    }
                });
            }
        }
    }

    // Manejar el formulario de edición
    document.getElementById('editForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        console.log('Formulario enviado');
        
        const loading = document.getElementById('loading');
        const submitBtn = e.target.querySelector('button[type="submit"]');
        
        loading.style.display = 'block';
        submitBtn.disabled = true;
        hideAlert();
        
        const formData = new FormData(e.target);
        
        // Debug: mostrar datos que se van a enviar
        console.log('Datos a enviar:');
        for (let [key, value] of formData.entries()) {
            console.log(key, value);
        }
        
        try {
            const response = await fetch('update_inscrito.php', {
                method: 'POST',
                body: formData
            });
            
            console.log('Respuesta del servidor:', response.status, response.statusText);
            
            // Verificar si la respuesta es exitosa
            if (!response.ok) {
                throw new Error(`Error HTTP: ${response.status} - ${response.statusText}`);
            }
            
            // Intentar parsear como JSON
            const responseText = await response.text();
            console.log('Texto de respuesta:', responseText);
            
            let result;
            try {
                result = JSON.parse(responseText);
            } catch (parseError) {
                console.error('Error parsing JSON:', parseError);
                throw new Error('La respuesta del servidor no es JSON válido: ' + responseText.substring(0, 100));
            }
            
            console.log('Resultado parseado:', result);
            
            if (result.success) {
                showAlert('Cantidad actualizada correctamente', 'success');
                
                // Actualizar la interfaz con los nuevos datos
                actualizarInterfaz(result.data);
                
                // Cerrar modal después de 2 segundos
                setTimeout(() => {
                    closeModal();
                }, 2000);
                
            } else {
                showAlert(result.message || 'Error al actualizar', 'error');
            }
            
        } catch (error) {
            console.error('Error completo:', error);
            showAlert('Error de conexión: ' + error.message, 'error');
        } finally {
            loading.style.display = 'none';
            submitBtn.disabled = false;
        }
    });

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

    // Logout functionality
    document.getElementById('logoutBtn').addEventListener('click', function() {
        if (confirm('¿Estás seguro de que deseas cerrar sesión?')) {
            window.location.href = 'logout.php';
        }
    });

    // Verificar que las funciones están disponibles cuando se carga la página
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM cargado');
        console.log('Funciones disponibles:', {
            openViewModal: typeof openViewModal,
            openEditModal: typeof openEditModal,
            editSingle: typeof editSingle,
            closeModal: typeof closeModal
        });
        
        // Verificar que los elementos existen
        const elementos = ['editModal', 'modalTitle', 'tableView', 'editView', 'editForm', 'alertContainer'];
        elementos.forEach(id => {
            const elemento = document.getElementById(id);
            console.log(`Elemento ${id}:`, elemento ? 'Existe' : 'NO EXISTE');
        });
    });
    </script>
</body>
</html>
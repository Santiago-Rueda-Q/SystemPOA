<?php
// Incluir la conexión a la base de datos
require_once 'conexion.php';

// Procesar actualización cuando se envía
if ($_POST && isset($_POST['accion']) && $_POST['accion'] === 'actualizar') {
    try {
        $categoria = $_POST['categoria'];
        $id = $_POST['id'];
        $campos = [];
        
        // Procesar cada campo del formulario
        foreach ($_POST as $key => $value) {
            if ($key !== 'categoria' && $key !== 'id' && $key !== 'accion' && !empty($value)) {
                // Convertir campos boolean
                $booleanFields = ['realizado', 'terminado', 'realizada', 'concretada', 'aprobado', 'terminados'];
                
                if (in_array($key, $booleanFields)) {
                    if (strtoupper($value) === 'SI' || strtoupper($value) === 'SÍ') {
                        $campos[] = "$key = true";
                    } else {
                        $campos[] = "$key = false";
                    }
                } else {
                    $campos[] = "$key = ?";
                    $valores[] = $value;
                }
            }
        }
        
        // Construir la consulta SQL de actualización
        if (!empty($campos)) {
            $sql = "UPDATE $categoria SET " . implode(', ', $campos) . " WHERE id = ?";
            $valores[] = $id;
            
            $stmt = $conn->prepare($sql);
            $stmt->execute($valores);
            
            $mensaje = "Registro actualizado exitosamente";
            $tipo_mensaje = "success";
        }
    } catch (PDOException $e) {
        $mensaje = "Error al actualizar el registro: " . $e->getMessage();
        $tipo_mensaje = "error";
    }
}

// Procesar eliminación
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'eliminar') {
    $categoria = $_POST['categoria'] ?? '';
    $id = $_POST['id'] ?? '';
    
    if (!empty($categoria) && !empty($id)) {
        try {
            $stmt = $conn->prepare("DELETE FROM {$categoria} WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            // Respuesta para AJAX
            http_response_code(200);
            echo "OK";
        } catch (PDOException $e) {
            http_response_code(500);
            echo "Error: " . $e->getMessage();
        }
    } else {
        http_response_code(400);
        echo "Parámetros incompletos";
    }
    exit;
}

// Definir los campos por tipo de actividad (igual que en llenar-poa.php)
$camposPorTipo = [
    "ppa" => [
        "nivel_formacion" => "text",
        "ppa_realizado" => "text", 
        "terminado" => "select",
        "evidencia_link" => "url",
        "semester" => "number"
    ],
    "visita_regional" => [
        "visitas_regionales" => "text",
        "estudiantes" => "number",
        "realizado" => "select",
        "evidencia_link" => "url",
        "semester" => "number"
    ],
    "visita_nacional" => [
        "visita_nacional" => "text",
        "estudiantes" => "number",
        "realizada" => "select",
        "evidencia_link" => "url",
        "semester" => "number"
    ],
    "charlas" => [
        "nombre_evento" => "text",
        "estudiantes" => "number",
        "realizada" => "select",
        "evidencia_link" => "url",
        "semester" => "number"
    ],
    "feria" => [
        "nombre_evento" => "text",
        "estudiantes" => "number",
        "realizada" => "select",
        "evidencia_link" => "url",
        "semester" => "number"
    ],
    "congreso" => [
        "nombre_evento" => "text",
        "estudiantes" => "number",
        "realizado" => "select",
        "evidencia_link" => "url",
        "semester" => "number"
    ],
    "evento_nacional" => [
        "nombre_evento" => "text",
        "estudiantes" => "number",
        "realizado" => "select",
        "evidencia_link" => "url",
        "semester" => "number"
    ],
    "biblio" => [
        "asignaturas_programadas" => "text",
        "bibliografia_ingles" => "text",
        "evidencia_link" => "url",
        "semester" => "number"
    ],
    "profesor_visitante" => [
        "profesor" => "text",
        "tema" => "text",
        "estudiantes" => "number",
        "realizada" => "select",
        "evidencia_link" => "url",
        "semester" => "number"
    ],
    "actividades_interculturales" => [
        "actividad" => "text",
        "estudiantes" => "number",
        "realizada" => "select",
        "evidencia_link" => "url",
        "semester" => "number"
    ],
    "actividades_universidades_internacionales" => [
        "actividad" => "text",
        "actividad_conjunta" => "text",
        "estudiantes" => "number",
        "realizada" => "select",
        "evidencia_link" => "url",
        "semester" => "number"
    ],
    "productos_en_ingles" => [
        "producto" => "text",
        "estudiantes" => "number",
        "terminados" => "select",
        "evidencia_link" => "url",
        "semester" => "number"
    ],
    "ppa_traducidos" => [
        "documento" => "text",
        "estudiantes" => "number",
        "asignatura" => "text",
        "terminados" => "select",
        "evidencia_link" => "url",
        "semester" => "number"
    ],
    "coil" => [
        "universidad" => "text",
        "asignatura" => "text",
        "tema" => "text",
        "realizada" => "select",
        "evidencia_link" => "url",
        "semester" => "number"
    ],
    "clase_espejo" => [
        "universidad" => "text",
        "asignatura" => "text",
        "tema" => "text",
        "realizada" => "select",
        "evidencia_link" => "url",
        "semester" => "number"
    ],
    "reto_empresa" => [
        "empresa" => "text",
        "reto" => "text",
        "estudiantes" => "number",
        "realizado" => "select",
        "evidencia_link" => "url",
        "semester" => "number"
    ],
    "grupo_focal" => [
        "experto" => "text",
        "fecha_comite" => "date",
        "evidencia_link" => "url",
        "semester" => "number"
    ],
    "estudio_tendencias" => [
        "informe" => "text",
        "fecha_elaboracion" => "date",
        "acta_comite" => "text",
        "evidencia_link" => "url",
        "semester" => "number"
    ],
    "analisis_contexto" => [
        "informe" => "text",
        "fecha_elaboracion" => "date",
        "acta_comite" => "text",
        "evidencia_link" => "url",
        "semester" => "number"
    ],
    "autoevaluacion_mejoras" => [
        "informe" => "text",
        "fecha_elaboracion" => "date",
        "acta_comite" => "text",
        "evidencia_link" => "url",
        "semester" => "number"
    ],
    "empresas_practicas" => [
        "empresa" => "text",
        "concretada" => "select",
        "evidencia_link" => "url",
        "semester" => "number"
    ],
    "graduados" => [
        "actividades_programadas" => "number",
        "actividades_realizadas" => "number",
        "evidencia_link" => "url",
        "semester" => "number"
    ],
    "mesa_sector" => [
        "participacion" => "text",
        "fecha_encuentro" => "date",
        "tema" => "text",
        "evidencia_link" => "url",
        "semester" => "number"
    ],
    "mejoras_practicas" => [
        "accion" => "text",
        "fecha_implementacion" => "date",
        "evidencia_link" => "url",
        "semester" => "number"
    ],
    "diplomado_grado" => [
        "nivel_formacion" => "text",
        "tema" => "text",
        "estudiantes" => "number",
        "evidencia_link" => "url",
        "semester" => "number"
    ],
    "formacion_continua" => [
        "curso" => "text",
        "estudiantes" => "number",
        "aprobado" => "select",
        "evidencia_link" => "url",
        "semester" => "number"
    ],
    "mbc_refuerzos" => [
        "competencia" => "text",
        "asignatura" => "text",
        "docente" => "text",
        "actividades_verificadas" => "number",
        "evidencia_link" => "url",
        "semester" => "number"
    ],
    "taller_refuerzo_saber" => [
        "taller" => "text",
        "estudiantes" => "number",
        "fecha" => "date",
        "evidencia_link" => "url",
        "semester" => "number"
    ],
    "proyecto_grado" => [
        "propuesta" => "text",
        "evidencia_link" => "url",
        "semester" => "number"
    ],
    "visita_aula" => [
        "docente" => "text",
        "fecha" => "date",
        "hora" => "time",
        "asignatura" => "text",
        "aula" => "text",
        "calificacion" => "number",
        "evidencia_link" => "url",
        "semester" => "number"
    ],
    "estudio_semilleros_estudiantes" => [
        "estudiante" => "text",
        "fecha_ingreso" => "date",
        "evidencia_link" => "url",
        "semester" => "number"
    ],
    "estudio_semilleros_docentes" => [
        "docente" => "text",
        "fecha_ingreso" => "date",
        "evidencia_link" => "url",
        "semester" => "number"
    ],
    "pep" => [
        "proyecto" => "text",
        "fecha_actualizacion" => "date",
        "fecha_aprobacion" => "date",
        "evidencia_link" => "url",
        "semester" => "number"
    ],
    "herramientas" => [
        "herramienta" => "text",
        "asignatura" => "text",
        "fecha_incorporacion" => "date",
        "evidencia_link" => "url",
        "semester" => "number"
    ],
    "micrositio" => [
        "actualizacion" => "text",
        "fecha_actualizacion" => "date",
        "evidencia_link" => "url",
        "semester" => "number"
    ],
    "modalidad_virtual" => [
        "modulo" => "text",
        "cantidad" => "number",
        "evidencia_link" => "url",
        "semester" => "number"
    ],
    "sitio_interaccion" => [
        "espacio" => "text",
        "cantidad" => "number",
        "evidencia_link" => "url",
        "semester" => "number"
    ],
    "atlas" => [
        "registro" => "text",
        "cantidad" => "number",
        "evidencia_link" => "url",
        "semester" => "number"
    ],
    "seguimiento_estudiantes" => [
        "seguimiento" => "text",
        "cantidad" => "number",
        "evidencia_link" => "url",
        "semester" => "number"
    ],
    "matricula_estudiantes_antiguos" => [
        "matricula" => "text",
        "cantidad" => "number",
        "evidencia_link" => "url",
        "semester" => "number"
    ]
];

// Función para obtener registros de una tabla
function obtenerRegistros($conn, $tabla) {
    try {
        $sql = "SELECT * FROM $tabla ORDER BY id DESC";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SystemPOA - Ver POA</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #07396b 0%, #195da2 100%);
        }
        .card-shadow {
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        .hover-scale {
            transition: transform 0.2s ease-in-out;
        }
        .hover-scale:hover {
            transform: scale(1.02);
        }
        .category-item {
            background: linear-gradient(135deg, #195da2 0%, #3b8cde 100%);
            transition: all 0.3s ease;
        }
        .category-item:hover {
            background: linear-gradient(135deg, #3b8cde 0%, #5ba3e8 100%);
            transform: translateX(5px);
        }
        .category-item.active {
            background: linear-gradient(135deg, #059669 0%, #10b981 100%);
        }
        .form-container {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        }
        .input-focus:focus {
            border-color: #3b8cde;
            box-shadow: 0 0 0 3px rgba(59, 140, 222, 0.1);
        }
        .table-hover:hover {
            background-color: #f8fafc;
        }
    </style>
</head>
<body class="bg-gray-100">
    <!-- Header -->
    <div class="gradient-bg text-white p-4 shadow-lg">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <i class="fas fa-graduation-cap text-2xl"></i>
                <h1 class="text-2xl font-bold">SystemPOA</h1>
            </div>
            <div class="flex items-center space-x-4">
                <span class="text-sm opacity-90">¡Bienvenido, Santiago R!</span>
                <a href="dashboard.php" class="bg-white bg-opacity-20 px-4 py-2 rounded-lg hover:bg-opacity-30 transition">
                    <i class="fas fa-arrow-left mr-2"></i>Dashboard
                </a>
            </div>
        </div>
    </div>

    <div class="flex h-screen">
        <!-- Sidebar -->
        <aside class="w-80 gradient-bg text-white shadow-xl overflow-y-auto">
            <div class="p-6">
                <h2 class="text-xl font-bold mb-6 flex items-center">
                    <i class="fas fa-table mr-3"></i>
                    Ver Registros POA
                </h2>
                
                <?php if (isset($mensaje)): ?>
                <div class="mb-4 p-3 rounded-lg <?= $tipo_mensaje === 'success' ? 'bg-green-500 bg-opacity-20 border border-green-400' : 'bg-red-500 bg-opacity-20 border border-red-400' ?>">
                    <i class="fas <?= $tipo_mensaje === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle' ?> mr-2"></i>
                    <?= htmlspecialchars($mensaje) ?>
                </div>
                <?php endif; ?>
                
                <ul class="space-y-2" id="poa-categories">
                    <?php foreach (array_keys($camposPorTipo) as $categoria): ?>
                    <li class="category-item rounded-lg cursor-pointer p-3 text-sm font-medium"
                        onclick="mostrarTabla('<?= $categoria ?>')">
                        <i class="fas fa-chevron-right mr-2"></i>
                        <?= strtoupper(str_replace('_', ' ', $categoria)) ?>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </aside>

        <!-- Área principal -->
        <main class="flex-1 form-container overflow-y-auto">
            <div class="p-8">
                <div class="max-w-full">
                    <!-- Título dinámico -->
                    <div class="mb-8">
                        <h1 class="text-3xl font-bold text-gray-800 mb-2" id="main-title">
                            Visualizar Registros POA
                        </h1>
                        <p class="text-gray-600">Selecciona una categoría para ver los registros existentes</p>
                    </div>

                    <!-- Tabla de registros -->
                    <div class="hidden bg-white rounded-2xl shadow-xl card-shadow" id="tabla-registros">
                        <div class="p-6">
                            <div class="flex justify-between items-center mb-6">
                                <h2 class="text-xl font-bold text-gray-800" id="tabla-titulo"></h2>
                                <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm font-medium" id="contador-registros"></span>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full table-auto" id="tabla-datos">
                                    <thead id="tabla-head"></thead>
                                    <tbody id="tabla-body"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Formulario de edición -->
                    <div class="hidden bg-white rounded-2xl shadow-xl p-8 card-shadow mt-8" id="formulario-edicion">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-xl font-bold text-gray-800">Editar Registro</h2>
                            <button onclick="cancelarEdicion()" class="text-gray-500 hover:text-gray-700">
                                <i class="fas fa-times text-xl"></i>
                            </button>
                        </div>
                        
                        <form method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-6" id="form-edicion">
                            <input type="hidden" name="accion" value="actualizar">
                            <input type="hidden" name="categoria" id="edit-categoria">
                            <input type="hidden" name="id" id="edit-id">
                            
                            <div id="campos-edicion"></div>
                            
                            <div class="col-span-full pt-6 border-t border-gray-200">
                                <div class="flex justify-end space-x-4">
                                    <button type="button" onclick="cancelarEdicion()" 
                                            class="px-6 py-3 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition font-medium">
                                        <i class="fas fa-times mr-2"></i>Cancelar
                                    </button>
                                    <button type="submit" 
                                            class="px-8 py-3 gradient-bg text-white rounded-lg hover:shadow-lg transition font-medium hover-scale">
                                        <i class="fas fa-save mr-2"></i>Actualizar
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Estado inicial -->
                    <div class="text-center py-20" id="estado-inicial">
                        <i class="fas fa-table text-6xl text-gray-300 mb-6"></i>
                        <h3 class="text-xl font-semibold text-gray-600 mb-2">¡Visualiza tus registros POA!</h3>
                        <p class="text-gray-500">Selecciona una categoría del menú lateral para ver los registros existentes</p>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal de confirmación para eliminar -->
    <div class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50" id="modal-eliminar">
        <div class="bg-white rounded-lg p-6 m-4 max-w-sm w-full">
            <div class="text-center">
                <i class="fas fa-exclamation-triangle text-red-500 text-4xl mb-4"></i>
                <h3 class="text-lg font-bold text-gray-900 mb-2">Confirmar Eliminación</h3>
                <p class="text-gray-600 mb-6">¿Estás seguro de que deseas eliminar este registro? Esta acción no se puede deshacer.</p>
                <div class="flex space-x-4">
                    <button onclick="cerrarModalEliminar()" class="flex-1 px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition">
                        Cancelar
                    </button>
                    <button onclick="confirmarEliminacion()" class="flex-1 px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition">
                        Eliminar
                    </button>
                </div>
            </div>
        </div>
    </div>

<script>
    const camposPorTipo = <?= json_encode($camposPorTipo) ?>;
    let categoriaActual = '';
    let registroAEliminar = null;
    
    async function mostrarTabla(categoria) {
        categoriaActual = categoria;
        
        // Actualizar elementos activos
        document.querySelectorAll('.category-item').forEach(item => {
            item.classList.remove('active');
        });
        if (event && event.target) {
            event.target.classList.add('active');
        }
        
        // Ocultar otros elementos
        document.getElementById('estado-inicial').classList.add('hidden');
        document.getElementById('formulario-edicion').classList.add('hidden');
        
        // Actualizar títulos
        document.getElementById('main-title').textContent = `Registros: ${categoria.replace(/_/g, ' ').toUpperCase()}`;
        document.getElementById('tabla-titulo').textContent = categoria.replace(/_/g, ' ').toUpperCase();
        
        try {
            // Cargar registros via AJAX
            const response = await fetch(`obtener_registros.php?tabla=${categoria}`);
            const registros = await response.json();
            
            generarTabla(categoria, registros);
            document.getElementById('tabla-registros').classList.remove('hidden');
            
        } catch (error) {
            console.error('Error al cargar registros:', error);
        }
    }
    
    function generarTabla(categoria, registros) {
        const thead = document.getElementById('tabla-head');
        const tbody = document.getElementById('tabla-body');
        const contador = document.getElementById('contador-registros');
        
        // Actualizar contador
        contador.textContent = `${registros.length} registros`;
        
        // Limpiar tabla
        thead.innerHTML = '';
        tbody.innerHTML = '';
        
        if (registros.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="100%" class="text-center py-8 text-gray-500">
                        <i class="fas fa-inbox text-4xl mb-4 block"></i>
                        No hay registros para mostrar
                    </td>
                </tr>
            `;
            return;
        }
        
        // Generar encabezados
        const campos = Object.keys(camposPorTipo[categoria]);
        const headerRow = document.createElement('tr');
        headerRow.className = 'bg-gray-50';
        
        // ID column
        headerRow.innerHTML = '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>';
        
        // Campo columns
        campos.forEach(campo => {
            headerRow.innerHTML += `<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">${campo.replace(/_/g, ' ')}</th>`;
        });
        
        // Actions column
        headerRow.innerHTML += '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>';
        
        thead.appendChild(headerRow);
        
        // Generar filas de datos
        registros.forEach(registro => {
            const row = document.createElement('tr');
            row.className = 'table-hover border-b border-gray-200';
            row.setAttribute('data-id', registro.id); // Agregar data-id para verificación
            
            // ID cell
            row.innerHTML = `<td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${registro.id}</td>`;
            
            // Campo cells
            campos.forEach(campo => {
                let valor = registro[campo] || '';
                
                // Format boolean values
                if (typeof valor === 'boolean') {
                    valor = valor ? 'SÍ' : 'NO';
                }
                
                // Truncate long text
                if (typeof valor === 'string' && valor.length > 50) {
                    valor = valor.substring(0, 50) + '...';
                }
                
                row.innerHTML += `<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${valor}</td>`;
            });
            
            // Actions cell
            row.innerHTML += `
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <button onclick="editarRegistro('${categoria}', ${registro.id})" 
                            class="text-blue-600 hover:text-blue-900 mr-3">
                        <i class="fas fa-edit"></i> Editar
                    </button>
                    <button onclick="eliminarRegistro('${categoria}', ${registro.id})" 
                            class="text-red-600 hover:text-red-900">
                        <i class="fas fa-trash"></i> Eliminar
                    </button>
                </td>
            `;
            
            tbody.appendChild(row);
        });
    }
    
    async function editarRegistro(categoria, id) {
        try {
            const response = await fetch(`obtener_registro.php?tabla=${categoria}&id=${id}`);
            const registro = await response.json();
            
            if (registro) {
                mostrarFormularioEdicion(categoria, registro);
            }
        } catch (error) {
            console.error('Error al cargar registro:', error);
        }
    }
    
    function mostrarFormularioEdicion(categoria, registro) {
        document.getElementById('edit-categoria').value = categoria;
        document.getElementById('edit-id').value = registro.id;
        
        const camposEdicion = document.getElementById('campos-edicion');
        camposEdicion.innerHTML = '';
        
        // Generar campos de edición
        if (camposPorTipo[categoria]) {
            Object.entries(camposPorTipo[categoria]).forEach(([campo, tipo]) => {
                const label = campo.replace(/_/g, ' ').toUpperCase();
                const valor = registro[campo] || '';
                let inputHTML = '';
                
                if (tipo === 'select') {
                    const selected1 = (valor === true || valor === 'SI') ? 'selected' : '';
                    const selected2 = (valor === false || valor === 'NO') ? 'selected' : '';
                    inputHTML = `
                        <select name="${campo}" class="w-full p-3 border border-gray-300 rounded-lg input-focus transition">
                            <option value="">-- Seleccionar --</option>
                            <option value="SI" ${selected1}>SÍ</option>
                            <option value="NO" ${selected2}>NO</option>
                        </select>
                    `;
                } else {
                    const inputType = tipo === 'url' ? 'url' : tipo;
                    inputHTML = `
                        <input name="${campo}" 
                                type="${inputType}" 
                                value="${valor}"
                                class="w-full p-3 border border-gray-300 rounded-lg input-focus transition" 
                                ${tipo === 'url' ? 'placeholder="https://ejemplo.com"' : ''}>
                    `;
                }
                
                const fieldHTML = `
                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-gray-700">
                            <i class="fas fa-edit mr-2 text-blue-500"></i>
                            ${label}
                        </label>
                        ${inputHTML}
                    </div>
                `;
                
                camposEdicion.innerHTML += fieldHTML;
            });
        }
        
        document.getElementById('formulario-edicion').classList.remove('hidden');
        document.getElementById('formulario-edicion').scrollIntoView({ behavior: 'smooth' });
    }

    function eliminarRegistro(categoria, id) {
        registroAEliminar = { categoria: categoria, id: id };
        document.getElementById('modal-eliminar').classList.remove('hidden');
        document.getElementById('modal-eliminar').classList.add('flex');
    }

    function cerrarModalEliminar() {
        document.getElementById('modal-eliminar').classList.add('hidden');
        document.getElementById('modal-eliminar').classList.remove('flex');
        registroAEliminar = null;
    }

    // SOLUCIÓN OPTIMIZADA: Solo reemplaza estas funciones en tu script actual

    async function confirmarEliminacion() {
        if (!registroAEliminar) return;
        
        // Mostrar mensaje de procesamiento
        const mensajeProcesando = mostrarMensajeCarga('Eliminando registro...');
        
        try {
            // Crear formulario para envío POST
            const formData = new FormData();
            formData.append('accion', 'eliminar');
            formData.append('categoria', registroAEliminar.categoria);
            formData.append('id', registroAEliminar.id);
            
            // Enviar solicitud de eliminación
            const response = await fetch(window.location.href, {
                method: 'POST',
                body: formData
            });
            
            if (response.ok) {
                // Ocultar mensaje de carga
                ocultarMensajeCarga(mensajeProcesando);
                
                // MÉTODO OPTIMIZADO: Verificar eliminación por DOM en lugar de base de datos
                const eliminacionExitosa = await verificarEliminacionPorDOM(registroAEliminar.categoria, registroAEliminar.id);
                
                if (eliminacionExitosa) {
                    mostrarMensaje('Registro eliminado exitosamente', 'success');
                } else {
                    mostrarMensaje('El registro se está eliminando, por favor espera...', 'info');
                    // Recargar después de un delay adicional
                    setTimeout(async () => {
                        await mostrarTabla(registroAEliminar.categoria);
                        mostrarMensaje('Registro eliminado exitosamente', 'success');
                    }, 3000);
                }
                
                // Cerrar modal
                cerrarModalEliminar();
            } else {
                ocultarMensajeCarga(mensajeProcesando);
                throw new Error('Error en la respuesta del servidor');
            }
            
        } catch (error) {
            ocultarMensajeCarga(mensajeProcesando);
            console.error('Error al eliminar registro:', error);
            mostrarMensaje('Error al eliminar el registro', 'error');
            cerrarModalEliminar();
        }
    }

    // Verificación optimizada usando solo el DOM (sin consultas adicionales a la BD)
    async function verificarEliminacionPorDOM(categoria, id) {
        try {
            // Esperar 2 segundos para que la BD procese
            await new Promise(resolve => setTimeout(resolve, 2000));
            
            // Recargar la tabla
            await mostrarTabla(categoria);
            
            // Verificar si el elemento sigue en el DOM
            const elemento = document.querySelector(`tr[data-id="${id}"]`);
            
            // Si no existe el elemento, la eliminación fue exitosa
            return !elemento;
            
        } catch (error) {
            console.error('Error en verificación por DOM:', error);
            return false; // En caso de error, asumir que no se eliminó
        }
    }

    // Mensaje de carga simple
    function mostrarMensajeCarga(mensaje) {
        const mensajeDiv = document.createElement('div');
        mensajeDiv.id = 'mensaje-carga-unico';
        mensajeDiv.className = 'fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 bg-blue-500 text-white';
        
        mensajeDiv.innerHTML = `
            <div class="flex items-center">
                <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div>
                ${mensaje}
            </div>
        `;
        
        document.body.appendChild(mensajeDiv);
        return mensajeDiv;
    }

    function ocultarMensajeCarga(mensajeDiv) {
        if (mensajeDiv && mensajeDiv.parentNode) {
            mensajeDiv.parentNode.removeChild(mensajeDiv);
        }
        // También remover por ID como respaldo
        const elemento = document.getElementById('mensaje-carga-unico');
        if (elemento && elemento.parentNode) {
            elemento.parentNode.removeChild(elemento);
        }
    }

    // Función mejorada para mostrar mensajes sin duplicados
    function mostrarMensaje(mensaje, tipo) {
        const mensajesAnteriores = document.querySelectorAll('.mensaje-notificacion');
        mensajesAnteriores.forEach(msg => {
            if (msg.parentNode) {
                msg.parentNode.removeChild(msg);
            }
        });
        
        const mensajeDiv = document.createElement('div');
        mensajeDiv.className = `mensaje-notificacion fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50`;
        
        let claseColor, icono;
        switch(tipo) {
            case 'success':
                claseColor = 'bg-green-500 text-white';
                icono = 'fa-check-circle';
                break;
            case 'error':
                claseColor = 'bg-red-500 text-white';
                icono = 'fa-exclamation-triangle';
                break;
            case 'info':
                claseColor = 'bg-yellow-500 text-white';
                icono = 'fa-info-circle';
                break;
            default:
                claseColor = 'bg-gray-500 text-white';
                icono = 'fa-info-circle';
        }
        
        mensajeDiv.className += ` ${claseColor}`;
        
        mensajeDiv.innerHTML = `
            <div class="flex items-center">
                <i class="fas ${icono} mr-2"></i>
                ${mensaje}
            </div>
        `;
        
        document.body.appendChild(mensajeDiv);
        
        setTimeout(() => {
            if (mensajeDiv.parentNode) {
                mensajeDiv.parentNode.removeChild(mensajeDiv);
            }
        }, 4000);
    }
</script>

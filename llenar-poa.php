<?php
// Incluir la conexión a la base de datos
require_once 'conexion.php';

session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_nombre'])) {
    header("Location: index.php");
    exit;
}

// Obtener datos del usuario de la sesión
$nombre_usuario = $_SESSION['usuario_nombre'];

// Procesar el formulario cuando se envía
if ($_POST && isset($_POST['categoria'])) {
    try {
        $categoria = $_POST['categoria'];
        $campos = [];
        $valores = [];
        $placeholders = [];
        
        // Obtener el unificacion_id desde la sesión o parámetro
        $unificacion_id = $_POST['unificacion_id'] ?? 1; 
        
        $campos[] = 'unificacion_id';
        $valores[] = $unificacion_id;
        $placeholders[] = '?';
        
        // Procesar cada campo del formulario
        foreach ($_POST as $key => $value) {
            if ($key !== 'categoria' && $key !== 'unificacion_id' && $value !== '') {
                $campos[] = $key;
                
                // Definir todos los campos que manejan SI/NO
                $camposBooleanos = [
                    'realizado', 'terminado', 'realizada', 'concretada', 
                    'aprobado', 'terminados'
                ];
                
                if (in_array($key, $camposBooleanos)) {
                    // Manejo unificado para SI/NO
                    $valorLimpio = strtoupper(trim($value));
                    if ($valorLimpio === 'SI' || $valorLimpio === 'SÍ') {
                        $valores[] = 1; // TRUE como 1
                    } else {
                        $valores[] = 0; // FALSE como 0 (incluyendo "NO")
                    }
                } else {
                    $valores[] = $value;
                }
                
                $placeholders[] = '?';
            }
        }
        
        // Construir la consulta SQL dinámicamente
        $sql = "INSERT INTO $categoria (" . implode(', ', $campos) . ") VALUES (" . implode(', ', $placeholders) . ")";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute($valores);
        
        $mensaje = "Actividad guardada exitosamente";
        $tipo_mensaje = "success";
    } catch (PDOException $e) {
        $mensaje = "Error al guardar la actividad: " . $e->getMessage();
        $tipo_mensaje = "error";
    }
}

// Definir los campos por tipo de actividad
$camposPorTipo = [
    "ppa" => [
        "nivel_formacion" => "select_nivel",
        "ppa_realizado" => "text", 
        "terminado" => "select",
        "evidencia_link" => "url",
        "semester" => "select_semester"
    ],
    "visita_regional" => [
        "visitas_regionales" => "text",
        "estudiantes" => "number",
        "realizado" => "select",
        "evidencia_link" => "url",
        "semester" => "select_semester"
    ],
    "visita_nacional" => [
        "visita_nacional" => "text",
        "estudiantes" => "number",
        "realizada" => "select",
        "evidencia_link" => "url",
        "semester" => "select_semester"
    ],
    "charlas" => [
        "nombre_evento" => "text",
        "estudiantes" => "number",
        "realizada" => "select",
        "evidencia_link" => "url",
        "semester" => "select_semester"
    ],
    "feria" => [
        "nombre_evento" => "text",
        "estudiantes" => "number",
        "realizada" => "select",
        "evidencia_link" => "url",
        "semester" => "select_semester"
    ],
    "congreso" => [
        "nombre_evento" => "text",
        "estudiantes" => "number",
        "realizado" => "select",
        "evidencia_link" => "url",
        "semester" => "select_semester"
    ],
    "evento_nacional" => [
        "nombre_evento" => "text",
        "estudiantes" => "number",
        "realizado" => "select",
        "evidencia_link" => "url",
        "semester" => "select_semester"
    ],
    "biblio" => [
        "asignaturas_programadas" => "text",
        "bibliografia_ingles" => "text",
        "evidencia_link" => "url",
        "semester" => "select_semester"
    ],
    "profesor_visitante" => [
        "profesor" => "text",
        "tema" => "text",
        "estudiantes" => "number",
        "realizada" => "select",
        "evidencia_link" => "url",
        "semester" => "select_semester"
    ],
    "actividades_interculturales" => [
        "actividad" => "text",
        "estudiantes" => "number",
        "realizada" => "select",
        "evidencia_link" => "url",
        "semester" => "select_semester"
    ],
    "actividades_universidades_internacionales" => [
        "actividad" => "text",
        "actividad_conjunta" => "text",
        "estudiantes" => "number",
        "realizada" => "select",
        "evidencia_link" => "url",
        "semester" => "select_semester"
    ],
    "productos_en_ingles" => [
        "producto" => "text",
        "estudiantes" => "number",
        "terminados" => "select",
        "evidencia_link" => "url",
        "semester" => "select_semester"
    ],
    "ppa_traducidos" => [
        "documento" => "text",
        "estudiantes" => "number",
        "asignatura" => "text",
        "terminados" => "select",
        "evidencia_link" => "url",
        "semester" => "select_semester"
    ],
    "coil" => [
        "universidad" => "text",
        "asignatura" => "text",
        "tema" => "text",
        "realizada" => "select",
        "evidencia_link" => "url",
        "semester" => "select_semester"
    ],
    "clase_espejo" => [
        "universidad" => "text",
        "asignatura" => "text",
        "tema" => "text",
        "realizada" => "select",
        "evidencia_link" => "url",
        "semester" => "select_semester"
    ],
    "reto_empresa" => [
        "empresa" => "text",
        "reto" => "text",
        "estudiantes" => "number",
        "realizado" => "select",
        "evidencia_link" => "url",
        "semester" => "select_semester"
    ],
    "grupo_focal" => [
        "experto" => "text",
        "fecha_comite" => "date",
        "evidencia_link" => "url",
        "semester" => "select_semester"
    ],
    "estudio_tendencias" => [
        "informe" => "text",
        "fecha_elaboracion" => "date",
        "acta_comite" => "text",
        "evidencia_link" => "url",
        "semester" => "select_semester"
    ],
    "analisis_contexto" => [
        "informe" => "text",
        "fecha_elaboracion" => "date",
        "acta_comite" => "text",
        "evidencia_link" => "url",
        "semester" => "select_semester"
    ],
    "autoevaluacion_mejoras" => [
        "informe" => "text",
        "fecha_elaboracion" => "date",
        "acta_comite" => "text",
        "evidencia_link" => "url",
        "semester" => "select_semester"
    ],
    "empresas_practicas" => [
        "empresa" => "text",
        "concretada" => "select",
        "evidencia_link" => "url",
        "semester" => "select_semester"
    ],
    "graduados" => [
        "actividades_programadas" => "number",
        "actividades_realizadas" => "number",
        "evidencia_link" => "url",
        "semester" => "select_semester"
    ],
    "mesa_sector" => [
        "participacion" => "text",
        "fecha_encuentro" => "date",
        "tema" => "text",
        "evidencia_link" => "url",
        "semester" => "select_semester"
    ],
    "mejoras_practicas" => [
        "accion" => "text",
        "fecha_implementacion" => "date",
        "evidencia_link" => "url",
        "semester" => "select_semester"
    ],
    "diplomado_grado" => [
        "nivel_formacion" => "select_nivel",
        "tema" => "text",
        "estudiantes" => "number",
        "evidencia_link" => "url",
        "semester" => "select_semester"
    ],
    "formacion_continua" => [
        "curso" => "text",
        "estudiantes" => "number",
        "aprobado" => "select",
        "evidencia_link" => "url",
        "semester" => "select_semester"
    ],
    "mbc_refuerzos" => [
        "competencia" => "text",
        "asignatura" => "text",
        "docente" => "text",
        "actividades_verificadas" => "number",
        "evidencia_link" => "url",
        "semester" => "select_semester"
    ],
    "taller_refuerzo_saber" => [
        "taller" => "text",
        "estudiantes" => "number",
        "fecha" => "date",
        "evidencia_link" => "url",
        "semester" => "select_semester"
    ],
    "proyecto_grado" => [
        "propuesta" => "text",
        "evidencia_link" => "url",
        "semester" => "select_semester"
    ],
    "visita_aula" => [
        "docente" => "text",
        "fecha" => "date",
        "hora" => "time",
        "asignatura" => "text",
        "aula" => "text",
        "calificacion" => "number",
        "evidencia_link" => "url",
        "semester" => "select_semester"
    ],
    "estudio_semilleros_estudiantes" => [
        "estudiante" => "text",
        "fecha_ingreso" => "date",
        "evidencia_link" => "url",
        "semester" => "select_semester"
    ],
    "estudio_semilleros_docentes" => [
        "docente" => "text",
        "fecha_ingreso" => "date",
        "evidencia_link" => "url",
        "semester" => "select_semester"
    ],
    "pep" => [
        "proyecto" => "text",
        "fecha_actualizacion" => "date",
        "fecha_aprobacion" => "date",
        "evidencia_link" => "url",
        "semester" => "select_semester"
    ],
    "herramientas" => [
        "herramienta" => "text",
        "asignatura" => "text",
        "fecha_incorporacion" => "date",
        "evidencia_link" => "url",
        "semester" => "select_semester"
    ],
    "micrositio" => [
        "actualizacion" => "text",
        "fecha_actualizacion" => "date",
        "evidencia_link" => "url",
        "semester" => "select_semester"
    ],
    "modalidad_virtual" => [
        "modulo" => "text",
        "cantidad" => "number",
        "evidencia_link" => "url",
        "semester" => "select_semester"
    ],
    "sitio_interaccion" => [
        "espacio" => "text",
        "cantidad" => "number",
        "evidencia_link" => "url",
        "semester" => "select_semester"
    ],
    "atlas" => [
        "registro" => "text",
        "cantidad" => "number",
        "evidencia_link" => "url",
        "semester" => "select_semester"
    ],
    "seguimiento_estudiantes" => [
        "seguimiento" => "text",
        "cantidad" => "number",
        "evidencia_link" => "url",
        "semester" => "select_semester"
    ],
    "matricula_estudiantes_antiguos" => [
        "matricula" => "text",
        "cantidad" => "number",
        "evidencia_link" => "url",
        "semester" => "select_semester"
    ]
];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SystemPOA - Llenar POA</title>
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
        .form-container {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        }
        .input-focus:focus {
            border-color: #3b8cde;
            box-shadow: 0 0 0 3px rgba(59, 140, 222, 0.1);
        }
        .header-height {
            height: 80px;
        }
        .main-container {
            height: calc(100vh - 80px);
            margin-top: 80px;
        }
    </style>
</head>
<body class="bg-gray-100">
    <!-- Header -->
    <div class="gradient-bg text-white p-4 shadow-lg fixed top-0 left-0 right-0 z-50 header-height flex items-center">
        <div class="flex items-center justify-between w-full">
            <div class="flex items-center space-x-3">
                <i class="fas fa-graduation-cap text-2xl"></i>
                <h1 class="text-2xl font-bold">SystemPOA</h1>
            </div>
            <div class="flex items-center space-x-4">
                <span class="text-sm opacity-90">¡Bienvenido, <?php echo htmlspecialchars($nombre_usuario); ?>!</span>
                <a href="dashboard.php" class="bg-white bg-opacity-20 px-4 py-2 rounded-lg hover:bg-opacity-30 transition">
                    <i class="fas fa-arrow-left mr-2"></i>Dashboard
                </a>
            </div>
        </div>
    </div>

    <div class="flex main-container">
        <!-- Sidebar -->
        <aside class="w-80 gradient-bg text-white shadow-xl overflow-y-auto">
            <div class="p-6">
                <h2 class="text-xl font-bold mb-6 flex items-center">
                    <i class="fas fa-list-ul mr-3"></i>
                    Categorías POA
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
                        onclick="mostrarFormulario('<?= $categoria ?>')">
                        <i class="fas fa-chevron-right mr-2"></i>
                        <?= strtoupper(str_replace('_', ' ', $categoria)) ?>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </aside>

        <!-- Área principal del formulario -->
        <main class="flex-1 form-container overflow-y-auto">
            <div class="p-8">
                <div class="max-w-4xl mx-auto">
                    <!-- Título dinámico -->
                    <div class="mb-8">
                        <h1 class="text-3xl font-bold text-gray-800 mb-2" id="form-title">
                            Selecciona una categoría POA
                        </h1>
                        <p class="text-gray-600">Complete el formulario con la información requerida para registrar la actividad</p>
                    </div>

                    <!-- Formulario -->
                    <div class="hidden bg-white rounded-2xl shadow-xl p-8 card-shadow" id="poa-form">
                        <form method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-6" id="dynamic-form">
                            <input type="hidden" name="categoria" id="categoria-input">
                            <input type="hidden" name="unificacion_id" value="1"> <!-- Ajustar según tu lógica -->
                            
                            <div id="campos-dinamicos"></div>
                            
                            <div class="col-span-full pt-6 border-t border-gray-200">
                                <div class="flex justify-end space-x-4">
                                    <button type="button" onclick="cancelarFormulario()" 
                                            class="px-6 py-3 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition font-medium">
                                        <i class="fas fa-times mr-2"></i>Cancelar
                                    </button>
                                    <button type="submit" 
                                            class="px-8 py-3 gradient-bg text-white rounded-lg hover:shadow-lg transition font-medium hover-scale">
                                        <i class="fas fa-save mr-2"></i>Guardar Actividad
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Estado inicial -->
                    <div class="text-center py-20" id="estado-inicial">
                        <i class="fas fa-clipboard-list text-6xl text-gray-300 mb-6"></i>
                        <h3 class="text-xl font-semibold text-gray-600 mb-2">¡Comienza a llenar tu POA!</h3>
                        <p class="text-gray-500">Selecciona una categoría del menú lateral para comenzar</p>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        const camposPorTipo = <?= json_encode($camposPorTipo) ?>;
        
        // Función para generar opciones de semestres
        function generarSemestres() {
            const currentYear = new Date().getFullYear();
            const semestres = [];
            
            // Generar semestres desde 2025 hasta 5 años en el futuro
            for (let year = 2025; year <= currentYear + 5; year++) {
                semestres.push(`${year}-1`);
                semestres.push(`${year}-2`);
            }
            
            return semestres;
        }
        
        function mostrarFormulario(categoria) {
            const formTitle = document.getElementById('form-title');
            const poaForm = document.getElementById('poa-form');
            const estadoInicial = document.getElementById('estado-inicial');
            const categoriaInput = document.getElementById('categoria-input');
            const camposDinamicos = document.getElementById('campos-dinamicos');
            
            // Actualizar título
            formTitle.textContent = `Formulario: ${categoria.replace(/_/g, ' ').toUpperCase()}`;
            
            // Establecer categoría
            categoriaInput.value = categoria;
            
            // Limpiar campos anteriores
            camposDinamicos.innerHTML = '';
            
            // Generar campos dinámicos
            if (camposPorTipo[categoria]) {
                Object.entries(camposPorTipo[categoria]).forEach(([campo, tipo]) => {
                    const label = campo.replace(/_/g, ' ').toUpperCase();
                    let inputHTML = '';
                    
                    if (tipo === 'select') {
                        inputHTML = `
                            <select name="${campo}" class="w-full p-3 border border-gray-300 rounded-lg input-focus transition" required>
                                <option value=""> Seleccionar </option>
                                <option value="SI">SÍ</option>
                                <option value="NO">NO</option>
                            </select>
                        `;
                    } else if (tipo === 'select_nivel') {
                        inputHTML = `
                            <select name="${campo}" class="w-full p-3 border border-gray-300 rounded-lg input-focus transition" required>
                                <option value=""> Seleccionar Nivel </option>
                                <option value="Técnico">Técnico</option>
                                <option value="Tecnología">Tecnología</option>
                                <option value="Profesional">Profesional</option>
                            </select>
                        `;
                    } else if (tipo === 'select_semester') {
                        const semestres = generarSemestres();
                        let opcionesSemestres = '<option value=""> Seleccionar Semestre </option>';
                        semestres.forEach(semestre => {
                            opcionesSemestres += `<option value="${semestre}">${semestre}</option>`;
                        });
                        
                        inputHTML = `
                            <select name="${campo}" class="w-full p-3 border border-gray-300 rounded-lg input-focus transition" required>
                                ${opcionesSemestres}
                            </select>
                        `;
                    } else {
                        const inputType = tipo === 'url' ? 'url' : tipo;
                        inputHTML = `
                            <input name="${campo}" 
                                   type="${inputType}" 
                                   class="w-full p-3 border border-gray-300 rounded-lg input-focus transition" 
                                   ${tipo === 'url' ? 'placeholder="https://ejemplo.com"' : ''}
                                   required>
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
                    
                    camposDinamicos.innerHTML += fieldHTML;
                });
            }
            
            // Mostrar formulario y ocultar estado inicial
            estadoInicial.classList.add('hidden');
            poaForm.classList.remove('hidden');
            
            // Scroll al formulario
            poaForm.scrollIntoView({ behavior: 'smooth' });
        }
        
        function cancelarFormulario() {
            const poaForm = document.getElementById('poa-form');
            const estadoInicial = document.getElementById('estado-inicial');
            const formTitle = document.getElementById('form-title');
            
            poaForm.classList.add('hidden');
            estadoInicial.classList.remove('hidden');
            formTitle.textContent = 'Selecciona una categoría POA';
            
            // Reset form
            document.getElementById('dynamic-form').reset();
        }

        // Mostrar mensaje de confirmación al enviar
        document.getElementById('dynamic-form').addEventListener('submit', function(e) {
            const submitButton = this.querySelector('button[type="submit"]');
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Guardando...';
            submitButton.disabled = true;
        });
    </script>
</body>
</html>
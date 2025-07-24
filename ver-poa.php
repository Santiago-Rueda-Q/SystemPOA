<?php
// Incluir la conexión a la base de datos
require_once 'conexion.php';

session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_nombre'])) {
    header("Location: index.php");
    exit;
}

// CORRECCIÓN: Obtener el nombre del usuario ANTES de usarlo
$nombre_usuario = $_SESSION['usuario_nombre'];

// Obtener datos del usuario de la sesión
$stmt = $conn->prepare("SELECT id, rol FROM usuario WHERE nombre = ?");
$stmt->execute([$nombre_usuario]);
$usuario_data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario_data) {
    header("Location: index.php");
    exit;
}

$usuario_id = $usuario_data['id'];
$usuario_rol = $usuario_data['rol'];

// Obtener las categorías disponibles según el rol
$camposPorTipo = obtenerCategoriasDisponibles($conn, $usuario_id, $usuario_rol);

if (isset($_GET['action']) && $_GET['action'] === 'get_records') {
    $categoria = $_GET['categoria'] ?? '';
    
    if (!empty($categoria) && array_key_exists($categoria, $camposPorTipo)) {
        header('Content-Type: application/json');
        
        try {
            if ($usuario_rol === 'Docente') {
                $sql = "SELECT * FROM $categoria ORDER BY id DESC LIMIT 100";
                $stmt = $conn->prepare($sql);
                $stmt->execute();
            } else {
                // Directores ven todos los registros
                $sql = "SELECT * FROM $categoria ORDER BY id DESC LIMIT 100";
                $stmt = $conn->prepare($sql);
                $stmt->execute();
            }
            
            $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'data' => $registros,
                'count' => count($registros)
            ]);
        } catch (PDOException $e) {
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Categoría no especificada o sin acceso']);
    }
    exit;
}

// Procesar actualización cuando se envía
if ($_POST && isset($_POST['accion']) && $_POST['accion'] === 'actualizar') {
    $categoria = $_POST['categoria'];
     
    if (!array_key_exists($categoria, $camposPorTipo)) {
        $mensaje = "No tienes acceso a esta categoría";
        $tipo_mensaje = "error";
    } else {
        try {
            $id = $_POST['id'];
            $campos = [];
            $valores = [];
            
            // Procesar cada campo del formulario
            foreach ($_POST as $key => $value) {
                if ($key !== 'categoria' && $key !== 'id' && $key !== 'accion' && !empty($value)) {
                    // Convertir campos boolean
                    $booleanFields = ['realizado', 'true', 'TRUE', 'concretada', 'aprobado', 'terminados'];
                    
                    if (in_array($key, $booleanFields)) {
                        if (strtoupper($value) === 'SI' || strtoupper($value) === 'SÍ') {
                            $campos[] = "$key = 1";
                        } else {
                            $campos[] = "$key = 0";
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
}

// Procesar eliminación
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'eliminar') {
    $categoria = $_POST['categoria'] ?? '';
    $id = $_POST['id'] ?? '';
    
    // NUEVA VALIDACIÓN: Verificar acceso a la categoría
    if (!empty($categoria) && !empty($id) && array_key_exists($categoria, $camposPorTipo)) {
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
        echo "Parámetros incompletos o sin acceso";
    }
    exit;
}

// Definir los campos por tipo de actividad
function obtenerCategoriasDisponibles($conn, $usuario_id, $usuario_rol) {
    if ($usuario_rol === 'Director') {
        // Los directores pueden ver todas las categorías
        return [
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
    } else if ($usuario_rol === 'Docente') {
        // Los docentes solo ven las categorías que tienen asignadas y activas
        try {
            $stmt = $conn->prepare("
                SELECT nombre_categoria 
                FROM configuracion_poa 
                WHERE docente_id = ? AND activo = TRUE
            ");
            $stmt->execute([$usuario_id]);
            $categorias_asignadas = $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            // Si hay error en la consulta, log el error y retornar array vacío
            error_log("Error al obtener categorías para docente: " . $e->getMessage());
            return [];
        }
        
        // Definir todos los campos disponibles (igual que el array original completo)
        $todos_los_campos = [
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

        $categorias_filtradas = [];
        foreach ($categorias_asignadas as $categoria) {
            if (isset($todos_los_campos[$categoria])) {
                $categorias_filtradas[$categoria] = $todos_los_campos[$categoria];
            }
        }
        
        return $categorias_filtradas;
    }
    
    return [];
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
    <link rel="icon" type="image/png" href="resource/fesc.png">
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
        .header-height {
            height: 80px;
        }
        .main-container {
            height: calc(100vh - 80px);
            margin-top: 80px;
        }
        .btn-edit {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
        }
        .btn-edit:hover {
            background: linear-gradient(135deg, #1d4ed8 0%, #1e3a8a 100%);
        }
        .btn-delete {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        }
        .btn-delete:hover {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
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
                    <?php if (empty($camposPorTipo)): ?>
                        <li class="text-white text-sm p-3 bg-red-500 bg-opacity-20 rounded-lg">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            <?php if ($usuario_rol === 'Docente'): ?>
                                No tienes categorías asignadas para visualizar. Contacta al Director.
                            <?php else: ?>
                                No hay categorías disponibles.
                            <?php endif; ?>
                        </li>
                    <?php else: ?>
                        <?php foreach (array_keys($camposPorTipo) as $categoria): ?>
                        <li class="category-item rounded-lg cursor-pointer p-3 text-sm font-medium"
                            onclick="mostrarTabla('<?= $categoria ?>')">
                            <i class="fas fa-chevron-right mr-2"></i>
                            <?= strtoupper(str_replace('_', ' ', $categoria)) ?>
                        </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
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
    let registrosActuales = [];
    
    // Función para generar opciones de semestres
    function generarSemestres() {
        const currentYear = new Date().getFullYear();
        const semestres = [];
        
        for (let year = 2025; year <= currentYear + 5; year++) {
            semestres.push(`${year}-1`);
            semestres.push(`${year}-2`);
        }
        
        return semestres;
    }
    
    async function mostrarTabla(categoria) {
        categoriaActual = categoria;
        
        // Actualizar elementos activos
        document.querySelectorAll('.category-item').forEach(item => {
            item.classList.remove('active');
        });
        event.target.classList.add('active');
        
        // Ocultar otros elementos
        document.getElementById('estado-inicial').classList.add('hidden');
        document.getElementById('formulario-edicion').classList.add('hidden');
        
        // Mostrar indicador de carga
        document.getElementById('tabla-registros').classList.remove('hidden');
        document.getElementById('tabla-body').innerHTML = `
            <tr>
                <td colspan="100%" class="text-center py-8">
                    <i class="fas fa-spinner fa-spin text-blue-500 text-2xl mb-2"></i>
                    <p class="text-gray-600">Cargando registros...</p>
                </td>
            </tr>
        `;

        // Actualizar títulos
        document.getElementById('main-title').textContent = `Registros: ${categoria.replace(/_/g, ' ').toUpperCase()}`;
        document.getElementById('tabla-titulo').textContent = categoria.replace(/_/g, ' ').toUpperCase();
        
        try {
            // Cargar datos vía AJAX
            const response = await fetch(`?action=get_records&categoria=${categoria}`);
            const result = await response.json();
            
            if (result.success) {
                registrosActuales = result.data;
                generarTabla(categoria, result.data);
            } else {
                throw new Error(result.error || 'Error desconocido');
            }
            
        } catch (error) {
            console.error('Error al cargar registros:', error);
            document.getElementById('tabla-body').innerHTML = `
                <tr>
                    <td colspan="100%" class="text-center py-8 text-red-500">
                        <i class="fas fa-exclamation-triangle text-2xl mb-2"></i>
                        <p>Error al cargar los registros: ${error.message}</p>
                    </td>
                </tr>
            `;
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
            row.setAttribute('data-id', registro.id);
            
            // ID cell
            row.innerHTML = `<td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${registro.id}</td>`;
            
            // Campo cells
            campos.forEach(campo => {
                let valor = registro[campo] || '';
                
                // Format boolean values
                if (valor === 1 || valor === '1' || valor === 0 || valor === '0' || valor === 'true' || valor === 'false') {
                    valor = (valor == 1 || valor === '1') ? 
                           '<span class="text-green-600 font-medium">SI</span>' : 
                           '<span class="text-red-600 font-medium">NO</span>';
                } else if (campo === 'evidencia_link' && valor) {
                    valor = `<a href="${valor}" target="_blank" class="text-blue-600 hover:underline"><i class="fas fa-external-link-alt"></i> Ver</a>`;
                } else {
                    // Truncar texto largo
                    if (typeof valor === 'string' && valor.length > 50) {
                        valor = valor.substring(0, 50) + '...';
                    }
                    valor = valor || '-';
                }
                
                row.innerHTML += `<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${valor}</td>`;
            });
            
            // Actions cell - COMPLETANDO LA PARTE FALTANTE
            row.innerHTML += `
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <button onclick="editarRegistro('${categoria}', ${registro.id})" 
                            class="btn-edit text-white px-3 py-1 rounded-lg mr-2 hover-scale transition">
                        <i class="fas fa-edit mr-1"></i>Editar
                    </button>
                    <button onclick="eliminarRegistro('${categoria}', ${registro.id})" 
                            class="btn-delete text-white px-3 py-1 rounded-lg hover-scale transition">
                        <i class="fas fa-trash mr-1"></i>Eliminar
                    </button>
                </td>
            `;
            
            tbody.appendChild(row);
        });
    }

    function editarRegistro(categoria, id) {
        // Buscar en los registros ya cargados
        const registro = registrosActuales.find(r => r.id == id);
    
        if (!registro) {
                alert('Registro no encontrado');
                return;
        }

        // Mostrar formulario de edición
        document.getElementById('tabla-registros').classList.add('hidden');
        document.getElementById('formulario-edicion').classList.remove('hidden');
    
        // Llenar campos ocultos
        document.getElementById('edit-categoria').value = categoria;
        document.getElementById('edit-id').value = id;
    
    // Generar campos de edición
    const camposContainer = document.getElementById('campos-edicion');
    const campos = camposPorTipo[categoria];
    let camposHTML = '';
    
    Object.keys(campos).forEach(campo => {
        const tipo = campos[campo];
        const valor = registro[campo] || '';
        let inputHTML = '';
        
        switch (tipo) {
            case 'text':
                inputHTML = `<input type="text" name="${campo}" value="${valor}" class="w-full px-4 py-3 border border-gray-300 rounded-lg input-focus transition">`;
                break;
            case 'number':
                inputHTML = `<input type="number" name="${campo}" value="${valor}" class="w-full px-4 py-3 border border-gray-300 rounded-lg input-focus transition">`;
                break;
            case 'date':
                inputHTML = `<input type="date" name="${campo}" value="${valor}" class="w-full px-4 py-3 border border-gray-300 rounded-lg input-focus transition">`;
                break;
            case 'time':
                inputHTML = `<input type="time" name="${campo}" value="${valor}" class="w-full px-4 py-3 border border-gray-300 rounded-lg input-focus transition">`;
                break;
            case 'url':
                inputHTML = `<input type="url" name="${campo}" value="${valor}" class="w-full px-4 py-3 border border-gray-300 rounded-lg input-focus transition">`;
                break;
            case 'select':
                // Determinar opciones basadas en el campo específico
                let opciones = '';
                const booleanFields = ['realizado', 'terminado', 'realizada', 'concretada', 'aprobado', 'terminados'];
                
                if (booleanFields.includes(campo)) {
                    const valorBoolean = (valor == 1 || valor === '1');                    opciones = `
                        <option value="">Seleccionar...</option>
                        <option value="SI" ${valorBoolean ? 'selected' : ''}>SI</option>
                        <option value="NO" ${!valorBoolean ? 'selected' : ''}>NO</option>
                    `;
                }
                
                inputHTML = `
                    <select name="${campo}" class="w-full px-4 py-3 border border-gray-300 rounded-lg input-focus transition">
                        ${opciones}
                    </select>
                `;
                break;
            case 'select_nivel':
                inputHTML = `
                    <select name="${campo}" class="w-full px-4 py-3 border border-gray-300 rounded-lg input-focus transition">
                        <option value="">Seleccionar...</option>
                        <option value="Técnico" ${valor === 'Técnico' ? 'selected' : ''}>Técnico</option>
                        <option value="Tecnología" ${valor === 'Tecnología' ? 'selected' : ''}>Tecnología</option>
                        <option value="Profesional" ${valor === 'Profesional' ? 'selected' : ''}>Profesional</option>
                    </select>
                `;
                break;
            case 'select_semester':
                const semestres = generarSemestres();
                const opcionesSemestre = semestres.map(sem => 
                    `<option value="${sem}" ${valor === sem ? 'selected' : ''}>${sem}</option>`
                ).join('');
                inputHTML = `
                    <select name="${campo}" class="w-full px-4 py-3 border border-gray-300 rounded-lg input-focus transition">
                        <option value="">Seleccionar...</option>
                        ${opcionesSemestre}
                    </select>
                `;
                break;
            default:
                inputHTML = `<input type="text" name="${campo}" value="${valor}" class="w-full px-4 py-3 border border-gray-300 rounded-lg input-focus transition">`;
        }
        
        camposHTML += `
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    ${campo.replace(/_/g, ' ').toUpperCase()}
                </label>
                ${inputHTML}
            </div>
        `;
    });
    
    camposContainer.innerHTML = camposHTML;
    }

    function eliminarRegistro(categoria, id) {
        registroAEliminar = { categoria, id };
        document.getElementById('modal-eliminar').classList.remove('hidden');
        document.getElementById('modal-eliminar').classList.add('flex');
    }

    function cerrarModalEliminar() {
        document.getElementById('modal-eliminar').classList.add('hidden');
        document.getElementById('modal-eliminar').classList.remove('flex');
        registroAEliminar = null;
    }

    async function confirmarEliminacion() {
    if (!registroAEliminar) return;
    
    const { categoria, id } = registroAEliminar;
    
    try {
        const formData = new FormData();
        formData.append('accion', 'eliminar');
        formData.append('categoria', categoria);
        formData.append('id', id);
        
        const response = await fetch(window.location.href, {
            method: 'POST',
            body: formData
        });
        
        if (response.ok) {
            const responseText = await response.text();
            if (responseText === 'OK') {
                // Recargar la tabla después de eliminar
                location.reload();
            } else {
                alert('Error al eliminar: ' + responseText);
            }
        } else {
            alert('Error en la respuesta del servidor');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error al eliminar el registro');
    }
    
    cerrarModalEliminar();
    }

    function actualizarRegistro(event) {
        event.preventDefault();
        
        const formData = new FormData(event.target);
        const categoria = formData.get('categoria');
        const id = parseInt(formData.get('id'));
        
        // Simular actualización (en un caso real, se haría una petición al servidor)
        const registros = registrosEjemplo[categoria];
        const index = registros.findIndex(r => r.id === id);
        
        if (index !== -1) {
            // Actualizar registro
            const campos = Object.keys(camposPorTipo[categoria]);
            campos.forEach(campo => {
                const valor = formData.get(campo);
                if (camposPorTipo[categoria][campo] === 'boolean') {
                    registros[index][campo] = valor === 'true';
                } else if (camposPorTipo[categoria][campo] === 'number') {
                    registros[index][campo] = parseInt(valor) || 0;
                } else {
                    registros[index][campo] = valor || '';
                }
            });
            
            mostrarMensaje('Registro actualizado correctamente', 'success');
            cancelarEdicion();
            generarTabla(categoria, registros);
        }
    }

    function cancelarEdicion() {
        document.getElementById('formulario-edicion').classList.add('hidden');
        document.getElementById('tabla-registros').classList.remove('hidden');
    }

    // Función para mostrar mensajes de éxito/error (mejorada)
    function mostrarMensaje(texto, tipo) {
        // Crear elemento de mensaje si no existe
        let mensaje = document.getElementById('mensaje-dinamico');
        if (!mensaje) {
            mensaje = document.createElement('div');
            mensaje.id = 'mensaje-dinamico';
            mensaje.style.position = 'fixed';
            mensaje.style.top = '100px';
            mensaje.style.right = '20px';
            mensaje.style.zIndex = '1000';
            mensaje.style.padding = '12px 20px';
            mensaje.style.borderRadius = '8px';
            mensaje.style.fontWeight = '500';
            mensaje.style.boxShadow = '0 4px 12px rgba(0,0,0,0.15)';
            document.body.appendChild(mensaje);
        }
        
        // Configurar mensaje según tipo
        if (tipo === 'error') {
            mensaje.className = 'bg-red-500 text-white border border-red-600';
            mensaje.innerHTML = `<i class="fas fa-exclamation-triangle mr-2"></i>${texto}`;
        } else {
            mensaje.className = 'bg-green-500 text-white border border-green-600';
            mensaje.innerHTML = `<i class="fas fa-check-circle mr-2"></i>${texto}`;
        }
        
        mensaje.style.display = 'block';
        
        // Ocultar después de 4 segundos
        setTimeout(() => {
            mensaje.style.display = 'none';
        }, 4000);
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        const formEdicion = document.getElementById('form-edicion');
        if (formEdicion) {
            formEdicion.addEventListener('submit', function(e) {
                e.preventDefault();
                // Enviar formulario
                this.submit();
            });
        }
    });
</script>

</body>
</html>

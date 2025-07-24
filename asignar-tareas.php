<?php
session_start();

// Incluir la conexión a la base de datos
include_once 'conexion.php'; // Tu archivo de conexión

// Verificar que el usuario esté logueado y sea Director
if (!isset($_SESSION['usuario_nombre'])) {
    header('Location: index.php');
    exit();
}

// Verificar que el rol sea Director
if (trim($_SESSION['usuario_rol']) !== 'Director') {
    header('Location: dashboard.php');
    exit();
}

// Obtener el ID del director desde la base de datos usando el email de la sesión
$stmt = $conn->prepare("SELECT id FROM usuario WHERE email = ?");
$stmt->execute([$_SESSION['usuario_email']]);
$director_data = $stmt->fetch(PDO::FETCH_ASSOC);
$director_id = $director_data['id'];

// Establecer la variable de sesión para compatibilidad
$_SESSION['usuario_id'] = $director_id;

// Incluir la clase ConfiguracionPOA
class ConfiguracionPOA {
    private $conn;
    
    // Array con todas las categorías disponibles del POA
    private $categorias_poa = [
        'ppa' => 'Proyecto Pedagógico de Aula',
        'visita_regional' => 'Visitas Regionales',
        'visita_nacional' => 'Visitas Nacionales', 
        'charlas' => 'Charlas y Conferencias',
        'feria' => 'Participación en Ferias',
        'congreso' => 'Participación en Congresos',
        'evento_nacional' => 'Eventos Nacionales',
        'biblio' => 'Bibliografía en Inglés',
        'profesor_visitante' => 'Profesores Visitantes',
        'actividades_interculturales' => 'Actividades Interculturales',
        'actividades_universidades_internacionales' => 'Actividades con Universidades Internacionales',
        'productos_en_ingles' => 'Productos en Inglés',
        'ppa_traducidos' => 'PPA Traducidos',
        'coil' => 'COIL',
        'clase_espejo' => 'Clases Espejo',
        'reto_empresa' => 'Retos con Empresas',
        'grupo_focal' => 'Grupos Focales',
        'estudio_tendencias' => 'Estudios de Tendencias',
        'analisis_contexto' => 'Análisis de Contexto',
        'autoevaluacion_mejoras' => 'Autoevaluación y Mejoras',
        'empresas_practicas' => 'Empresas para Prácticas',
        'graduados' => 'Actividades con Graduados',
        'mesa_sector' => 'Mesa del Sector',
        'mejoras_practicas' => 'Mejoras en Prácticas',
        'diplomado_grado' => 'Diplomados de Grado',
        'formacion_continua' => 'Formación Continua',
        'mbc_refuerzos' => 'Refuerzos MBC',
        'taller_refuerzo_saber' => 'Talleres de Refuerzo Saber',
        'proyecto_grado' => 'Proyectos de Grado',
        'visita_aula' => 'Visitas de Aula',
        'estudio_semilleros_estudiantes' => 'Semilleros - Estudiantes',
        'estudio_semilleros_docentes' => 'Semilleros - Docentes',
        'pep' => 'Proyecto Educativo del Programa',
        'herramientas' => 'Herramientas Tecnológicas',
        'micrositio' => 'Actualización Micrositio',
        'modalidad_virtual' => 'Modalidad Virtual',
        'sitio_interaccion' => 'Sitios de Interacción',
        'atlas' => 'Registro en Atlas',
        'seguimiento_estudiantes' => 'Seguimiento a Estudiantes',
        'matricula_estudiantes_antiguos' => 'Matrícula Estudiantes Antiguos'
    ];
    
    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function inicializarDocenteCompleto($docente_id, $director_id) {
        try {
            $this->conn->beginTransaction();
            
            $stmt = $this->conn->prepare("
                INSERT INTO configuracion_poa (docente_id, nombre_categoria, director_id, activo) 
                VALUES (?, ?, ?, FALSE) 
                ON CONFLICT (docente_id, nombre_categoria) DO NOTHING
            ");
            
            foreach (array_keys($this->categorias_poa) as $categoria) {
                $stmt->execute([$docente_id, $categoria, $director_id]);
            }
            
            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            throw new Exception("Error al inicializar docente: " . $e->getMessage());
        }
    }

    public function getMallaCompletaDocente($docente_id) {
        $stmt = $this->conn->prepare("
            SELECT 
                c.id,
                c.nombre_categoria,
                c.activo,
                c.fecha_modificacion,
                u.nombre as nombre_docente,
                u.email as email_docente
            FROM configuracion_poa c
            JOIN usuario u ON c.docente_id = u.id
            WHERE c.docente_id = ?
            ORDER BY c.nombre_categoria
        ");
        
        $stmt->execute([$docente_id]);
        $configuraciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Si no existe configuración, crearla
        if (empty($configuraciones)) {
            $director_id = $_SESSION['usuario_id'];
            $this->inicializarDocenteCompleto($docente_id, $director_id);
            return $this->getMallaCompletaDocente($docente_id);
        }

        // Agregar descripción de cada categoría
        foreach ($configuraciones as &$config) {
            $config['descripcion_categoria'] = $this->categorias_poa[$config['nombre_categoria']] ?? 'Sin descripción';
        }
        
        return $configuraciones;
    }

    public function actualizarEstadoCategoria($docente_id, $categoria, $activo) {
        // Convertir explícitamente a booleano para PostgreSQL
        $activo_bool = $activo ? 'TRUE' : 'FALSE';
        
        $stmt = $this->conn->prepare("
            UPDATE configuracion_poa 
            SET activo = $activo_bool, fecha_modificacion = CURRENT_TIMESTAMP 
            WHERE docente_id = ? AND nombre_categoria = ?
        ");
        
        return $stmt->execute([$docente_id, $categoria]);
    }

    public function getTodasLasCategorias() {
        return $this->categorias_poa;
    }

    public function getDocentes() {
        $stmt = $this->conn->query("
            SELECT id, nombre, email 
            FROM usuario 
            WHERE rol = 'Docente' 
            ORDER BY nombre
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// Instanciar la clase
$configuracion = new ConfiguracionPOA($conn);

// Procesar el formulario
$mensaje = '';
$tipo_mensaje = '';

if ($_POST && isset($_POST['docente_id'])) {
    try {
        $docente_id = $_POST['docente_id'];
        
        // Actualizar cada categoría
        foreach (array_keys($configuracion->getTodasLasCategorias()) as $categoria) {
            // Asegurar que el valor sea booleano
            $activo = isset($_POST["categoria_$categoria"]) && $_POST["categoria_$categoria"] == '1';
            $configuracion->actualizarEstadoCategoria($docente_id, $categoria, $activo);
        }
        
        $mensaje = 'Configuración actualizada exitosamente';
        $tipo_mensaje = 'success';
    } catch (Exception $e) {
        $mensaje = 'Error al actualizar la configuración: ' . $e->getMessage();
        $tipo_mensaje = 'error';
    }
}

// Obtener lista de docentes
$docentes = $configuracion->getDocentes();

// Obtener configuración del docente seleccionado
$malla_completa = [];
$docente_seleccionado = null;
if (isset($_GET['docente_id']) || isset($_POST['docente_id'])) {
    $docente_id = $_GET['docente_id'] ?? $_POST['docente_id'];
    $malla_completa = $configuracion->getMallaCompletaDocente($docente_id);
    if (!empty($malla_completa)) {
        $docente_seleccionado = [
            'id' => $docente_id,
            'nombre' => $malla_completa[0]['nombre_docente'],
            'email' => $malla_completa[0]['email_docente']
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asignar Tareas POA - Director</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="icon" type="image/png" href="resource/fesc.png">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'azul1': '#3b8cde',
                        'azul2': '#195da2',
                        'azul3': '#406e9b',
                        'azul4': '#07396b',
                        'azul5': '#85b7e9',
                        'gris-claro': '#f8fafc',
                        'gris-medio': '#e2e8f0',
                        'gris-oscuro': '#64748b'
                    }
                }
            }
        }
    </script>
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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gris-claro min-h-screen">
    <!-- Header -->
    <header class="gradient-bg text-white p-4 shadow-lg fixed top-0 left-0 right-0 z-50 header-height flex items-center">
        <div class="flex items-center justify-between w-full">
            <div class="flex items-center space-x-3">
                <i class="fas fa-graduation-cap text-2xl"></i>
                <h1 class="text-2xl font-bold">SystemPOA</h1>
            </div>
            <div class="flex items-center space-x-4">
                <span class="text-sm opacity-90">¡Bienvenido, <?php echo htmlspecialchars($_SESSION['usuario_nombre'] ?? 'Usuario'); ?>!</span>
                <a href="dashboard.php" class="bg-white bg-opacity-20 px-4 py-2 rounded-lg hover:bg-opacity-30 transition">
                    <i class="fas fa-arrow-left mr-2"></i>Dashboard
                </a>
            </div>
        </div>
    </header>

     <div class="main-container overflow-y-auto">
        <div class="container mx-auto px-6 py-8">
            <!-- Mensajes -->
            <?php if ($mensaje): ?>
            <div class="mb-6 p-4 rounded-lg card-shadow <?php echo $tipo_mensaje === 'success' ? 'bg-green-100 text-green-800 border border-green-300' : 'bg-red-100 text-red-800 border border-red-300'; ?>">
                <div class="flex items-center">
                    <i class="fas <?php echo $tipo_mensaje === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle'; ?> mr-2"></i>
                    <?php echo htmlspecialchars($mensaje); ?>
                </div>
            </div>
            <?php endif; ?>
         <!-- Selector de Docente -->
            <div class="bg-white rounded-xl card-shadow p-6 mb-8 hover-scale form-container">
                <h2 class="text-xl font-bold text-azul4 mb-4 flex items-center">
                    <i class="fas fa-user-tie mr-3 text-azul2"></i>
                    Seleccionar Docente
                </h2>
                
                <form method="GET" class="flex items-center space-x-4">
                    <div class="flex-1">
                        <select name="docente_id" onchange="this.form.submit()" 
                                class="w-full px-4 py-3 border border-gris-medio rounded-lg focus:ring-2 focus:ring-azul2 focus:border-azul2 bg-white text-gray-800 input-focus">
                            <option value="">Seleccione un docente...</option>
                            <?php foreach ($docentes as $docente): ?>
                            <option value="<?php echo $docente['id']; ?>" 
                                    <?php echo (isset($_GET['docente_id']) && $_GET['docente_id'] == $docente['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($docente['nombre'] . ' - ' . $docente['email']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="bg-azul2 hover:bg-azul3 text-white px-6 py-3 rounded-lg transition-colors hover-scale">
                        <i class="fas fa-search mr-2"></i>Buscar
                    </button>
                </form>
            </div>

        <!-- Configuración de Categorías -->
        <?php if ($docente_seleccionado && !empty($malla_completa)): ?>
        <div class="bg-white rounded-xl card-shadow p-6 hover-scale">
                <div class="mb-6">
                    <h2 class="text-xl font-bold text-azul4 flex items-center">
                        <i class="fas fa-clipboard-list mr-3 text-azul2"></i>
                        Configurar Tareas POA para: <?php echo htmlspecialchars($docente_seleccionado['nombre']); ?>
                    </h2>
                    <p class="text-gris-oscuro mt-2">
                        <i class="fas fa-envelope mr-2"></i>
                        <?php echo htmlspecialchars($docente_seleccionado['email']); ?>
                    </p>
                </div>

            <form method="POST" id="configuracionForm">
                <input type="hidden" name="docente_id" value="<?php echo $docente_seleccionado['id']; ?>">
                
                <!-- Controles superiores -->
                <div class="mb-6 flex flex-wrap items-center justify-between gap-4 p-4 form-container rounded-lg card-shadow">
                    <div class="flex items-center space-x-4">
                        <button type="button" onclick="seleccionarTodos()" 
                                class="bg-azul1 hover:bg-azul2 text-white px-4 py-2 rounded-lg transition-colors hover-scale">
                            <i class="fas fa-check-double mr-2"></i>Seleccionar Todos
                        </button>
                        <button type="button" onclick="deseleccionarTodos()" 
                                class="bg-gris-oscuro hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors hover-scale">
                            <i class="fas fa-times mr-2"></i>Deseleccionar Todos
                        </button>
                    </div>
                    <div class="text-sm text-gris-oscuro font-semibold">
                        <span id="contador">0</span> de <?php echo count($malla_completa); ?> categorías seleccionadas
                    </div>
                </div>

                <!-- Grid de Categorías -->
                 <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
                    <?php foreach ($malla_completa as $categoria): ?>
                    <div class="categoria-item bg-white border border-gris-medio rounded-lg p-4 card-shadow hover-scale transition-all duration-200">
                        <label class="flex items-start space-x-3 cursor-pointer group">
                            <input type="checkbox" 
                                    name="categoria_<?php echo $categoria['nombre_categoria']; ?>" 
                                    value="1"
                                    class="categoria-checkbox mt-1 w-5 h-5 accent-azul2 bg-white border-2 border-gris-medio rounded focus:ring-azul2 focus:ring-2"
                                    <?php echo $categoria['activo'] ? 'checked' : ''; ?>
                                    onchange="actualizarContador()">

                            <div class="flex-1">
                                <div class="font-semibold text-azul4 group-hover:text-azul2 transition-colors">
                                    <?php echo htmlspecialchars($categoria['descripcion_categoria']); ?>
                                </div>
                                <?php if ($categoria['fecha_modificacion']): ?>
                                <div class="text-xs text-gris-oscuro mt-1">
                                    <i class="fas fa-clock mr-1"></i>
                                    Modificado: <?php echo date('d/m/Y H:i', strtotime($categoria['fecha_modificacion'])); ?>
                                </div>
                                <?php endif; ?>
                                <div class="mt-2">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $categoria['activo'] ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'; ?>">
                                        <i class="fas <?php echo $categoria['activo'] ? 'fa-check-circle' : 'fa-circle'; ?> mr-1"></i>
                                        <?php echo $categoria['activo'] ? 'Activa' : 'Inactiva'; ?>
                                    </span>
                                </div>
                            </div>
                        </label>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Botón de Guardar -->
                <div class="flex justify-center">
                    <button type="submit" 
                            class="gradient-bg text-white px-8 py-3 rounded-lg font-semibold card-shadow hover-scale transition-all duration-200">
                        <i class="fas fa-save mr-2"></i>
                        Guardar Configuración
                    </button>
                </div>
            </form>
        </div>
        <?php elseif (isset($_GET['docente_id'])): ?>
        <div class="bg-white rounded-xl card-shadow p-8 text-center hover-scale">
            <div class="text-6xl text-gris-medio mb-4">
                <i class="fas fa-user-slash"></i>
            </div>
            <h3 class="text-xl font-semibold text-gris-oscuro mb-2">Docente no encontrado</h3>
            <p class="text-gris-oscuro">El docente seleccionado no existe o no está disponible.</p>
        </div>
        <?php else: ?>
        <div class="bg-white rounded-xl card-shadow p-8 text-center hover-scale">
            <div class="text-6xl text-azul5 mb-4">
                <i class="fas fa-arrow-up"></i>
            </div>
            <h3 class="text-xl font-semibold text-azul4 mb-2">Seleccione un Docente</h3>
            <p class="text-gris-oscuro">Por favor, seleccione un docente del desplegable superior para configurar sus tareas POA.</p>
        </div>
        <?php endif; ?>
    </div>

    <script>
        function seleccionarTodos() {
            const checkboxes = document.querySelectorAll('.categoria-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = true;
            });
            actualizarContador();
        }

        function deseleccionarTodos() {
            const checkboxes = document.querySelectorAll('.categoria-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = false;
            });
            actualizarContador();
        }

        function actualizarContador() {
            const checkboxes = document.querySelectorAll('.categoria-checkbox');
            const seleccionados = document.querySelectorAll('.categoria-checkbox:checked');
            document.getElementById('contador').textContent = seleccionados.length;
        }

        // Inicializar contador al cargar la página
        document.addEventListener('DOMContentLoaded', function() {
            actualizarContador();
        });
    </script>
</body>
</html>

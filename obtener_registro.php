<?php
// Incluir la conexión a la base de datos
require_once 'conexion.php';

// Verificar que se recibieron los parámetros necesarios
if (!isset($_GET['tabla']) || empty($_GET['tabla']) || !isset($_GET['id']) || empty($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Parámetros insuficientes']);
    exit;
}

$tabla = $_GET['tabla'];
$id = intval($_GET['id']);

// Lista de tablas permitidas para seguridad
$tablasPermitidas = [
    'ppa', 'visita_regional', 'visita_nacional', 'charlas', 'feria', 'congreso',
    'evento_nacional', 'biblio', 'profesor_visitante', 'actividades_interculturales',
    'actividades_universidades_internacionales', 'productos_en_ingles', 'ppa_traducidos',
    'coil', 'clase_espejo', 'reto_empresa', 'grupo_focal', 'estudio_tendencias',
    'analisis_contexto', 'autoevaluacion_mejoras', 'empresas_practicas', 'graduados',
    'mesa_sector', 'mejoras_practicas', 'diplomado_grado', 'formacion_continua',
    'mbc_refuerzos', 'taller_refuerzo_saber', 'proyecto_grado', 'visita_aula',
    'estudio_semilleros_estudiantes', 'estudio_semilleros_docentes', 'pep',
    'herramientas', 'micrositio', 'modalidad_virtual', 'sitio_interaccion',
    'atlas', 'seguimiento_estudiantes', 'matricula_estudiantes_antiguos'
];

// Verificar que la tabla esté en la lista permitida
if (!in_array($tabla, $tablasPermitidas)) {
    http_response_code(400);
    echo json_encode(['error' => 'Tabla no permitida']);
    exit;
}

// Establecer el tipo de contenido
header('Content-Type: application/json; charset=utf-8');

try {
    // Obtener el registro específico
    $sql = "SELECT * FROM $tabla WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$id]);
    $registro = $stmt->fetch();
    
    if (!$registro) {
        http_response_code(404);
        echo json_encode(['error' => 'Registro no encontrado']);
        exit;
    }
    
    // Convertir valores booleanos para mejor manejo en JSON
    foreach ($registro as $key => &$valor) {
        if (is_bool($valor)) {
            $valor = $valor ? true : false;
        }
    }
    
    echo json_encode($registro);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al obtener el registro: ' . $e->getMessage()]);
}
?>
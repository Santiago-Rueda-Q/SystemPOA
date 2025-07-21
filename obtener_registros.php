<?php
// Incluir la conexión a la base de datos
require_once 'conexion.php';

// Verificar que se recibió el parámetro tabla
if (!isset($_GET['tabla']) || empty($_GET['tabla'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Tabla no especificada']);
    exit;
}

$tabla = $_GET['tabla'];

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
    // Obtener registros de la tabla especificada
    $sql = "SELECT * FROM $tabla ORDER BY id DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $registros = $stmt->fetchAll();
    
    // Convertir valores booleanos para mejor manejo en JSON
    foreach ($registros as &$registro) {
        foreach ($registro as $key => &$valor) {
            if (is_bool($valor)) {
                $valor = $valor ? true : false;
            }
        }
    }
    
    echo json_encode($registros);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al obtener registros: ' . $e->getMessage()]);
}
?>
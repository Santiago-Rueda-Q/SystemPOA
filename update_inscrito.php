<?php
session_start();
header('Content-Type: application/json');

// Verificar si el usuario está logueado y es Director
if (!isset($_SESSION['usuario_nombre']) || $_SESSION['usuario_rol'] !== 'Director') {
    echo json_encode([
        'success' => false,
        'message' => 'No tienes permisos para realizar esta acción'
    ]);
    exit;
}

// Verificar que sea una petición POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Método no permitido'
    ]);
    exit;
}

// Obtener y validar datos - Reemplazar FILTER_SANITIZE_STRING deprecated
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$cantidad = filter_input(INPUT_POST, 'cantidad', FILTER_VALIDATE_INT);
$tipo = isset($_POST['tipo']) ? trim($_POST['tipo']) : '';

if ($id === false || $cantidad === false || $cantidad < 0 || empty($tipo)) {
    echo json_encode([
        'success' => false,
        'message' => 'Datos inválidos. La cantidad debe ser un número positivo.'
    ]);
    exit;
}

try {
    // Conexión a la base de datos - usando tu archivo conexion.php
    require_once 'conexion.php';
    
    // Preparar y ejecutar la consulta de actualización
    $sql = "UPDATE public.total_inscritos SET cantidad = :cantidad WHERE id = :id";
    $stmt = $conn->prepare($sql);
    
    $stmt->bindParam(':cantidad', $cantidad, PDO::PARAM_INT);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    
    if ($stmt->execute()) {
        // Verificar que se actualizó al menos una fila
        if ($stmt->rowCount() > 0) {
            // Obtener los datos actualizados para recalcular totales
            $sqlSelect = "SELECT id, nivel_formacion, cantidad FROM public.total_inscritos ORDER BY nivel_formacion";
            $stmtSelect = $conn->prepare($sqlSelect);
            $stmtSelect->execute();
            $datosActualizados = $stmtSelect->fetchAll(PDO::FETCH_ASSOC);
            
            // Calcular totales actualizados
            $totales = [
                'total' => 0,
                'tecnico' => 0,
                'tecnologia' => 0,
                'profesional' => 0
            ];

            foreach ($datosActualizados as $dato) {
                $cantidadDato = (int)$dato['cantidad'];
                $totales['total'] += $cantidadDato;
                
                $nivel = trim($dato['nivel_formacion']);
                // Mapear basado en el nombre exacto de la base de datos
                switch ($nivel) {
                    case 'Técnico':
                        $totales['tecnico'] = $cantidadDato;
                        break;
                    case 'Tecnología':
                        $totales['tecnologia'] = $cantidadDato;
                        break;
                    case 'Profesional':
                        $totales['profesional'] = $cantidadDato;
                        break;
                }
            }
            
            // Log de la acción (opcional)
            error_log("Usuario {$_SESSION['usuario_nombre']} actualizó inscrito ID: $id, nueva cantidad: $cantidad");
            
            echo json_encode([
                'success' => true,
                'message' => 'Cantidad actualizada correctamente',
                'data' => [
                    'id' => $id,
                    'cantidad' => $cantidad,
                    'tipo' => $tipo,
                    'totales' => $totales
                ]
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'No se encontró el registro para actualizar'
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Error al ejecutar la consulta'
        ]);
    }
    
} catch (PDOException $e) {
    // Log del error para debugging
    error_log("Error en update_inscrito.php: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => 'Error de base de datos: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    // Log del error general
    error_log("Error general en update_inscrito.php: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => 'Error interno del servidor: ' . $e->getMessage()
    ]);
}
?>
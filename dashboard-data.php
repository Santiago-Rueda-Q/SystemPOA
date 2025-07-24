<?php
require_once 'conexion.php';

header('Content-Type: application/json');
header('Cache-Control: no-cache');

try {
    // Inicializar contadores
    $total_actividades = 0;
    $actividades_completadas = 0;
    $por_nivel = ['Técnico' => 0, 'Tecnología' => 0, 'Profesional' => 0];
    $por_componente = ['1' => 0, '2' => 0, '3' => 0, '4' => 0, '5' => 0];
    $por_categoria = [
        'ppa' => 0,
        'visita_regional' => 0,
        'charlas' => 0,
        'proyecto_grado' => 0,
        'otros' => 0
    ];
    // pruebas 
 $internacionalizacion  = [
        'ppa' => 0,
        'visita_regional' => 0,
        'charlas' => 0,
        'proyecto_grado' => 0,
        'otros' => 0
    ];

     $stmt = $conn->query("SELECT nivel_formacion, terminado FROM ppa");
    while ($row = $stmt->fetch()) {
        $total_actividades++;
        $internacionalizacion ['ppa']++;
        $por_componente['1']++;

        $nivel = $row['nivel_formacion'];
        if (isset($por_nivel[$nivel])) {
            $por_nivel[$nivel]++;
        }

        if (strtoupper($row['terminado']) === 'SI' || $row['terminado'] == '1') {
            $actividades_completadas++;
        }
    }

    // Visita regional
    $stmt = $conn->query("SELECT realizado FROM visita_regional");
    while ($row = $stmt->fetch()) {
        $total_actividades++;
        $por_categoria['visita_regional']++;
        $por_componente['1']++;

        if (strtoupper($row['realizado']) === 'SI' || $row['realizado'] == '1') {
            $actividades_completadas++;
        }
    }

    // Charlas
    $stmt = $conn->query("SELECT realizada FROM charlas");
    while ($row = $stmt->fetch()) {
        $total_actividades++;
        $por_categoria['charlas']++;
        $por_componente['1']++;

        if (strtoupper($row['realizada']) === 'SI' || $row['realizada'] == '1') {
            $actividades_completadas++;
        }
    }

    // Proyecto grado
    $stmt = $conn->query("SELECT propuesta FROM proyecto_grado");
    while ($row = $stmt->fetch()) {
        $total_actividades++;
        $por_categoria['proyecto_grado']++;
        $por_componente['4']++;

        if (!empty($row['propuesta'])) {
            $actividades_completadas++;
        }
    }


    // PPA - nivel + estado
    $stmt = $conn->query("SELECT nivel_formacion, terminado FROM ppa");
    while ($row = $stmt->fetch()) {
        $total_actividades++;
        $por_categoria['ppa']++;
        $por_componente['1']++;

        $nivel = $row['nivel_formacion'];
        if (isset($por_nivel[$nivel])) {
            $por_nivel[$nivel]++;
        }

        if (strtoupper($row['terminado']) === 'SI' || $row['terminado'] == '1') {
            $actividades_completadas++;
        }
    }

    // Visita regional
    $stmt = $conn->query("SELECT realizado FROM visita_regional");
    while ($row = $stmt->fetch()) {
        $total_actividades++;
        $por_categoria['visita_regional']++;
        $por_componente['1']++;

        if (strtoupper($row['realizado']) === 'SI' || $row['realizado'] == '1') {
            $actividades_completadas++;
        }
    }

    // Charlas
    $stmt = $conn->query("SELECT realizada FROM charlas");
    while ($row = $stmt->fetch()) {
        $total_actividades++;
        $por_categoria['charlas']++;
        $por_componente['1']++;

        if (strtoupper($row['realizada']) === 'SI' || $row['realizada'] == '1') {
            $actividades_completadas++;
        }
    }

    // Proyecto grado
    $stmt = $conn->query("SELECT propuesta FROM proyecto_grado");
    while ($row = $stmt->fetch()) {
        $total_actividades++;
        $por_categoria['proyecto_grado']++;
        $por_componente['4']++;

        if (!empty($row['propuesta'])) {
            $actividades_completadas++;
        }
    }






    
    // Respuesta final
    echo json_encode([
        'total_actividades' => $total_actividades,
        'actividades_completadas' => $actividades_completadas,
        'por_nivel' => $por_nivel,
        'por_componente' => $por_componente,
        'por_categoria' => $por_categoria
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Error al consultar la base de datos: ' . $e->getMessage(),
        'total_actividades' => 0,
        'actividades_completadas' => 0,
        'por_nivel' => [
            'Técnico' => 0,
            'Tecnología' => 0,
            'Profesional' => 0
        ],
        'por_componente' => [
            '1' => 0,
            '2' => 0,
            '3' => 0,
            '4' => 0,
            '5' => 0
        ],
        'por_categoria' => [
            'ppa' => 0,
            'visita_regional' => 0,
            'charlas' => 0,
            'proyecto_grado' => 0,
            'otros' => 0
        ], // ← esta coma era necesaria
        // pruebas
        'internacionalizacion ' => [
            'ppa' => 0,
            'visita_regional' => 0,
            'charlas' => 0,
            'proyecto_grado' => 0,
            'otros' => 0
        ]
    ]);
}


?>

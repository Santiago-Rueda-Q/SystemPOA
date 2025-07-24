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

// Función para generar PDF usando mPDF (alternativa más simple)
if (isset($_GET['action']) && $_GET['action'] === 'download_pdf') {
    $categoria = $_GET['categoria'] ?? '';
    
    if (!empty($categoria)) {
        try {
            $sql = "SELECT * FROM $categoria ORDER BY id DESC";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Generar HTML para el PDF
            $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Reporte POA</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { color: #07396b; font-size: 18px; margin-bottom: 10px; }
        .info { margin-bottom: 20px; }
        .info p { margin: 5px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .no-data { text-align: center; color: #666; padding: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>REPORTE POA - ' . strtoupper(str_replace('_', ' ', $categoria)) . '</h1>
    </div>
    
    <div class="info">
        <p><strong>Generado por:</strong> ' . htmlspecialchars($nombre_usuario) . '</p>
        <p><strong>Fecha:</strong> ' . date('d/m/Y H:i:s') . '</p>
        <p><strong>Total de registros:</strong> ' . count($registros) . '</p>
    </div>';
            
            if (!empty($registros)) {
                $html .= '<table>';
                
                // Encabezados
                $columnas = array_keys($registros[0]);
                $html .= '<thead><tr>';
                foreach ($columnas as $columna) {
                    $html .= '<th>' . strtoupper(str_replace('_', ' ', $columna)) . '</th>';
                }
                $html .= '</tr></thead><tbody>';
                
                // Datos
                foreach ($registros as $registro) {
                    $html .= '<tr>';
                    foreach ($columnas as $columna) {
                        $valor = $registro[$columna];
                        
                        // Formatear valores booleanos
                        if ($valor === '1' || $valor === 1) {
                            $valor = 'SI';
                        } elseif ($valor === '0' || $valor === 0) {
                            $valor = 'NO';
                        } elseif (empty($valor)) {
                            $valor = '-';
                        }
                        
                        // Limpiar y truncar texto
                        $valor = htmlspecialchars($valor);
                        if (strlen($valor) > 30) {
                            $valor = substr($valor, 0, 30) . '...';
                        }
                        
                        $html .= '<td>' . $valor . '</td>';
                    }
                    $html .= '</tr>';
                }
                $html .= '</tbody></table>';
            } else {
                $html .= '<div class="no-data">No hay registros para mostrar</div>';
            }
            
            $html .= '</body></html>';
            
            // Configurar headers para descarga de PDF
            $filename = 'reporte_poa_' . $categoria . '_' . date('Y-m-d_H-i-s') . '.pdf';
            
            // Usar DomPDF o generar HTML si no hay librería PDF
            if (class_exists('Dompdf\Dompdf')) {
                // Si tienes DomPDF instalado
                $dompdf = new Dompdf\Dompdf();
                $dompdf->loadHtml($html);
                $dompdf->setPaper('A4', 'landscape');
                $dompdf->render();
                $dompdf->stream($filename);
            } else {
                // Alternativa: descargar como HTML que se puede imprimir como PDF
                header('Content-Type: text/html; charset=utf-8');
                header('Content-Disposition: attachment; filename="' . str_replace('.pdf', '.html', $filename) . '"');
                echo $html;
            }
            exit;
            
        } catch (PDOException $e) {
            echo "Error al generar reporte: " . $e->getMessage();
            exit;
        }
    }
}

// Obtener datos para reportes si se solicita
if (isset($_GET['action']) && $_GET['action'] === 'get_records') {
    $categoria = $_GET['categoria'] ?? '';
    
    if (!empty($categoria)) {
        header('Content-Type: application/json');
        
        try {
            $sql = "SELECT * FROM $categoria ORDER BY id DESC LIMIT 100";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
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
        echo json_encode(['success' => false, 'error' => 'Categoría no especificada']);
    }
    exit;
}

// Definir las categorías disponibles
$categorias = [
    "ppa" => "PPA",
    "visita_regional" => "Visitas Regionales", 
    "visita_nacional" => "Visitas Nacionales",
    "charlas" => "Charlas",
    "feria" => "Ferias",
    "congreso" => "Congresos",
    "evento_nacional" => "Eventos Nacionales",
    "biblio" => "Bibliografía",
    "profesor_visitante" => "Profesores Visitantes",
    "actividades_interculturales" => "Actividades Interculturales",
    "actividades_universidades_internacionales" => "Actividades Universidades Internacionales",
    "productos_en_ingles" => "Productos en Inglés",
    "ppa_traducidos" => "PPA Traducidos",
    "coil" => "COIL",
    "clase_espejo" => "Clases Espejo",
    "reto_empresa" => "Retos Empresariales",
    "grupo_focal" => "Grupos Focales",
    "estudio_tendencias" => "Estudios de Tendencias",
    "analisis_contexto" => "Análisis de Contexto",
    "autoevaluacion_mejoras" => "Autoevaluación y Mejoras",
    "empresas_practicas" => "Empresas para Prácticas",
    "graduados" => "Graduados",
    "mesa_sector" => "Mesa Sectorial",
    "mejoras_practicas" => "Mejoras en Prácticas",
    "diplomado_grado" => "Diplomados de Grado",
    "formacion_continua" => "Formación Continua",
    "mbc_refuerzos" => "MBC Refuerzos",
    "taller_refuerzo_saber" => "Talleres de Refuerzo SABER",
    "proyecto_grado" => "Proyectos de Grado",
    "visita_aula" => "Visitas de Aula",
    "estudio_semilleros_estudiantes" => "Semilleros de Investigación - Estudiantes",
    "estudio_semilleros_docentes" => "Semilleros de Investigación - Docentes",
    "pep" => "PEP",
    "herramientas" => "Herramientas",
    "micrositio" => "Micrositio",
    "modalidad_virtual" => "Modalidad Virtual",
    "sitio_interaccion" => "Sitios de Interacción",
    "atlas" => "Atlas",
    "seguimiento_estudiantes" => "Seguimiento de Estudiantes",
    "matricula_estudiantes_antiguos" => "Matrícula de Estudiantes Antiguos"
];

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SystemPOA - Reportes</title>
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
        .btn-download {
            background: linear-gradient(135deg, #059669 0%, #10b981 100%);
        }
        .btn-download:hover {
            background: linear-gradient(135deg, #047857 0%, #059669 100%);
        }
        .stats-card {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
        }
    </style>
</head>
<body class="bg-gray-100">
    <!-- Header -->
    <div class="gradient-bg text-white p-4 shadow-lg fixed top-0 left-0 right-0 z-50 header-height flex items-center">
        <div class="flex items-center justify-between w-full">
            <div class="flex items-center space-x-3">
                <i class="fas fa-chart-bar text-2xl"></i>
                <h1 class="text-2xl font-bold">SystemPOA - Reportes</h1>
            </div>
            <div class="flex items-center space-x-4">
                <span class="text-sm opacity-90">¡Bienvenido, <?php echo htmlspecialchars($nombre_usuario); ?>!</span>
                <a href="dashboard.php" class="bg-white bg-opacity-20 px-4 py-2 rounded-lg hover:bg-opacity-30 transition">
                    <i class="fas fa-arrow-left mr-2"></i>Regresar al Dashboard
                </a>
            </div>
        </div>
    </div>

    <div class="flex main-container">
        <!-- Sidebar -->
        <aside class="w-80 gradient-bg text-white shadow-xl overflow-y-auto">
            <div class="p-6">
                <h2 class="text-xl font-bold mb-6 flex items-center">
                    <i class="fas fa-file-pdf mr-3"></i>
                    Generar Reportes
                </h2>
                
                <ul class="space-y-2" id="categorias-reportes">
                    <?php foreach ($categorias as $key => $nombre): ?>
                    <li class="category-item rounded-lg cursor-pointer p-3 text-sm font-medium"
                        onclick="mostrarReporte('<?= $key ?>')">
                        <i class="fas fa-chevron-right mr-2"></i>
                        <?= $nombre ?>
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
                            Reportes POA
                        </h1>
                        <p class="text-gray-600">Genera y descarga reportes de las diferentes categorías POA</p>
                    </div>

                    <!-- Estadísticas generales -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8" id="estadisticas-generales">
                        <div class="stats-card text-white p-6 rounded-xl card-shadow">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-white text-opacity-80 text-sm">Total Categorías</p>
                                    <p class="text-2xl font-bold"><?= count($categorias) ?></p>
                                </div>
                                <i class="fas fa-layer-group text-3xl text-white text-opacity-60"></i>
                            </div>
                        </div>
                        
                        <div class="bg-blue-500 text-white p-6 rounded-xl card-shadow">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-white text-opacity-80 text-sm">Reportes Generados</p>
                                    <p class="text-2xl font-bold" id="reportes-generados">0</p>
                                </div>
                                <i class="fas fa-file-alt text-3xl text-white text-opacity-60"></i>
                            </div>
                        </div>
                        
                        <div class="bg-green-500 text-white p-6 rounded-xl card-shadow">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-white text-opacity-80 text-sm">Último Reporte</p>
                                    <p class="text-sm font-bold" id="ultimo-reporte">Ninguno</p>
                                </div>
                                <i class="fas fa-clock text-3xl text-white text-opacity-60"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Tabla de registros con botón de descarga -->
                    <div class="hidden bg-white rounded-2xl shadow-xl card-shadow" id="tabla-reportes">
                        <div class="p-6">
                            <div class="flex justify-between items-center mb-6">
                                <h2 class="text-xl font-bold text-gray-800" id="reporte-titulo"></h2>
                                <div class="flex items-center space-x-4">
                                    <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm font-medium" id="contador-registros-reporte"></span>
                                    <button onclick="descargarPDF()" 
                                            class="btn-download text-white px-6 py-3 rounded-lg hover-scale transition font-medium" 
                                            id="btn-descargar">
                                        <i class="fas fa-download mr-2"></i>Descargar Reporte
                                    </button>
                                </div>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full table-auto" id="tabla-datos-reporte">
                                    <thead id="tabla-head-reporte"></thead>
                                    <tbody id="tabla-body-reporte"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Estado inicial -->
                    <div class="text-center py-20" id="estado-inicial-reporte">
                        <i class="fas fa-chart-bar text-6xl text-gray-300 mb-6"></i>
                        <h3 class="text-xl font-semibold text-gray-600 mb-2">¡Genera reportes POA!</h3>
                        <p class="text-gray-500">Selecciona una categoría del menú lateral para ver los datos y generar el reporte PDF</p>
                    </div>
                </div>
            </div>
        </main>
    </div>

<script>
    let categoriaActualReporte = '';
    let registrosActualesReporte = [];
    let contadorReportes = 0;
    
    async function mostrarReporte(categoria) {
        categoriaActualReporte = categoria;
        
        // Actualizar elementos activos
        document.querySelectorAll('.category-item').forEach(item => {
            item.classList.remove('active');
        });
        event.target.classList.add('active');
        
        // Ocultar estado inicial
        document.getElementById('estado-inicial-reporte').classList.add('hidden');
        
        // Mostrar tabla de reportes
        document.getElementById('tabla-reportes').classList.remove('hidden');
        
        // Mostrar indicador de carga
        document.getElementById('tabla-body-reporte').innerHTML = `
            <tr>
                <td colspan="100%" class="text-center py-8">
                    <i class="fas fa-spinner fa-spin text-blue-500 text-2xl mb-2"></i>
                    <p class="text-gray-600">Cargando datos para el reporte...</p>
                </td>
            </tr>
        `;

        // Actualizar títulos
        const categoriaTexto = categoria.replace(/_/g, ' ').toUpperCase();
        document.getElementById('main-title').textContent = `Reporte: ${categoriaTexto}`;
        document.getElementById('reporte-titulo').textContent = `REPORTE - ${categoriaTexto}`;
        
        try {
            // Cargar datos vía AJAX
            const response = await fetch(`?action=get_records&categoria=${categoria}`);
            const result = await response.json();
            
            if (result.success) {
                registrosActualesReporte = result.data;
                generarTablaReporte(categoria, result.data);
                
                // Actualizar contador de último reporte
                document.getElementById('ultimo-reporte').textContent = new Date().toLocaleString();
            } else {
                throw new Error(result.error || 'Error desconocido');
            }
            
        } catch (error) {
            console.error('Error al cargar datos del reporte:', error);
            document.getElementById('tabla-body-reporte').innerHTML = `
                <tr>
                    <td colspan="100%" class="text-center py-8 text-red-500">
                        <i class="fas fa-exclamation-triangle text-2xl mb-2"></i>
                        <p>Error al cargar los datos: ${error.message}</p>
                    </td>
                </tr>
            `;
        }
    }
    
    function generarTablaReporte(categoria, registros) {
        const thead = document.getElementById('tabla-head-reporte');
        const tbody = document.getElementById('tabla-body-reporte');
        const contador = document.getElementById('contador-registros-reporte');
        
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
                        No hay registros para generar el reporte
                    </td>
                </tr>
            `;
            // Deshabilitar botón de descarga
            document.getElementById('btn-descargar').disabled = true;
            document.getElementById('btn-descargar').classList.add('opacity-50', 'cursor-not-allowed');
            return;
        }
        
        // Habilitar botón de descarga
        document.getElementById('btn-descargar').disabled = false;
        document.getElementById('btn-descargar').classList.remove('opacity-50', 'cursor-not-allowed');
        
        // Generar encabezados
        const campos = Object.keys(registros[0]);
        const headerRow = document.createElement('tr');
        headerRow.className = 'bg-gray-50';
        
        campos.forEach(campo => {
            headerRow.innerHTML += `<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">${campo.replace(/_/g, ' ')}</th>`;
        });
        
        thead.appendChild(headerRow);
        
        // Generar filas de datos
        registros.forEach((registro, index) => {
            const row = document.createElement('tr');
            row.className = `table-hover border-b border-gray-200 ${index % 2 === 0 ? 'bg-white' : 'bg-gray-50'}`;
            
            campos.forEach(campo => {
                let valor = registro[campo] || '';
                
                // Formatear valores booleanos
                if (valor === 1 || valor === '1' || valor === 0 || valor === '0') {
                    valor = (valor == 1 || valor === '1') ? 
                           '<span class="text-green-600 font-medium">SI</span>' : 
                           '<span class="text-red-600 font-medium">NO</span>';
                } else if (campo === 'evidencia_link' && valor) {
                    valor = `<a href="${valor}" target="_blank" class="text-blue-600 hover:underline"><i class="fas fa-external-link-alt"></i> Ver</a>`;
                } else {
                    // Truncar texto largo para visualización
                    if (typeof valor === 'string' && valor.length > 50) {
                        valor = `<span title="${valor}">${valor.substring(0, 50)}...</span>`;
                    }
                    valor = valor || '-';
                }
                
                row.innerHTML += `<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${valor}</td>`;
            });
            
            tbody.appendChild(row);
        });
    }

    function descargarPDF() {
        if (!categoriaActualReporte || registrosActualesReporte.length === 0) {
            alert('No hay datos para generar el PDF');
            return;
        }
        
        // Mostrar indicador de descarga
        const btnDescargar = document.getElementById('btn-descargar');
        const textoOriginal = btnDescargar.innerHTML;
        btnDescargar.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Generando PDF...';
        btnDescargar.disabled = true;
        
        // Crear enlace de descarga
        const url = `?action=download_pdf&categoria=${categoriaActualReporte}`;
        const link = document.createElement('a');
        link.href = url;
        link.target = '_blank';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        // Incrementar contador de reportes
        contadorReportes++;
        document.getElementById('reportes-generados').textContent = contadorReportes;
        
        // Restaurar botón después de un breve delay
        setTimeout(() => {
            btnDescargar.innerHTML = textoOriginal;
            btnDescargar.disabled = false;
        }, 2000);
        
        // Mostrar mensaje de éxito
        mostrarMensajeReporte('Reporte generado y descargado exitosamente', 'success');
    }
    
    function mostrarMensajeReporte(texto, tipo) {
        // Crear elemento de mensaje si no existe
        let mensaje = document.getElementById('mensaje-reporte');
        if (!mensaje) {
            mensaje = document.createElement('div');
            mensaje.id = 'mensaje-reporte';
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

    // Cargar estadísticas al iniciar
    document.addEventListener('DOMContentLoaded', function() {
        // Aquí puedes agregar lógica adicional para cargar estadísticas generales
        console.log('Sistema de reportes POA cargado');
    });
</script>

</body>
</html>
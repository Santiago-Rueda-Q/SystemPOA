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
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Total Inscritos</h3>
                        <p class="stat-number">1,560</p>
                    </div>
                </div>

                <div class="stat-card secondary">
                    <div class="stat-icon">
                        <i class="fas fa-tools"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Técnico</h3>
                        <p class="stat-number">450</p>
                    </div>
                </div>

                <div class="stat-card tertiary">
                    <div class="stat-icon">
                        <i class="fas fa-laptop-code"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Tecnología</h3>
                        <p class="stat-number">780</p>
                    </div>
                </div>

                <div class="stat-card quaternary">
                    <div class="stat-icon">
                        <i class="fas fa-university"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Profesional</h3>
                        <p class="stat-number">330</p>
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

            <section class="recent-activity">
                <h2>Actividad Reciente</h2>
                <div class="activity-list">
                    <div class="activity-item">
                        <div class="activity-icon">
                            <i class="fas fa-plus-circle"></i>
                        </div>
                        <div class="activity-content">
                            <p><strong>POA actualizado</strong></p>
                            <span>Hace 2 horas</span>
                        </div>
                    </div>
                    <div class="activity-item">
                        <div class="activity-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="activity-content">
                            <p><strong>Componente completado</strong></p>
                            <span>Ayer</span>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <script src="dashboard.js"></script>
</body>
</html>
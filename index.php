<?php
session_start();

// Incluir conexión a la base de datos
require_once 'conexion.php';

// Variables para mensajes
$mensaje = '';
$tipo_mensaje = '';

// Procesar formulario de registro
if (isset($_POST['registro'])) {
    $nombre = trim($_POST['txt']);
    $email = trim($_POST['email']);
    $rol = trim($_POST['role']);
    $password = $_POST['pswd'];

    // Validaciones
    if (empty($nombre) || empty($email) || empty($rol) || empty($password)) {
        $mensaje = "Todos los campos son obligatorios.";
        $tipo_mensaje = 'error';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mensaje = "El formato del email no es válido.";
        $tipo_mensaje = 'error';
    } elseif (strlen($password) < 6) {
        $mensaje = "La contraseña debe tener al menos 6 caracteres.";
        $tipo_mensaje = 'error';
    } else {
        try {
            // Verificar si el email ya existe
            $check_stmt = $conn->prepare("SELECT COUNT(*) FROM usuario WHERE email = :email");
            $check_stmt->bindParam(':email', $email);
            $check_stmt->execute();
            
            if ($check_stmt->fetchColumn() > 0) {
                $mensaje = "Este email ya está registrado.";
                $tipo_mensaje = 'error';
            } else {
                // Insertar nuevo usuario
                $password_hash = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $conn->prepare("INSERT INTO usuario (nombre, email, rol, contrasena_hash) VALUES (:nombre, :email, :rol, :pass)");
                $stmt->bindParam(':nombre', $nombre);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':rol', $rol);
                $stmt->bindParam(':pass', $password_hash);
                $stmt->execute();
                
                $mensaje = "¡Registro exitoso! Ya puedes iniciar sesión.";
                $tipo_mensaje = 'success';
            }
        } catch (PDOException $e) {
            $mensaje = "Error al registrar: " . $e->getMessage();
            $tipo_mensaje = 'error';
        }
    }
}

// Procesar formulario de login
if (isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['pswd'];

    // Validaciones
    if (empty($email) || empty($password)) {
        $mensaje = "Email y contraseña son obligatorios.";
        $tipo_mensaje = 'error';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mensaje = "El formato del email no es válido.";
        $tipo_mensaje = 'error';
    } else {
        try {
            $stmt = $conn->prepare("SELECT * FROM usuario WHERE email = :email");
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($usuario && password_verify($password, $usuario['contrasena_hash'])) {
                // Guardar información del usuario en la sesión
                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['usuario_nombre'] = $usuario['nombre'];
                $_SESSION['usuario_email'] = $usuario['email'];
                $_SESSION['usuario_rol'] = $usuario['rol'];
                
                // Redirigir al dashboard
                header("Location: dashboard.php");
                exit;
            } else {
                $mensaje = "Credenciales inválidas. Verifica tu email y contraseña.";
                $tipo_mensaje = 'error';
            }
        } catch (PDOException $e) {
            $mensaje = "Error al iniciar sesión: " . $e->getMessage();
            $tipo_mensaje = 'error';
        }
    }
}

// Si ya está logueado, redirigir al dashboard
if (isset($_SESSION['usuario_nombre'])) {
    header("Location: dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SystemPOA - Acceso</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container" id="main-container">
        <!-- Panel de Bienvenida -->
        <div class="welcome-panel">
            <div class="logo">
                <i class="fas fa-graduation-cap"></i>
                SystemPOA
            </div>
            <div class="welcome-text">
                Sistema de Planificación Operativa Anual para la gestión académica y administrativa de tu institución educativa.
            </div>
            <div class="welcome-image">
                <img src="resource/software.png" alt="SystemPOA Software" />
            </div>
        </div>

        <!-- Contenedor de Formularios -->
        <div class="form-container show-login" id="form-container">
            
            <!-- Mensajes -->
            <?php if (!empty($mensaje)): ?>
                <div class="message <?php echo $tipo_mensaje; ?>">
                    <i class="fas fa-<?php echo $tipo_mensaje === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                    <?php echo htmlspecialchars($mensaje); ?>
                </div>
            <?php endif; ?>

            <!-- Formulario de Login -->
            <div class="login-form" id="login-form">
                <h2 class="form-title">Bienvenido</h2>
                <p class="form-subtitle">Ingresa tus credenciales para acceder</p>

                <form method="POST" action="">
                    <div class="form-group">
                        <label for="login-email">Correo Electrónico</label>
                        <div class="input-wrapper">
                            <i class="fas fa-envelope"></i>
                            <input type="email" id="login-email" name="email" class="has-icon" 
                                   placeholder="tu@correo.com" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="login-password">Contraseña</label>
                        <div class="input-wrapper">
                            <i class="fas fa-lock"></i>
                            <input type="password" id="login-password" name="pswd" class="has-icon" 
                                   placeholder="Tu contraseña" required>
                        </div>
                    </div>

                    <button type="submit" name="login" class="btn btn-primary">
                        <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
                    </button>
                </form>

                <div class="switch-form">
                    <span>¿No tienes cuenta? </span>
                    <a href="#" onclick="toggleForm('register')">Regístrate aquí</a>
                </div>
            </div>

            <!-- Formulario de Registro -->
            <div class="register-form" id="register-form" style="display: none;">
                <h2 class="form-title">Crear Cuenta</h2>
                <p class="form-subtitle">Completa tus datos para registrarte</p>

                <form method="POST" action="">
                    <div class="form-group">
                        <label for="register-name">Nombre Completo</label>
                        <div class="input-wrapper">
                            <i class="fas fa-user"></i>
                            <input type="text" id="register-name" name="txt" class="has-icon" 
                                   placeholder="Tu nombre completo" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="register-email">Correo Electrónico</label>
                        <div class="input-wrapper">
                            <i class="fas fa-envelope"></i>
                            <input type="email" id="register-email" name="email" class="has-icon" 
                                   placeholder="tu@correo.com" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="register-role">Rol</label>
                        <div class="input-wrapper">
                            <i class="fas fa-user-tag"></i>
                            <select id="register-role" name="role" class="has-icon" required>
                                <option value="" disabled selected>Selecciona tu rol</option>
                                <option value="Director">Director</option>
                                <option value="Docente">Docente</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="register-password">Contraseña</label>
                        <div class="input-wrapper">
                            <i class="fas fa-lock"></i>
                            <input type="password" id="register-password" name="pswd" class="has-icon" 
                                   placeholder="Mínimo 6 caracteres" required minlength="6">
                        </div>
                    </div>

                    <button type="submit" name="registro" class="btn btn-primary">
                        <i class="fas fa-user-plus"></i> Crear Cuenta
                    </button>
                </form>

                <div class="switch-form">
                    <span>¿Ya tienes cuenta? </span>
                    <a href="#" onclick="toggleForm('login')">Inicia sesión aquí</a>
                </div>
            </div>
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>
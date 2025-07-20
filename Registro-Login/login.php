<?php
session_start(); // Iniciar sesión para manejar el estado del usuario
require '../conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['pswd'];

    // Validar que los campos no estén vacíos
    if (empty($email) || empty($password)) {
        echo "Email y contraseña son obligatorios.";
        exit;
    }

    // Validar formato de email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "El formato del email no es válido.";
        exit;
    }

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
            
            echo "Bienvenido, " . $usuario['nombre'] . " (" . $usuario['rol'] . ")";
            
            // Opcional: Redirigir a una página de dashboard según el rol
            /*
            if ($usuario['rol'] === 'Director') {
                header("Location: ../dashboard_director.php");
            } else {
                header("Location: ../dashboard_docente.php");
            }
            exit;
            */
            
        } else {
            echo "Credenciales inválidas. Verifica tu email y contraseña.";
        }
    } catch (PDOException $e) {
        echo "Error al ingresar: " . $e->getMessage();
    }
}
?>
<?php
require '../conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['txt']);
    $email = trim($_POST['email']);
    $rol = trim($_POST['role']);
    $password = password_hash($_POST['pswd'], PASSWORD_BCRYPT);

    // Validar que los campos no estén vacíos
    if (empty($nombre) || empty($email) || empty($rol) || empty($_POST['pswd'])) {
        echo "Todos los campos son obligatorios.";
        exit;
    }

    // Validar formato de email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "El formato del email no es válido.";
        exit;
    }

    // Verificar si el email ya existe
    try {
        $check_stmt = $conn->prepare("SELECT COUNT(*) FROM usuario WHERE email = :email");
        $check_stmt->bindParam(':email', $email);
        $check_stmt->execute();
        
        if ($check_stmt->fetchColumn() > 0) {
            echo "Este email ya está registrado.";
            exit;
        }
    } catch (PDOException $e) {
        echo "Error al verificar email: " . $e->getMessage();
        exit;
    }

    // Insertar nuevo usuario
    try {
        $stmt = $conn->prepare("INSERT INTO usuario (nombre, email, rol, contrasena_hash) VALUES (:nombre, :email, :rol, :pass)");
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':rol', $rol);
        $stmt->bindParam(':pass', $password);
        $stmt->execute();
        
        echo "Registro exitoso. Usuario creado correctamente.";
        
        // Opcional: Redirigir al login después del registro exitoso
        header("Location: ../dashboard.php");
        exit;
        
    } catch (PDOException $e) {
        echo "Error al registrar: " . $e->getMessage();
    }
}
?>
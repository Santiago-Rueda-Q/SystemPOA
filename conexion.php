<?php
// Datos de conexión
$host = "postgres.juansegaliz.com";
$port = "5432";
$dbname = "SystemPOA";
$user = "est_s_rueda";
$password = "5E3c45k0TEhi5ttC";

try {
    // Crear conexión PDO para PostgreSQL
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
    $conn = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
        
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>
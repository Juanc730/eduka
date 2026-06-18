<?php
$host     = 'localhost';
$dbname   = 'eduka_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexión a la base de datos']);
    exit;
}
?>
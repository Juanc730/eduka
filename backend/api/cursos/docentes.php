<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../middleware/auth_middleware.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    json_response([], 200);
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_error('Método no permitido', 405);
}

verificar_rol(['administrador']);

$stmt = $pdo->query("SELECT id, nombre, apellido FROM usuarios WHERE rol_id = 3 AND activo = 1 ORDER BY nombre");
$docentes = $stmt->fetchAll();

json_success(['docentes' => $docentes]);
?>
<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../middleware/auth_middleware.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    json_response([], 200);
}

$payload = verificar_autenticacion();

// Verificar que el usuario sigue activo en la base de datos
$stmt = $pdo->prepare("SELECT activo FROM usuarios WHERE id = ?");
$stmt->execute([$payload['usuario_id']]);
$usuario = $stmt->fetch();

if (!$usuario || !$usuario['activo']) {
    json_error('Tu cuenta ha sido desactivada.', 403);
}

json_success([
    'usuario_id' => $payload['usuario_id'],
    'nombre'     => $payload['nombre'],
    'rol'        => $payload['rol']
]);
?>
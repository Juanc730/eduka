<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../middleware/auth_middleware.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    json_response([], 200);
}

if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    json_error('Método no permitido', 405);
}

$payload = verificar_rol(['administrador']);

$input = json_decode(file_get_contents('php://input'), true);
$id    = (int)($input['id'] ?? 0);

if (empty($id)) {
    json_error('ID de usuario requerido.', 400);
}

if ($id === $payload['usuario_id']) {
    json_error('No puedes desactivar tu propia cuenta.', 403);
}

$stmt = $pdo->prepare("SELECT id FROM usuarios WHERE id = ?");
$stmt->execute([$id]);
if (!$stmt->fetch()) {
    json_error('Usuario no encontrado.', 404);
}

$pdo->prepare("UPDATE usuarios SET activo = NOT activo WHERE id = ?")->execute([$id]);

json_success([], 'Estado del usuario actualizado.');
?>
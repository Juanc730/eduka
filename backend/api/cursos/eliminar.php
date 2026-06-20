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

verificar_rol(['administrador']);

$input = json_decode(file_get_contents('php://input'), true);
$id    = (int)($input['id'] ?? 0);

if (empty($id)) {
    json_error('ID de curso requerido.', 400);
}

$stmt = $pdo->prepare("SELECT id FROM cursos WHERE id = ?");
$stmt->execute([$id]);
if (!$stmt->fetch()) {
    json_error('Curso no encontrado.', 404);
}

$pdo->prepare("UPDATE cursos SET estado = 'inactivo' WHERE id = ?")->execute([$id]);

json_success([], 'Curso eliminado correctamente.');
?>
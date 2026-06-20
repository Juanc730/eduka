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
    json_error('ID de matrícula requerido.', 400);
}

$stmt = $pdo->prepare("SELECT * FROM matriculas WHERE id = ?");
$stmt->execute([$id]);
$matricula = $stmt->fetch();

if (!$matricula) {
    json_error('Matrícula no encontrada.', 404);
}

if ($matricula['estado'] === 'anulada') {
    json_error('Esta matrícula ya está anulada.', 409);
}

try {
    $pdo->beginTransaction();
    $pdo->prepare("UPDATE matriculas SET estado = 'anulada' WHERE id = ?")->execute([$id]);
    $pdo->prepare("UPDATE cursos SET cupos_disponibles = cupos_disponibles + 1 WHERE id = ?")->execute([$matricula['curso_id']]);
    $pdo->commit();

    json_success([], 'Matrícula anulada correctamente.');
} catch (Exception $e) {
    $pdo->rollBack();
    json_error('Error al anular la matrícula.', 500);
}
?>
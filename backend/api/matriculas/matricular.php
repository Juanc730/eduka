<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../middleware/auth_middleware.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    json_response([], 200);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('Método no permitido', 405);
}

$payload = verificar_rol(['estudiante']);

$input    = json_decode(file_get_contents('php://input'), true);
$curso_id = (int)($input['curso_id'] ?? 0);
$estudiante_id = $payload['usuario_id'];

if (empty($curso_id)) {
    json_error('ID de curso requerido.', 400);
}

$stmt = $pdo->prepare("SELECT * FROM cursos WHERE id = ? AND estado = 'activo'");
$stmt->execute([$curso_id]);
$curso = $stmt->fetch();

if (!$curso) {
    json_error('Curso no encontrado.', 404);
}

$stmt = $pdo->prepare("SELECT id FROM matriculas WHERE estudiante_id = ? AND curso_id = ? AND estado != 'anulada'");
$stmt->execute([$estudiante_id, $curso_id]);
if ($stmt->fetch()) {
    json_error('Ya estás matriculado en este curso.', 409);
}

if ($curso['cupos_disponibles'] <= 0) {
    json_error('No hay cupos disponibles. Puedes unirte a la lista de espera.', 409);
}

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("INSERT INTO matriculas (estudiante_id, curso_id, estado) VALUES (?, ?, 'pendiente')");
    $stmt->execute([$estudiante_id, $curso_id]);
    $matricula_id = $pdo->lastInsertId();

    $pdo->prepare("UPDATE cursos SET cupos_disponibles = cupos_disponibles - 1 WHERE id = ?")->execute([$curso_id]);

    $pdo->commit();

    json_success(['matricula_id' => $matricula_id], 'Matrícula registrada. Continúa con el pago.');
} catch (Exception $e) {
    $pdo->rollBack();
    json_error('Ocurrió un error al procesar la matrícula.', 500);
}
?>
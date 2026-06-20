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

$stmt = $pdo->prepare("SELECT id FROM cursos WHERE id = ? AND estado = 'activo'");
$stmt->execute([$curso_id]);
if (!$stmt->fetch()) {
    json_error('Curso no encontrado.', 404);
}

$stmt = $pdo->prepare("SELECT id FROM lista_espera WHERE estudiante_id = ? AND curso_id = ?");
$stmt->execute([$estudiante_id, $curso_id]);
if ($stmt->fetch()) {
    json_error('Ya estás en la lista de espera de este curso.', 409);
}

$stmt = $pdo->prepare("INSERT INTO lista_espera (estudiante_id, curso_id) VALUES (?, ?)");
$stmt->execute([$estudiante_id, $curso_id]);

json_success([], 'Te uniste a la lista de espera correctamente.');
?>
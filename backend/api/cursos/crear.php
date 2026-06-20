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

verificar_rol(['administrador']);

$input = json_decode(file_get_contents('php://input'), true);

$nombre        = trim($input['nombre'] ?? '');
$descripcion   = trim($input['descripcion'] ?? '');
$docente_id    = !empty($input['docente_id']) ? (int)$input['docente_id'] : null;
$cupos_totales = (int)($input['cupos_totales'] ?? 0);
$horario       = trim($input['horario'] ?? '');

if (empty($nombre) || empty($cupos_totales) || empty($horario)) {
    json_error('Nombre, cupos y horario son obligatorios.', 400);
}

if ($cupos_totales < 1) {
    json_error('Los cupos deben ser al menos 1.', 400);
}

$stmt = $pdo->prepare("INSERT INTO cursos (nombre, descripcion, docente_id, cupos_totales, cupos_disponibles, horario)
                       VALUES (?, ?, ?, ?, ?, ?)");
$stmt->execute([$nombre, $descripcion, $docente_id, $cupos_totales, $cupos_totales, $horario]);

json_success(['id' => $pdo->lastInsertId()], 'Curso creado exitosamente.');
?>
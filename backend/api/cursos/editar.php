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

$id            = (int)($input['id'] ?? 0);
$nombre        = trim($input['nombre'] ?? '');
$descripcion   = trim($input['descripcion'] ?? '');
$docente_id    = !empty($input['docente_id']) ? (int)$input['docente_id'] : null;
$cupos_totales = (int)($input['cupos_totales'] ?? 0);
$horario       = trim($input['horario'] ?? '');

if (empty($id) || empty($nombre) || empty($cupos_totales) || empty($horario)) {
    json_error('Todos los campos obligatorios deben completarse.', 400);
}

$stmt = $pdo->prepare("SELECT * FROM cursos WHERE id = ?");
$stmt->execute([$id]);
$curso = $stmt->fetch();

if (!$curso) {
    json_error('Curso no encontrado.', 404);
}

// Ajustar cupos disponibles proporcionalmente al cambio en cupos totales
$diferencia          = $cupos_totales - $curso['cupos_totales'];
$nuevos_disponibles  = max(0, $curso['cupos_disponibles'] + $diferencia);

$stmt = $pdo->prepare("UPDATE cursos SET nombre=?, descripcion=?, docente_id=?, cupos_totales=?, cupos_disponibles=?, horario=? WHERE id=?");
$stmt->execute([$nombre, $descripcion, $docente_id, $cupos_totales, $nuevos_disponibles, $horario, $id]);

json_success([], 'Curso actualizado correctamente.');
?>
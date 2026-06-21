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

$payload = verificar_rol(['administrador', 'docente']);

// Listar cursos disponibles para el selector, filtrados por rol
if ($payload['rol'] === 'docente') {
    $stmt = $pdo->prepare("SELECT id, nombre FROM cursos WHERE docente_id = ? AND estado = 'activo' ORDER BY nombre");
    $stmt->execute([$payload['usuario_id']]);
} else {
    $stmt = $pdo->query("SELECT id, nombre FROM cursos WHERE estado = 'activo' ORDER BY nombre");
}
$cursos = $stmt->fetchAll();

$curso_id = isset($_GET['curso_id']) ? (int)$_GET['curso_id'] : 0;

if (!$curso_id) {
    json_success(['cursos' => $cursos, 'matriculas' => []]);
}

// Si es docente, verificar que el curso le pertenece
if ($payload['rol'] === 'docente') {
    $stmt = $pdo->prepare("SELECT id FROM cursos WHERE id = ? AND docente_id = ?");
    $stmt->execute([$curso_id, $payload['usuario_id']]);
    if (!$stmt->fetch()) {
        json_error('No tienes permiso para ver este curso.', 403);
    }
}

$stmt = $pdo->prepare("SELECT m.id, m.fecha, m.estado AS matricula_estado,
                              CONCAT(u.nombre, ' ', u.apellido) AS estudiante,
                              u.email,
                              p.estado AS pago_estado,
                              p.monto,
                              p.metodo_verificacion
                       FROM matriculas m
                       JOIN usuarios u ON m.estudiante_id = u.id
                       LEFT JOIN pagos p ON p.matricula_id = m.id
                       WHERE m.curso_id = ?
                       ORDER BY m.fecha DESC");
$stmt->execute([$curso_id]);
$matriculas = $stmt->fetchAll();

json_success(['cursos' => $cursos, 'matriculas' => $matriculas]);
?>
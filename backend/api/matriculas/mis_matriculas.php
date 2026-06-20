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

$payload = verificar_rol(['estudiante']);

$stmt = $pdo->prepare("SELECT m.*, c.nombre AS curso, c.horario,
                              p.estado AS pago_estado, p.id AS pago_id,
                              u.nombre AS docente_nombre, u.apellido AS docente_apellido
                       FROM matriculas m
                       JOIN cursos c ON m.curso_id = c.id
                       LEFT JOIN usuarios u ON c.docente_id = u.id
                       LEFT JOIN pagos p ON p.matricula_id = m.id
                       WHERE m.estudiante_id = ?
                       ORDER BY m.fecha DESC");
$stmt->execute([$payload['usuario_id']]);
$matriculas = $stmt->fetchAll();

json_success(['matriculas' => $matriculas]);
?>
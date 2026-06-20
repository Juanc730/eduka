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

$stmt = $pdo->prepare("SELECT p.*, c.nombre AS curso, m.estado AS matricula_estado
                       FROM pagos p
                       JOIN matriculas m ON p.matricula_id = m.id
                       JOIN cursos c ON m.curso_id = c.id
                       WHERE m.estudiante_id = ?
                       ORDER BY p.fecha DESC");
$stmt->execute([$payload['usuario_id']]);
$pagos = $stmt->fetchAll();

json_success(['pagos' => $pagos]);
?>
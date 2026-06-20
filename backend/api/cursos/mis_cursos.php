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

$payload = verificar_rol(['docente']);

$stmt = $pdo->prepare("SELECT c.*,
                              (c.cupos_totales - c.cupos_disponibles) AS ocupados,
                              COUNT(m.id) AS total_matriculas
                       FROM cursos c
                       LEFT JOIN matriculas m ON m.curso_id = c.id AND m.estado != 'anulada'
                       WHERE c.docente_id = ? AND c.estado = 'activo'
                       GROUP BY c.id
                       ORDER BY c.nombre");
$stmt->execute([$payload['usuario_id']]);
$cursos = $stmt->fetchAll();

json_success(['cursos' => $cursos]);
?>
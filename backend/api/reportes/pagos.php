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

verificar_rol(['administrador']);

$stmt = $pdo->query("SELECT p.*,
                            CONCAT(u.nombre, ' ', u.apellido) AS estudiante,
                            c.nombre AS curso
                     FROM pagos p
                     JOIN matriculas m ON p.matricula_id = m.id
                     JOIN usuarios u ON m.estudiante_id = u.id
                     JOIN cursos c ON m.curso_id = c.id
                     ORDER BY p.fecha DESC");
$pagos = $stmt->fetchAll();

$total_aprobado  = array_sum(array_map(fn($p) => $p['estado'] === 'aprobado' ? (float)$p['monto'] : 0, $pagos));
$total_pendiente = count(array_filter($pagos, fn($p) => $p['estado'] === 'pendiente'));
$total_rechazado = count(array_filter($pagos, fn($p) => $p['estado'] === 'rechazado'));

json_success([
    'pagos'           => $pagos,
    'total_aprobado'  => $total_aprobado,
    'total_pendiente' => $total_pendiente,
    'total_rechazado' => $total_rechazado,
    'total_registros' => count($pagos)
]);
?>
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

$stmt = $pdo->query("SELECT c.*,
                            CONCAT(u.nombre, ' ', u.apellido) AS docente,
                            (c.cupos_totales - c.cupos_disponibles) AS ocupados
                     FROM cursos c
                     LEFT JOIN usuarios u ON c.docente_id = u.id
                     WHERE c.estado = 'activo'
                     ORDER BY ocupados DESC");
$cursos = $stmt->fetchAll();

json_success(['cursos' => $cursos]);
?>
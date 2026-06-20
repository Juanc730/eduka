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

verificar_autenticacion();

$stmt = $pdo->query("SELECT c.*, u.nombre AS docente_nombre, u.apellido AS docente_apellido
                     FROM cursos c
                     LEFT JOIN usuarios u ON c.docente_id = u.id
                     WHERE c.estado = 'activo'
                     ORDER BY c.nombre");
$cursos = $stmt->fetchAll();

json_success(['cursos' => $cursos]);
?>
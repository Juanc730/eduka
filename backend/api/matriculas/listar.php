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

$buscar        = isset($_GET['buscar']) ? trim($_GET['buscar']) : '';
$filtro_curso  = isset($_GET['curso_id']) ? (int)$_GET['curso_id'] : 0;
$filtro_estado = isset($_GET['estado']) ? trim($_GET['estado']) : '';
$pagina        = isset($_GET['pagina']) ? max(1, (int)$_GET['pagina']) : 1;
$por_pagina    = 10;
$offset        = ($pagina - 1) * $por_pagina;

$where  = [];
$params = [];

if ($filtro_curso) {
    $where[]  = 'm.curso_id = ?';
    $params[] = $filtro_curso;
}
if ($filtro_estado) {
    $where[]  = 'm.estado = ?';
    $params[] = $filtro_estado;
}
if ($buscar) {
    $where[]  = "(u.nombre LIKE ? OR u.apellido LIKE ? OR u.email LIKE ? OR c.nombre LIKE ?)";
    $termino  = "%$buscar%";
    $params   = array_merge($params, [$termino, $termino, $termino, $termino]);
}

$sql_base = "FROM matriculas m
             JOIN usuarios u ON m.estudiante_id = u.id
             JOIN cursos c ON m.curso_id = c.id"
          . ($where ? ' WHERE ' . implode(' AND ', $where) : '');

$stmt_total = $pdo->prepare("SELECT COUNT(*) $sql_base");
$stmt_total->execute($params);
$total      = $stmt_total->fetchColumn();
$total_pags = ceil($total / $por_pagina);

$stmt = $pdo->prepare("SELECT m.id, m.fecha, m.estado,
                              CONCAT(u.nombre, ' ', u.apellido) AS estudiante,
                              u.email,
                              c.nombre AS curso
                       $sql_base
                       ORDER BY m.fecha DESC
                       LIMIT $por_pagina OFFSET $offset");
$stmt->execute($params);
$matriculas = $stmt->fetchAll();

json_success([
    'matriculas' => $matriculas,
    'total'      => $total,
    'pagina'     => $pagina,
    'total_pags' => $total_pags
]);
?>
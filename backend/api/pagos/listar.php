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

$buscar     = isset($_GET['buscar']) ? trim($_GET['buscar']) : '';
$pagina     = isset($_GET['pagina']) ? max(1, (int)$_GET['pagina']) : 1;
$por_pagina = 10;
$offset     = ($pagina - 1) * $por_pagina;

$where  = [];
$params = [];

if ($buscar) {
    $where[]  = "(u.nombre LIKE ? OR u.apellido LIKE ? OR c.nombre LIKE ? OR p.estado LIKE ?)";
    $termino  = "%$buscar%";
    $params   = [$termino, $termino, $termino, $termino];
}

$sql_base = "FROM pagos p
             JOIN matriculas m ON p.matricula_id = m.id
             JOIN usuarios u ON m.estudiante_id = u.id
             JOIN cursos c ON m.curso_id = c.id"
          . ($where ? ' WHERE ' . implode(' AND ', $where) : '');

$stmt_total = $pdo->prepare("SELECT COUNT(*) $sql_base");
$stmt_total->execute($params);
$total      = $stmt_total->fetchColumn();
$total_pags = ceil($total / $por_pagina);

$stmt = $pdo->prepare("SELECT p.*,
                              CONCAT(u.nombre, ' ', u.apellido) AS estudiante,
                              c.nombre AS curso
                       $sql_base
                       ORDER BY p.estado ASC, p.fecha DESC
                       LIMIT $por_pagina OFFSET $offset");
$stmt->execute($params);
$pagos = $stmt->fetchAll();

json_success([
    'pagos'      => $pagos,
    'total'      => $total,
    'pagina'     => $pagina,
    'total_pags' => $total_pags
]);
?>
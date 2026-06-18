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
    $where[]  = "(u.nombre LIKE ? OR u.apellido LIKE ? OR u.email LIKE ? OR r.nombre LIKE ?)";
    $termino  = "%$buscar%";
    $params   = [$termino, $termino, $termino, $termino];
}

$sql_base = "FROM usuarios u JOIN roles r ON u.rol_id = r.id"
          . ($where ? ' WHERE ' . implode(' AND ', $where) : '');

$stmt_total = $pdo->prepare("SELECT COUNT(*) $sql_base");
$stmt_total->execute($params);
$total      = $stmt_total->fetchColumn();
$total_pags = ceil($total / $por_pagina);

$stmt = $pdo->prepare("SELECT u.id, u.nombre, u.apellido, u.email, u.activo, u.rol_id, r.nombre AS rol
                       $sql_base
                       ORDER BY u.rol_id, u.nombre
                       LIMIT $por_pagina OFFSET $offset");
$stmt->execute($params);
$usuarios = $stmt->fetchAll();

json_success([
    'usuarios'   => $usuarios,
    'total'      => $total,
    'pagina'     => $pagina,
    'total_pags' => $total_pags
]);
?>
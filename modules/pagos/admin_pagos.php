<?php
$page_title = 'Gestión de Pagos';
require_once '../../includes/header.php';
require_once '../../config/database.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'administrador') {
    header('Location: /eduka/login.php');
    exit;
}

// Aprobar o rechazar pago por comprobante
if (isset($_GET['accion']) && isset($_GET['pago_id'])) {
    $pago_id = (int)$_GET['pago_id'];
    $accion  = $_GET['accion'];

    $stmt = $pdo->prepare("SELECT * FROM pagos WHERE id = ?");
    $stmt->execute([$pago_id]);
    $pago = $stmt->fetch();

    if ($pago) {
        if ($accion === 'aprobar') {
            $pdo->prepare("UPDATE pagos SET estado = 'aprobado' WHERE id = ?")->execute([$pago_id]);
            $pdo->prepare("UPDATE matriculas SET estado = 'confirmada' WHERE id = ?")->execute([$pago['matricula_id']]);
        } elseif ($accion === 'rechazar') {
            $pdo->prepare("UPDATE pagos SET estado = 'rechazado' WHERE id = ?")->execute([$pago_id]);
            $pdo->prepare("UPDATE matriculas SET estado = 'pendiente' WHERE id = ?")->execute([$pago['matricula_id']]);
        }
    }
    header('Location: /eduka/modules/pagos/admin_pagos.php');
    exit;
}

// Buscador y paginación
$buscar     = isset($_GET['buscar']) ? trim($_GET['buscar']) : '';
$where      = [];
$params     = [];

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

$total_stmt = $pdo->prepare("SELECT COUNT(*) $sql_base");
$total_stmt->execute($params);
$total      = $total_stmt->fetchColumn();

$por_pagina = 10;
$pagina     = isset($_GET['pagina']) ? max(1, (int)$_GET['pagina']) : 1;
$offset     = ($pagina - 1) * $por_pagina;
$total_pags = ceil($total / $por_pagina);

$stmt = $pdo->prepare("SELECT p.*,
                              CONCAT(u.nombre, ' ', u.apellido) AS estudiante,
                              c.nombre AS curso
                       $sql_base
                       ORDER BY p.estado ASC, p.fecha DESC
                       LIMIT $por_pagina OFFSET $offset");
$stmt->execute($params);
$pagos = $stmt->fetchAll();
?>

<?php include '../../includes/navbar.php'; ?>

<div class="dashboard-container">
    <div class="page-header">
        <h1>Gestión de Pagos</h1>
        <a href="/eduka/modules/pagos/cargar_yape.php" class="btn-primary">+ Cargar códigos Yape</a>
    </div>

    <!-- Buscador -->
    <form method="GET" class="buscador-container">
        <input type="text" name="buscar" value="<?= htmlspecialchars($buscar) ?>"
               placeholder="🔍 Buscar por estudiante, curso o estado..." class="buscador-input">
        <button type="submit" class="btn-primary">Buscar</button>
        <?php if ($buscar): ?>
            <a href="/eduka/modules/pagos/admin_pagos.php" class="btn-secondary">Limpiar</a>
        <?php endif; ?>
    </form>

    <?php if ($buscar): ?>
        <p class="buscador-contador" style="margin-bottom:1rem;">
            <?= $total ?> resultado(s) para "<strong><?= htmlspecialchars($buscar) ?></strong>"
        </p>
    <?php endif; ?>

    <?php if (empty($pagos)): ?>
        <p>No hay pagos que coincidan con la búsqueda.</p>
    <?php else: ?>
        <div class="table-container">
            <table class="tabla">
                <thead>
                    <tr>
                        <th>Estudiante</th>
                        <th>Curso</th>
                        <th>Monto</th>
                        <th>Verificación</th>
                        <th>Estado</th>
                        <th>Fecha</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pagos as $p): ?>
                        <tr>
                            <td><?= htmlspecialchars($p['estudiante']) ?></td>
                            <td><?= htmlspecialchars($p['curso']) ?></td>
                            <td>S/ <?= number_format($p['monto'], 2) ?></td>
                            <td>
                                <?php if ($p['metodo_verificacion'] === 'codigo'): ?>
                                    <span class="badge-activo">Código: <?= htmlspecialchars($p['codigo_yape']) ?></span>
                                <?php else: ?>
                                    <?php if ($p['comprobante']): ?>
                                        <a href="/eduka/uploads/comprobantes/<?= $p['comprobante'] ?>" target="_blank" class="btn-secondary">Ver imagen</a>
                                    <?php else: ?>
                                        <span style="color:#aaa;">Sin archivo</span>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>
                            <td><span class="estado-badge estado-<?= $p['estado'] ?>"><?= ucfirst($p['estado']) ?></span></td>
                            <td><?= date('d/m/Y H:i', strtotime($p['fecha'])) ?></td>
                            <td>
                                <?php if ($p['estado'] === 'pendiente' && $p['metodo_verificacion'] === 'comprobante'): ?>
                                    <div class="btn-group">
                                        <a href="?accion=aprobar&pago_id=<?= $p['id'] ?>" class="btn-primary" onclick="return confirm('¿Aprobar este pago?')">Aprobar</a>
                                        <a href="?accion=rechazar&pago_id=<?= $p['id'] ?>" class="btn-danger" onclick="return confirm('¿Rechazar este pago?')">Rechazar</a>
                                    </div>
                                <?php else: ?>
                                    <span style="color:#aaa;">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <?php if ($total_pags > 1): ?>
        <div class="paginacion">
            <?php if ($pagina > 1): ?>
                <a href="?buscar=<?= urlencode($buscar) ?>&pagina=<?= $pagina - 1 ?>" class="pag-btn">← Anterior</a>
            <?php endif; ?>
            <?php for ($i = 1; $i <= $total_pags; $i++): ?>
                <a href="?buscar=<?= urlencode($buscar) ?>&pagina=<?= $i ?>" class="pag-btn <?= $i === $pagina ? 'pag-activa' : '' ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>
            <?php if ($pagina < $total_pags): ?>
                <a href="?buscar=<?= urlencode($buscar) ?>&pagina=<?= $pagina + 1 ?>" class="pag-btn">Siguiente →</a>
            <?php endif; ?>
        </div>
        <p class="pag-info">Mostrando <?= count($pagos) ?> de <?= $total ?> pagos — Página <?= $pagina ?> de <?= $total_pags ?></p>
    <?php endif; ?>

</div>

<?php include '../../includes/footer.php'; ?>
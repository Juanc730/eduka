<?php
$page_title = 'Gestión de Matrículas';
require_once '../../includes/header.php';
require_once '../../config/database.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'administrador') {
    header('Location: /eduka/login.php');
    exit;
}

// Anular matrícula
if (isset($_GET['accion']) && $_GET['accion'] === 'anular' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];

    $stmt = $pdo->prepare("SELECT * FROM matriculas WHERE id = ?");
    $stmt->execute([$id]);
    $matricula = $stmt->fetch();

    if ($matricula && $matricula['estado'] !== 'anulada') {
        $pdo->beginTransaction();
        // Anular matrícula
        $pdo->prepare("UPDATE matriculas SET estado = 'anulada' WHERE id = ?")->execute([$id]);
        // Devolver cupo
        $pdo->prepare("UPDATE cursos SET cupos_disponibles = cupos_disponibles + 1 WHERE id = ?")->execute([$matricula['curso_id']]);
        $pdo->commit();
    }
    header('Location: /eduka/modules/admin/matriculas.php?msg=Matrícula anulada correctamente');
    exit;
}

// Filtros
$filtro_curso  = isset($_GET['curso_id']) ? (int)$_GET['curso_id'] : 0;
$filtro_estado = isset($_GET['estado']) ? $_GET['estado'] : '';

$cursos = $pdo->query("SELECT id, nombre FROM cursos WHERE estado = 'activo' ORDER BY nombre")->fetchAll();

// Construir consulta con filtros
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

$sql = "SELECT m.id, m.fecha, m.estado,
               CONCAT(u.nombre, ' ', u.apellido) AS estudiante,
               u.email,
               c.nombre AS curso,
               p.estado AS pago_estado,
               p.monto
        FROM matriculas m
        JOIN usuarios u ON m.estudiante_id = u.id
        JOIN cursos c ON m.curso_id = c.id
        LEFT JOIN pagos p ON p.matricula_id = m.id
        " . ($where ? 'WHERE ' . implode(' AND ', $where) : '') . "
        ORDER BY m.fecha DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$matriculas = $stmt->fetchAll();
?>

<?php include '../../includes/navbar.php'; ?>

<div class="dashboard-container">
    <div class="page-header">
        <h1>Gestión de Matrículas</h1>
    </div>

    <?php if (isset($_GET['msg'])): ?>
        <p class="success"><?= htmlspecialchars($_GET['msg']) ?></p>
    <?php endif; ?>

    <!-- Filtros -->
    <div class="form-card" style="margin-bottom:1.5rem;">
        <form method="GET" style="display:flex; gap:1rem; flex-wrap:wrap; align-items:flex-end;">
            <div style="flex:1; min-width:200px;">
                <label>Curso</label>
                <select name="curso_id">
                    <option value="">Todos los cursos</option>
                    <?php foreach ($cursos as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= $filtro_curso == $c['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="flex:1; min-width:150px;">
                <label>Estado</label>
                <select name="estado">
                    <option value="">Todos</option>
                    <option value="pendiente"   <?= $filtro_estado === 'pendiente'   ? 'selected' : '' ?>>Pendiente</option>
                    <option value="confirmada"  <?= $filtro_estado === 'confirmada'  ? 'selected' : '' ?>>Confirmada</option>
                    <option value="anulada"     <?= $filtro_estado === 'anulada'     ? 'selected' : '' ?>>Anulada</option>
                </select>
            </div>
            <div>
                <button type="submit" class="btn-primary">Filtrar</button>
                <a href="/eduka/modules/admin/matriculas.php" class="btn-secondary">Limpiar</a>
            </div>
        </form>
    </div>

    <!-- Resumen rápido -->
    <div class="reporte-resumen" style="margin-bottom:1.5rem;">
        <div class="resumen-card">
            <span class="resumen-num"><?= count($matriculas) ?></span>
            <span class="resumen-label">Total mostradas</span>
        </div>
        <div class="resumen-card">
            <span class="resumen-num"><?= count(array_filter($matriculas, fn($m) => $m['estado'] === 'confirmada')) ?></span>
            <span class="resumen-label">Confirmadas</span>
        </div>
        <div class="resumen-card">
            <span class="resumen-num"><?= count(array_filter($matriculas, fn($m) => $m['estado'] === 'pendiente')) ?></span>
            <span class="resumen-label">Pendientes</span>
        </div>
        <div class="resumen-card">
            <span class="resumen-num"><?= count(array_filter($matriculas, fn($m) => $m['estado'] === 'anulada')) ?></span>
            <span class="resumen-label">Anuladas</span>
        </div>
    </div>

    <!-- Tabla -->
    <?php if (empty($matriculas)): ?>
        <p>No hay matrículas que coincidan con los filtros seleccionados.</p>
    <?php else: ?>
        <div class="table-container">
            <table class="tabla">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Estudiante</th>
                        <th>Correo</th>
                        <th>Curso</th>
                        <th>Estado matrícula</th>
                        <th>Estado pago</th>
                        <th>Monto</th>
                        <th>Fecha</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($matriculas as $m): ?>
                        <tr>
                            <td><?= $m['id'] ?></td>
                            <td><?= htmlspecialchars($m['estudiante']) ?></td>
                            <td><?= htmlspecialchars($m['email']) ?></td>
                            <td><?= htmlspecialchars($m['curso']) ?></td>
                            <td><span class="estado-badge estado-<?= $m['estado'] ?>"><?= ucfirst($m['estado']) ?></span></td>
                            <td>
                                <?php if ($m['pago_estado']): ?>
                                    <span class="estado-badge estado-<?= $m['pago_estado'] ?>"><?= ucfirst($m['pago_estado']) ?></span>
                                <?php else: ?>
                                    <span style="color:#aaa;">Sin pago</span>
                                <?php endif; ?>
                            </td>
                            <td><?= $m['monto'] ? 'S/ ' . number_format($m['monto'], 2) : '—' ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($m['fecha'])) ?></td>
                            <td>
                                <?php if ($m['estado'] !== 'anulada'): ?>
                                    <a href="?accion=anular&id=<?= $m['id'] ?>" 
                                       class="btn-danger"
                                       onclick="return confirm('¿Anular esta matrícula? Se devolverá el cupo al curso.')">
                                        Anular
                                    </a>
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
</div>

<?php include '../../includes/footer.php'; ?>
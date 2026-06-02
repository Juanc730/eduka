<?php
require_once '../../includes/header.php';
require_once '../../config/database.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: /eduka/login.php');
    exit;
}

$rol = $_SESSION['rol'];

// Filtrar por docente si corresponde
if ($rol === 'docente') {
    $stmt = $pdo->prepare("SELECT c.id, c.nombre FROM cursos c WHERE c.docente_id = ? AND c.estado = 'activo'");
    $stmt->execute([$_SESSION['usuario_id']]);
} else {
    $stmt = $pdo->query("SELECT c.id, c.nombre FROM cursos c WHERE c.estado = 'activo'");
}
$cursos = $stmt->fetchAll();

$curso_id = isset($_GET['curso_id']) ? (int)$_GET['curso_id'] : null;
$matriculas = [];

if ($curso_id) {
    if ($rol === 'docente') {
        // Verificar que el curso pertenece al docente
        $check = $pdo->prepare("SELECT id FROM cursos WHERE id = ? AND docente_id = ?");
        $check->execute([$curso_id, $_SESSION['usuario_id']]);
        if (!$check->fetch()) {
            header('Location: /eduka/modules/reportes/reporte_matriculas.php');
            exit;
        }
    }

    $stmt = $pdo->prepare("SELECT m.id, m.fecha, m.estado AS matricula_estado,
                                  CONCAT(u.nombre, ' ', u.apellido) AS estudiante,
                                  u.email,
                                  p.estado AS pago_estado,
                                  p.monto,
                                  p.metodo_verificacion
                           FROM matriculas m
                           JOIN usuarios u ON m.estudiante_id = u.id
                           LEFT JOIN pagos p ON p.matricula_id = m.id
                           WHERE m.curso_id = ?
                           ORDER BY m.fecha DESC");
    $stmt->execute([$curso_id]);
    $matriculas = $stmt->fetchAll();
}
?>

<?php include '../../includes/navbar.php'; ?>

<div class="dashboard-container">
    <div class="page-header">
        <h1>Matrículas por Curso</h1>
        <a href="/eduka/modules/reportes/index.php" class="btn-secondary">← Volver</a>
    </div>

    <div class="form-card" style="margin-bottom:1.5rem;">
        <form method="GET">
            <label>Selecciona un curso</label>
            <select name="curso_id" onchange="this.form.submit()">
                <option value="">-- Selecciona --</option>
                <?php foreach ($cursos as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= $curso_id == $c['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($c['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>

    <?php if ($curso_id && empty($matriculas)): ?>
        <p>No hay matrículas registradas para este curso.</p>
    <?php elseif (!empty($matriculas)): ?>

        <div class="reporte-resumen">
            <div class="resumen-card">
                <span class="resumen-num"><?= count($matriculas) ?></span>
                <span class="resumen-label">Total matriculados</span>
            </div>
            <div class="resumen-card">
                <span class="resumen-num"><?= count(array_filter($matriculas, fn($m) => $m['matricula_estado'] === 'confirmada')) ?></span>
                <span class="resumen-label">Confirmados</span>
            </div>
            <div class="resumen-card">
                <span class="resumen-num"><?= count(array_filter($matriculas, fn($m) => $m['matricula_estado'] === 'pendiente')) ?></span>
                <span class="resumen-label">Pendientes</span>
            </div>
            <div class="resumen-card">
                <span class="resumen-num">S/ <?= number_format(array_sum(array_column(array_filter($matriculas, fn($m) => $m['pago_estado'] === 'aprobado'), 'monto')), 2) ?></span>
                <span class="resumen-label">Total recaudado</span>
            </div>
        </div>

        <div class="table-container" style="margin-top:1.5rem;">
            <table class="tabla">
                <thead>
                    <tr>
                        <th>Estudiante</th>
                        <th>Correo</th>
                        <th>Fecha matrícula</th>
                        <th>Estado matrícula</th>
                        <th>Estado pago</th>
                        <th>Monto</th>
                        <th>Método</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($matriculas as $m): ?>
                        <tr>
                            <td><?= htmlspecialchars($m['estudiante']) ?></td>
                            <td><?= htmlspecialchars($m['email']) ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($m['fecha'])) ?></td>
                            <td><span class="estado-badge estado-<?= $m['matricula_estado'] ?>"><?= ucfirst($m['matricula_estado']) ?></span></td>
                            <td>
                                <?php if ($m['pago_estado']): ?>
                                    <span class="estado-badge estado-<?= $m['pago_estado'] ?>"><?= ucfirst($m['pago_estado']) ?></span>
                                <?php else: ?>
                                    <span style="color:#aaa;">Sin pago</span>
                                <?php endif; ?>
                            </td>
                            <td><?= $m['monto'] ? 'S/ ' . number_format($m['monto'], 2) : '—' ?></td>
                            <td>
                                <?php if ($m['metodo_verificacion'] === 'codigo'): ?>
                                    <span class="badge-activo">Código Yape</span>
                                <?php elseif ($m['metodo_verificacion'] === 'comprobante'): ?>
                                    <span class="estado-badge estado-pendiente">Comprobante</span>
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
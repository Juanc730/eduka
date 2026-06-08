<?php
$page_title = 'Estado de Pagos';
require_once '../../includes/header.php';
require_once '../../config/database.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'administrador') {
    header('Location: /eduka/login.php');
    exit;
}

$pagos = $pdo->query("SELECT p.*,
                             CONCAT(u.nombre, ' ', u.apellido) AS estudiante,
                             c.nombre AS curso
                      FROM pagos p
                      JOIN matriculas m ON p.matricula_id = m.id
                      JOIN usuarios u ON m.estudiante_id = u.id
                      JOIN cursos c ON m.curso_id = c.id
                      ORDER BY p.fecha DESC")->fetchAll();

$total_aprobado  = array_sum(array_column(array_filter($pagos, fn($p) => $p['estado'] === 'aprobado'), 'monto'));
$total_pendiente = count(array_filter($pagos, fn($p) => $p['estado'] === 'pendiente'));
$total_rechazado = count(array_filter($pagos, fn($p) => $p['estado'] === 'rechazado'));
?>

<?php include '../../includes/navbar.php'; ?>

<div class="dashboard-container">
    <div class="page-header">
        <h1>Estado de Pagos</h1>
        <a href="/eduka/modules/reportes/index.php" class="btn-secondary">← Volver</a>
    </div>

    <div class="reporte-resumen">
        <div class="resumen-card">
            <span class="resumen-num">S/ <?= number_format($total_aprobado, 2) ?></span>
            <span class="resumen-label">Total recaudado</span>
        </div>
        <div class="resumen-card">
            <span class="resumen-num"><?= $total_pendiente ?></span>
            <span class="resumen-label">Pagos pendientes</span>
        </div>
        <div class="resumen-card">
            <span class="resumen-num"><?= $total_rechazado ?></span>
            <span class="resumen-label">Pagos rechazados</span>
        </div>
        <div class="resumen-card">
            <span class="resumen-num"><?= count($pagos) ?></span>
            <span class="resumen-label">Total registros</span>
        </div>
    </div>

    <div class="table-container" style="margin-top:1.5rem;">
        <table class="tabla">
            <thead>
                <tr>
                    <th>Estudiante</th>
                    <th>Curso</th>
                    <th>Monto</th>
                    <th>Método</th>
                    <th>Estado</th>
                    <th>Fecha</th>
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
                                <span class="badge-activo">Código Yape</span>
                            <?php else: ?>
                                <span class="estado-badge estado-pendiente">Comprobante</span>
                            <?php endif; ?>
                        </td>
                        <td><span class="estado-badge estado-<?= $p['estado'] ?>"><?= ucfirst($p['estado']) ?></span></td>
                        <td><?= date('d/m/Y H:i', strtotime($p['fecha'])) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
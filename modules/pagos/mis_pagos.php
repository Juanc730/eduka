<?php
$page_title = 'Mis Pagos';
require_once '../../includes/header.php';
require_once '../../config/database.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'estudiante') {
    header('Location: /eduka/login.php');
    exit;
}

$stmt = $pdo->prepare("SELECT p.*, c.nombre AS curso, m.estado AS matricula_estado
                       FROM pagos p
                       JOIN matriculas m ON p.matricula_id = m.id
                       JOIN cursos c ON m.curso_id = c.id
                       WHERE m.estudiante_id = ?
                       ORDER BY p.fecha DESC");
$stmt->execute([$_SESSION['usuario_id']]);
$pagos = $stmt->fetchAll();
?>

<?php include '../../includes/navbar.php'; ?>

<div class="dashboard-container">
    <h1 style="color:#1a3c5e; margin-bottom:1.5rem;">Mis Pagos</h1>

    <?php if (empty($pagos)): ?>
        <p>No tienes pagos registrados.</p>
    <?php else: ?>
        <div class="table-container">
            <table class="tabla">
                <thead>
                    <tr>
                        <th>Curso</th>
                        <th>Monto</th>
                        <th>Método</th>
                        <th>Verificación</th>
                        <th>Estado</th>
                        <th>Fecha</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pagos as $p): ?>
                        <tr>
                            <td><?= htmlspecialchars($p['curso']) ?></td>
                            <td>S/ <?= number_format($p['monto'], 2) ?></td>
                            <td><?= htmlspecialchars($p['metodo']) ?></td>
                            <td>
                                <?php if ($p['metodo_verificacion'] === 'codigo'): ?>
                                    <span class="badge-activo">Código Yape</span>
                                <?php else: ?>
                                    <span class="estado-badge estado-pendiente">Comprobante</span>
                                <?php endif; ?>
                            </td>
                            <td><span class="estado-badge estado-<?= $p['estado'] ?>"><?= ucfirst($p['estado']) ?></span></td>
                            <td><?= date('d/m/Y H:i', strtotime($p['fecha'])) ?></td>
                            <td>
                                <?php if ($p['estado'] === 'rechazado'): ?>
                                    <a href="/eduka/modules/pagos/subir_pago.php?matricula_id=<?= $p['matricula_id'] ?>" class="btn-primary">Reintentar</a>
                                <?php elseif ($p['comprobante']): ?>
                                    <a href="/eduka/uploads/comprobantes/<?= $p['comprobante'] ?>" target="_blank" class="btn-secondary">Ver imagen</a>
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
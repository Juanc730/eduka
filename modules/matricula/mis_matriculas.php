<?php
require_once '../../includes/header.php';
require_once '../../config/database.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'estudiante') {
    header('Location: /eduka/login.php');
    exit;
}

$stmt = $pdo->prepare("SELECT m.*, c.nombre AS curso, c.horario, 
                              p.estado AS pago_estado, p.id AS pago_id,
                              u.nombre AS docente_nombre, u.apellido AS docente_apellido
                       FROM matriculas m
                       JOIN cursos c ON m.curso_id = c.id
                       LEFT JOIN usuarios u ON c.docente_id = u.id
                       LEFT JOIN pagos p ON p.matricula_id = m.id
                       WHERE m.estudiante_id = ?
                       ORDER BY m.fecha DESC");
$stmt->execute([$_SESSION['usuario_id']]);
$matriculas = $stmt->fetchAll();
?>

<?php include '../../includes/navbar.php'; ?>

<div class="dashboard-container">
    <div class="page-header">
        <h1>Mis Matrículas</h1>
        <a href="/eduka/modules/cursos/lista.php" class="btn-primary">+ Nueva Matrícula</a>
    </div>

    <?php if (empty($matriculas)): ?>
        <p>Aún no tienes matrículas registradas.</p>
    <?php else: ?>
        <div class="table-container">
            <table class="tabla">
                <thead>
                    <tr>
                        <th>Curso</th>
                        <th>Horario</th>
                        <th>Docente</th>
                        <th>Estado Matrícula</th>
                        <th>Estado Pago</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($matriculas as $m): ?>
                        <tr>
                            <td><?= htmlspecialchars($m['curso']) ?></td>
                            <td><?= htmlspecialchars($m['horario']) ?></td>
                            <td><?= htmlspecialchars($m['docente_nombre'] . ' ' . $m['docente_apellido']) ?></td>
                            <td><span class="estado-badge estado-<?= $m['estado'] ?>"><?= ucfirst($m['estado']) ?></span></td>
                            <td>
                                <?php if ($m['pago_estado']): ?>
                                    <span class="estado-badge estado-<?= $m['pago_estado'] ?>"><?= ucfirst($m['pago_estado']) ?></span>
                                <?php else: ?>
                                    <span class="estado-badge estado-pendiente">Sin pago</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!$m['pago_id']): ?>
                                    <a href="/eduka/modules/pagos/subir_pago.php?matricula_id=<?= $m['id'] ?>" class="btn-primary">Registrar pago</a>
                                <?php else: ?>
                                    <a href="/eduka/modules/pagos/mis_pagos.php" class="btn-secondary">Ver pago</a>
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
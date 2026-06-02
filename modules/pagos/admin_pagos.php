<?php
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

$pagos = $pdo->query("SELECT p.*, 
                             CONCAT(u.nombre, ' ', u.apellido) AS estudiante,
                             c.nombre AS curso
                      FROM pagos p
                      JOIN matriculas m ON p.matricula_id = m.id
                      JOIN usuarios u ON m.estudiante_id = u.id
                      JOIN cursos c ON m.curso_id = c.id
                      ORDER BY p.estado ASC, p.fecha DESC")->fetchAll();
?>

<?php include '../../includes/navbar.php'; ?>

<div class="dashboard-container">
    <div class="page-header">
        <h1>Gestión de Pagos</h1>
        <a href="/eduka/modules/pagos/cargar_yape.php" class="btn-primary">+ Cargar códigos Yape</a>
    </div>

    <?php if (empty($pagos)): ?>
        <p>No hay pagos registrados.</p>
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
</div>

<?php include '../../includes/footer.php'; ?>
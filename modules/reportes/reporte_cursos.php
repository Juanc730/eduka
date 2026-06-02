<?php
require_once '../../includes/header.php';
require_once '../../config/database.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'administrador') {
    header('Location: /eduka/login.php');
    exit;
}

$cursos = $pdo->query("SELECT c.*,
                              CONCAT(u.nombre, ' ', u.apellido) AS docente,
                              (c.cupos_totales - c.cupos_disponibles) AS ocupados
                       FROM cursos c
                       LEFT JOIN usuarios u ON c.docente_id = u.id
                       WHERE c.estado = 'activo'
                       ORDER BY ocupados DESC")->fetchAll();
?>

<?php include '../../includes/navbar.php'; ?>

<div class="dashboard-container">
    <div class="page-header">
        <h1>Ocupación de Cursos</h1>
        <a href="/eduka/modules/reportes/index.php" class="btn-secondary">← Volver</a>
    </div>

    <div class="table-container">
        <table class="tabla">
            <thead>
                <tr>
                    <th>Curso</th>
                    <th>Docente</th>
                    <th>Horario</th>
                    <th>Ocupados</th>
                    <th>Disponibles</th>
                    <th>Total</th>
                    <th>Ocupación</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cursos as $c): ?>
                    <?php $porcentaje = $c['cupos_totales'] > 0 ? round(($c['ocupados'] / $c['cupos_totales']) * 100) : 0; ?>
                    <tr>
                        <td><?= htmlspecialchars($c['nombre']) ?></td>
                        <td><?= htmlspecialchars($c['docente'] ?? '—') ?></td>
                        <td><?= htmlspecialchars($c['horario']) ?></td>
                        <td><?= $c['ocupados'] ?></td>
                        <td><?= $c['cupos_disponibles'] ?></td>
                        <td><?= $c['cupos_totales'] ?></td>
                        <td>
                            <div class="barra-container">
                                <div class="barra-fill <?= $porcentaje >= 90 ? 'barra-roja' : ($porcentaje >= 60 ? 'barra-amarilla' : 'barra-verde') ?>" 
                                     style="width:<?= $porcentaje ?>%"></div>
                            </div>
                            <small><?= $porcentaje ?>%</small>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
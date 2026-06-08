<?php
$page_title = 'Mis Cursos';
require_once '../../includes/header.php';
require_once '../../config/database.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'docente') {
    header('Location: /eduka/login.php');
    exit;
}

$docente_id = $_SESSION['usuario_id'];

// Obtener cursos asignados al docente
$stmt = $pdo->prepare("SELECT c.*,
                              (c.cupos_totales - c.cupos_disponibles) AS ocupados,
                              COUNT(m.id) AS total_matriculas
                       FROM cursos c
                       LEFT JOIN matriculas m ON m.curso_id = c.id AND m.estado != 'anulada'
                       WHERE c.docente_id = ? AND c.estado = 'activo'
                       GROUP BY c.id
                       ORDER BY c.nombre");
$stmt->execute([$docente_id]);
$cursos = $stmt->fetchAll();
?>

<?php include '../../includes/navbar.php'; ?>

<div class="dashboard-container">
    <h1 style="color:#1a3c5e; margin-bottom:1.5rem;">Mis Cursos</h1>

    <?php if (empty($cursos)): ?>
        <div class="aviso-pago">
            <strong>📋 Sin cursos asignados.</strong> Aún no tienes cursos asignados. Contacta al administrador.
        </div>
    <?php else: ?>

        <!-- Resumen -->
        <div class="reporte-resumen" style="margin-bottom:1.5rem;">
            <div class="resumen-card">
                <span class="resumen-num"><?= count($cursos) ?></span>
                <span class="resumen-label">Cursos asignados</span>
            </div>
            <div class="resumen-card">
                <span class="resumen-num"><?= array_sum(array_column($cursos, 'total_matriculas')) ?></span>
                <span class="resumen-label">Total estudiantes</span>
            </div>
            <div class="resumen-card">
                <span class="resumen-num"><?= array_sum(array_column($cursos, 'cupos_disponibles')) ?></span>
                <span class="resumen-label">Cupos disponibles</span>
            </div>
        </div>

        <!-- Tarjetas de cursos -->
        <div class="cards-grid">
            <?php foreach ($cursos as $c): ?>
                <?php $porcentaje = $c['cupos_totales'] > 0 ? round(($c['ocupados'] / $c['cupos_totales']) * 100) : 0; ?>
                <div class="card curso-card">
                    <h3><?= htmlspecialchars($c['nombre']) ?></h3>
                    <p class="curso-desc"><?= htmlspecialchars($c['descripcion'] ?: 'Sin descripción.') ?></p>

                    <div class="curso-info">
                        <span>🕐 <?= htmlspecialchars($c['horario']) ?></span>
                        <span>👥 <?= $c['total_matriculas'] ?> estudiante(s) matriculado(s)</span>
                        <span class="cupos <?= $c['cupos_disponibles'] == 0 ? 'sin-cupos' : '' ?>">
                            🪑 <?= $c['cupos_disponibles'] ?> / <?= $c['cupos_totales'] ?> cupos libres
                        </span>
                    </div>

                    <!-- Barra de ocupación -->
                    <div style="margin: 0.8rem 0 0.3rem;">
                        <div class="barra-container">
                            <div class="barra-fill <?= $porcentaje >= 90 ? 'barra-roja' : ($porcentaje >= 60 ? 'barra-amarilla' : 'barra-verde') ?>"
                                 style="width:<?= $porcentaje ?>%"></div>
                        </div>
                        <small style="color:#666;">Ocupación: <?= $porcentaje ?>%</small>
                    </div>

                    <a href="/eduka/modules/reportes/reporte_matriculas.php?curso_id=<?= $c['id'] ?>" 
                       class="btn-secondary" style="margin-top:0.8rem; display:inline-block;">
                        Ver estudiantes
                    </a>
                </div>
            <?php endforeach; ?>
        </div>

    <?php endif; ?>
</div>

<?php include '../../includes/footer.php'; ?>
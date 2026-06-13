<?php 
$page_title = 'Cursos';
require_once '../../includes/header.php';
require_once '../../config/database.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: /eduka/login.php');
    exit;
}

$rol = $_SESSION['rol'];

// Obtener todos los cursos activos
$stmt = $pdo->query("SELECT c.*, u.nombre AS docente_nombre, u.apellido AS docente_apellido 
                     FROM cursos c 
                     LEFT JOIN usuarios u ON c.docente_id = u.id 
                     WHERE c.estado = 'activo'");
$cursos = $stmt->fetchAll();
?>


<?php include '../../includes/navbar.php'; ?>

<div class="dashboard-container">
    <div class="page-header">
        <h1>Cursos Disponibles</h1>
        <?php if ($rol === 'administrador'): ?>
            <a href="/eduka/modules/cursos/crear.php" class="btn-primary">+ Nuevo Curso</a>
        <?php endif; ?>
    </div>

    <?php if (isset($_GET['msg'])): ?>
        <p class="success"><?= htmlspecialchars($_GET['msg']) ?></p>
    <?php endif; ?>
    <?php if (isset($_GET['error'])): ?>
        <p class="error"><?= htmlspecialchars($_GET['error']) ?></p>
    <?php endif; ?>

    <div class="cards-grid">
        <?php if (empty($cursos)): ?>
            <p>No hay cursos disponibles por el momento.</p>
        <?php else: ?>
            <?php foreach ($cursos as $curso): ?>
                <div class="card curso-card">
                    <h3><?= htmlspecialchars($curso['nombre']) ?></h3>
                    <p class="curso-desc"><?= htmlspecialchars($curso['descripcion']) ?></p>
                    <div class="curso-info">
                        <span>👨‍🏫 <?= htmlspecialchars($curso['docente_nombre'] . ' ' . $curso['docente_apellido']) ?></span>
                        <span>🕐 <?= htmlspecialchars($curso['horario']) ?></span>
                        <?php
                        $disponibles  = $curso['cupos_disponibles'];
                        $totales      = $curso['cupos_totales'];
                        $porcentaje   = $totales > 0 ? ($disponibles / $totales) * 100 : 0;
                        
                        if ($disponibles == 0) {
                            $cupo_clase = 'cupos-agotado';
                            $cupo_icono = '🔴';
                            $cupo_texto = 'Sin cupos disponibles';
                        } elseif ($porcentaje <= 25) {
                            $cupo_clase = 'cupos-critico';
                            $cupo_icono = '🟠';
                            $cupo_texto = "¡Solo $disponibles cupo(s) disponible(s)!";
                        } elseif ($porcentaje <= 50) {
                            $cupo_clase = 'cupos-medio';
                            $cupo_icono = '🟡';
                            $cupo_texto = "$disponibles / $totales cupos";
                        } else {
                            $cupo_clase = 'cupos-ok';
                            $cupo_icono = '🟢';
                            $cupo_texto = "$disponibles / $totales cupos";
                        }
                        ?>
                        <div class="cupo-indicador <?= $cupo_clase ?>">
                            <span><?= $cupo_icono ?> <?= $cupo_texto ?></span>
                            <div class="cupo-barra-container">
                                <div class="cupo-barra-fill" style="width: <?= $porcentaje ?>%"></div>
                            </div>
                        </div>
                    </div>

                    <?php if ($rol === 'estudiante'): ?>
                        <?php if ($curso['cupos_disponibles'] > 0): ?>
                            <a href="/eduka/modules/matricula/matricular.php?curso_id=<?= $curso['id'] ?>" class="btn-primary">Matricularme</a>
                        <?php else: ?>
                            <a href="/eduka/modules/matricula/lista_espera.php?curso_id=<?= $curso['id'] ?>" class="btn-secondary">Unirme a lista de espera</a>
                        <?php endif; ?>
                    <?php elseif ($rol === 'administrador'): ?>
                        <div class="btn-group">
                            <a href="/eduka/modules/cursos/editar.php?id=<?= $curso['id'] ?>" class="btn-secondary">Editar</a>
                            <a href="/eduka/modules/cursos/eliminar.php?id=<?= $curso['id'] ?>" class="btn-danger" onclick="return confirm('¿Eliminar este curso?')">Eliminar</a>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
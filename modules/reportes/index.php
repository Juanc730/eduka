<?php
$page_title = 'Reportes';
require_once '../../includes/header.php';
require_once '../../config/database.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: /eduka/login.php');
    exit;
}

$rol = $_SESSION['rol'];
?>

<?php include '../../includes/navbar.php'; ?>

<div class="dashboard-container">
    <h1 style="color:#1a3c5e; margin-bottom:1.5rem;">Reportes</h1>

    <div class="cards-grid">

        <?php if ($rol === 'administrador'): ?>
            <a href="/eduka/modules/reportes/reporte_matriculas.php" class="card">
                <div class="card-icon">📋</div>
                <h3>Matrículas por curso</h3>
                <p>Ver cuántos estudiantes hay en cada curso y su estado</p>
            </a>
            <a href="/eduka/modules/reportes/reporte_pagos.php" class="card">
                <div class="card-icon">💳</div>
                <h3>Estado de pagos</h3>
                <p>Resumen de pagos aprobados, pendientes y rechazados</p>
            </a>
            <a href="/eduka/modules/reportes/reporte_cursos.php" class="card">
                <div class="card-icon">📚</div>
                <h3>Ocupación de cursos</h3>
                <p>Ver cupos disponibles y ocupados por curso</p>
            </a>

        <?php elseif ($rol === 'docente'): ?>
            <a href="/eduka/modules/reportes/reporte_matriculas.php" class="card">
                <div class="card-icon">📋</div>
                <h3>Mis estudiantes</h3>
                <p>Ver los estudiantes matriculados en tus cursos</p>
            </a>

        <?php endif; ?>

    </div>
</div>

<?php include '../../includes/footer.php'; ?>
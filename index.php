<?php
require_once 'includes/header.php';
require_once 'config/database.php';

// Si no está logueado, redirigir al login
if (!isset($_SESSION['usuario_id'])) {
    header('Location: /eduka/login.php');
    exit;
}

$rol = $_SESSION['rol'];
?>

<?php include 'includes/navbar.php'; ?>

<div class="dashboard-container">
    <h1>Bienvenido, <?= htmlspecialchars($_SESSION['nombre']) ?></h1>
    <p class="rol-badge rol-<?= $rol ?>"><?= ucfirst($rol) ?></p>

    <div class="cards-grid">

        <?php if ($rol === 'estudiante'): ?>
            <a href="/eduka/modules/cursos/lista.php" class="card">
                <div class="card-icon">📚</div>
                <h3>Ver Cursos</h3>
                <p>Consulta los cursos disponibles y matricúlate</p>
            </a>
            <a href="/eduka/modules/matricula/mis_matriculas.php" class="card">
                <div class="card-icon">📋</div>
                <h3>Mis Matrículas</h3>
                <p>Revisa el estado de tus inscripciones</p>
            </a>
            <a href="/eduka/modules/pagos/mis_pagos.php" class="card">
                <div class="card-icon">💳</div>
                <h3>Mis Pagos</h3>
                <p>Gestiona y sube tus comprobantes de pago</p>
            </a>

        <?php elseif ($rol === 'administrador'): ?>
            <a href="/eduka/modules/cursos/lista.php" class="card">
                <div class="card-icon">📚</div>
                <h3>Gestión de Cursos</h3>
                <p>Crear, editar y eliminar cursos</p>
            </a>
            <a href="/eduka/modules/admin/usuarios.php" class="card">
                <div class="card-icon">👥</div>
                <h3>Usuarios</h3>
                <p>Administrar estudiantes y docentes</p>
            </a>
            <a href="/eduka/modules/admin/matriculas.php" class="card">
                <div class="card-icon">📋</div>
                <h3>Matrículas</h3>
                <p>Ver y gestionar todas las inscripciones</p>
            </a>
            <a href="/eduka/modules/pagos/admin_pagos.php" class="card">
                <div class="card-icon">💳</div>
                <h3>Pagos</h3>
                <p>Aprobar o rechazar comprobantes</p>
            </a>
            <a href="/eduka/modules/reportes/index.php" class="card">
                <div class="card-icon">📊</div>
                <h3>Reportes</h3>
                <p>Generar reportes del sistema</p>
            </a>

        <?php elseif ($rol === 'docente'): ?>
            <a href="/eduka/modules/cursos/mis_cursos.php" class="card">
                <div class="card-icon">📚</div>
                <h3>Mis Cursos</h3>
                <p>Consulta los cursos que tienes asignados</p>
            </a>
            <a href="/eduka/modules/reportes/index.php" class="card">
                <div class="card-icon">📊</div>
                <h3>Reportes</h3>
                <p>Ver reportes de tus cursos</p>
            </a>

        <?php endif; ?>

    </div>
</div>

<?php include 'includes/footer.php'; ?>
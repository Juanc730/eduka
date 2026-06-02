<?php
require_once '../../includes/header.php';
require_once '../../config/database.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'administrador') {
    header('Location: /eduka/login.php');
    exit;
}

$usuarios = $pdo->query("SELECT u.*, r.nombre AS rol 
                         FROM usuarios u 
                         JOIN roles r ON u.rol_id = r.id 
                         ORDER BY u.rol_id, u.nombre")->fetchAll();
?>

<?php include '../../includes/navbar.php'; ?>

<div class="dashboard-container">
    <div class="page-header">
        <h1>Gestión de Usuarios</h1>
        <a href="/eduka/modules/admin/crear_usuario.php" class="btn-primary">+ Nuevo Usuario</a>
    </div>

    <?php if (isset($_GET['msg'])): ?>
        <p class="success"><?= htmlspecialchars($_GET['msg']) ?></p>
    <?php endif; ?>
    <?php if (isset($_GET['error'])): ?>
        <p class="error"><?= htmlspecialchars($_GET['error']) ?></p>
    <?php endif; ?>

    <div class="table-container">
        <table class="tabla">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nombre</th>
                    <th>Correo</th>
                    <th>Rol</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($usuarios as $u): ?>
                    <tr>
                        <td><?= $u['id'] ?></td>
                        <td><?= htmlspecialchars($u['nombre'] . ' ' . $u['apellido']) ?></td>
                        <td><?= htmlspecialchars($u['email']) ?></td>
                        <td>
                            <span class="rol-badge rol-<?= $u['rol'] ?>"><?= ucfirst($u['rol']) ?></span>
                        </td>
                        <td>
                            <?php if ($u['activo']): ?>
                                <span class="badge-activo">Activo</span>
                            <?php else: ?>
                                <span class="badge-inactivo">Inactivo</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="btn-group">
                                <a href="/eduka/modules/admin/editar_usuario.php?id=<?= $u['id'] ?>" class="btn-secondary">Editar</a>
                                <?php if ($u['id'] !== $_SESSION['usuario_id']): ?>
                                    <a href="/eduka/modules/admin/toggle_usuario.php?id=<?= $u['id'] ?>" 
                                       class="<?= $u['activo'] ? 'btn-danger' : 'btn-primary' ?>"
                                       onclick="return confirm('¿Confirmas esta acción?')">
                                        <?= $u['activo'] ? 'Desactivar' : 'Activar' ?>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
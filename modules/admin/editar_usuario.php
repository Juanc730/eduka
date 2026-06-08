<?php
$page_title = 'Editar Usuario';
require_once '../../includes/header.php';
require_once '../../config/database.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'administrador') {
    header('Location: /eduka/login.php');
    exit;
}

$id = (int)$_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->execute([$id]);
$usuario = $stmt->fetch();

if (!$usuario) {
    header('Location: /eduka/modules/admin/usuarios.php?error=Usuario no encontrado');
    exit;
}

$roles = $pdo->query("SELECT * FROM roles")->fetchAll();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre   = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $email    = trim($_POST['email']);
    $rol_id   = (int)$_POST['rol_id'];
    $password = $_POST['password'];

    if (empty($nombre) || empty($apellido) || empty($email) || empty($rol_id)) {
        $error = 'Todos los campos son obligatorios.';
    } else {
        // Verificar email duplicado en otro usuario
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ? AND id != ?");
        $stmt->execute([$email, $id]);
        if ($stmt->fetch()) {
            $error = 'El correo ya está en uso por otro usuario.';
        } else {
            if (!empty($password)) {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE usuarios SET nombre=?, apellido=?, email=?, rol_id=?, password=? WHERE id=?");
                $stmt->execute([$nombre, $apellido, $email, $rol_id, $hash, $id]);
            } else {
                $stmt = $pdo->prepare("UPDATE usuarios SET nombre=?, apellido=?, email=?, rol_id=? WHERE id=?");
                $stmt->execute([$nombre, $apellido, $email, $rol_id, $id]);
            }
            header('Location: /eduka/modules/admin/usuarios.php?msg=Usuario actualizado correctamente');
            exit;
        }
    }
}
?>

<?php include '../../includes/navbar.php'; ?>

<div class="dashboard-container">
    <div class="page-header">
        <h1>Editar Usuario</h1>
        <a href="/eduka/modules/admin/usuarios.php" class="btn-secondary">← Volver</a>
    </div>

    <?php if ($error): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <div class="form-card">
        <form method="POST">
            <label>Nombre *</label>
            <input type="text" name="nombre" value="<?= htmlspecialchars($usuario['nombre']) ?>" required>

            <label>Apellido *</label>
            <input type="text" name="apellido" value="<?= htmlspecialchars($usuario['apellido']) ?>" required>

            <label>Correo electrónico *</label>
            <input type="email" name="email" value="<?= htmlspecialchars($usuario['email']) ?>" required>

            <label>Nueva contraseña <small>(dejar vacío para no cambiarla)</small></label>
            <input type="password" name="password" minlength="6">

            <label>Rol *</label>
            <select name="rol_id" required>
                <?php foreach ($roles as $r): ?>
                    <option value="<?= $r['id'] ?>" <?= $usuario['rol_id'] == $r['id'] ? 'selected' : '' ?>>
                        <?= ucfirst($r['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit" class="btn-primary">Guardar Cambios</button>
        </form>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
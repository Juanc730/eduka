<?php
$page_title = 'Nuevo Usuario';
require_once '../../includes/header.php';
require_once '../../config/database.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'administrador') {
    header('Location: /eduka/login.php');
    exit;
}

$roles = $pdo->query("SELECT * FROM roles")->fetchAll();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre   = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $email    = trim($_POST['email']);
    $password = $_POST['password'];
    $rol_id   = (int)$_POST['rol_id'];

    if (empty($nombre) || empty($apellido) || empty($email) || empty($password) || empty($rol_id)) {
        $error = 'Todos los campos son obligatorios.';
    } else {
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'El correo ya está registrado.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, apellido, email, password, rol_id) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$nombre, $apellido, $email, $hash, $rol_id]);
            header('Location: /eduka/modules/admin/usuarios.php?msg=Usuario creado exitosamente');
            exit;
        }
    }
}
?>

<?php include '../../includes/navbar.php'; ?>

<div class="dashboard-container">
    <div class="page-header">
        <h1>Nuevo Usuario</h1>
        <a href="/eduka/modules/admin/usuarios.php" class="btn-secondary">← Volver</a>
    </div>

    <?php if ($error): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <div class="form-card">
        <form method="POST">
            <label>Nombre *</label>
            <input type="text" name="nombre" required>

            <label>Apellido *</label>
            <input type="text" name="apellido" required>

            <label>Correo electrónico *</label>
            <input type="email" name="email" required>

            <label>Contraseña *</label>
            <input type="password" name="password" required minlength="6">

            <label>Rol *</label>
            <select name="rol_id" required>
                <option value="">-- Selecciona un rol --</option>
                <?php foreach ($roles as $r): ?>
                    <option value="<?= $r['id'] ?>"><?= ucfirst($r['nombre']) ?></option>
                <?php endforeach; ?>
            </select>

            <button type="submit" class="btn-primary">Crear Usuario</button>
        </form>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
<?php
session_start();
require_once '../../config/database.php';
$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre   = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $email    = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm  = $_POST['confirm_password'];

    if ($password !== $confirm) {
        $error = 'Las contraseñas no coinciden.';
    } else {
        // Verificar si el email ya existe
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'El correo ya está registrado.';
        } else {
            $hash   = password_hash($password, PASSWORD_DEFAULT);
            $rol_id = 2; // rol estudiante por defecto
            $stmt   = $pdo->prepare("INSERT INTO usuarios (nombre, apellido, email, password, rol_id) 
                                     VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$nombre, $apellido, $email, $hash, $rol_id]);
            $success = 'Cuenta creada exitosamente. <a href="/eduka/login.php">Inicia sesión</a>';
        }
    }
}
?>
<?php $page_title = 'Crear Cuenta'; ?>
<?php include '../../includes/header.php'; ?>

<div class="auth-container">
    <div class="auth-card">
        <h2>Crear Cuenta</h2>
        <?php if ($error): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
        <?php if ($success): ?>
            <p class="success"><?= $success ?></p>
        <?php endif; ?>
        <form method="POST">
            <label>Nombre</label>
            <input type="text" name="nombre" required>
            <label>Apellido</label>
            <input type="text" name="apellido" required>
            <label>Correo electrónico</label>
            <input type="email" name="email" required>
            <label>Contraseña</label>
            <input type="password" name="password" required minlength="6">
            <label>Confirmar contraseña</label>
            <input type="password" name="confirm_password" required>
            <button type="submit">Registrarse</button>
        </form>
        <p>¿Ya tienes cuenta? <a href="/eduka/login.php">Inicia sesión</a></p>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
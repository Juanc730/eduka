<?php
session_start();
if (isset($_SESSION['usuario_id'])) {
    header('Location: /eduka/index.php');
    exit;
}
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'config/database.php';
    $email    = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT u.*, r.nombre AS rol 
                           FROM usuarios u 
                           JOIN roles r ON u.rol_id = r.id 
                           WHERE u.email = ? AND u.activo = 1");
    $stmt->execute([$email]);
    $usuario = $stmt->fetch();

    if ($usuario && password_verify($password, $usuario['password'])) {
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['nombre']     = $usuario['nombre'];
        $_SESSION['rol']        = $usuario['rol'];
        header('Location: /eduka/index.php');
        exit;
    } else {
        $error = 'Correo o contraseña incorrectos.';
    }
}
?>
<?php include 'includes/header.php'; ?>

<div class="auth-container">
    <div class="auth-card">
        <h2>Iniciar Sesión</h2>
        <?php if ($error): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
        <form method="POST">
            <label>Correo electrónico</label>
            <input type="email" name="email" required>
            <label>Contraseña</label>
            <input type="password" name="password" required>
            <button type="submit">Ingresar</button>
        </form>
        <p>¿No tienes cuenta? <a href="/eduka/modules/auth/registro.php">Regístrate aquí</a></p>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
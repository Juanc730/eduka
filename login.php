<?php
$page_title = 'Iniciar Sesión';
require_once 'includes/header.php';

if (isset($_SESSION['usuario_id'])) {
    header('Location: /eduka/index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'config/database.php';
    $email    = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = 'Por favor completa todos los campos.';
    } else {
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
}
?>

<?php include 'includes/navbar.php'; ?>

<div class="auth-container">
    <div class="auth-card">
        <h2>Iniciar Sesión</h2>

        <?php if (isset($_GET['error'])): ?>
            <p class="error"><?= htmlspecialchars($_GET['error']) ?></p>
        <?php endif; ?>
        <?php if ($error): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <form method="POST" id="form-login" novalidate>
            <label>Correo electrónico</label>
            <input type="email" name="email" id="email" 
                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" 
                   placeholder="tucorreo@ejemplo.com">
            <p class="campo-error" id="error-email"></p>

            <label>Contraseña</label>
            <div class="input-password">
                <input type="password" name="password" id="password" placeholder="Tu contraseña">
                <button type="button" class="toggle-pass" onclick="togglePassword('password', 'icon-pass')">
                    <span id="icon-pass">👁️</span>
                </button>
            </div>
            <p class="campo-error" id="error-password"></p>

            <button type="submit" class="btn-primary" id="btn-login" style="margin-top:1rem; width:100%;">
                Ingresar
            </button>
        </form>
        <p>¿No tienes cuenta? <a href="/eduka/modules/auth/registro.php">Regístrate aquí</a></p>
    </div>
</div>

<script>
function togglePassword(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon  = document.getElementById(iconId);
    if (input.type === 'password') {
        input.type = 'text';
        icon.textContent = '🙈';
    } else {
        input.type = 'password';
        icon.textContent = '👁️';
    }
}

document.getElementById('form-login').addEventListener('submit', function (e) {
    let valido = true;

    const email    = document.getElementById('email');
    const password = document.getElementById('password');
    const errorEmail    = document.getElementById('error-email');
    const errorPassword = document.getElementById('error-password');

    // Limpiar errores previos
    errorEmail.textContent    = '';
    errorPassword.textContent = '';
    email.classList.remove('input-error');
    password.closest('.input-password').classList.remove('input-error');

    // Validar email
    if (email.value.trim() === '') {
        errorEmail.textContent = 'El correo es obligatorio.';
        email.classList.add('input-error');
        valido = false;
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value.trim())) {
        errorEmail.textContent = 'Ingresa un correo válido.';
        email.classList.add('input-error');
        valido = false;
    }

    // Validar password
    if (password.value === '') {
        errorPassword.textContent = 'La contraseña es obligatoria.';
        password.closest('.input-password').classList.add('input-error');
        valido = false;
    }

    if (!valido) {
        e.preventDefault();
        return;
    }

    // Indicador de carga
    const btn = document.getElementById('btn-login');
    btn.textContent = 'Verificando...';
    btn.disabled    = true;
});
</script>

<?php include 'includes/footer.php'; ?>
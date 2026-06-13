<?php
$page_title = 'Crear Cuenta';
if (session_status() === PHP_SESSION_NONE) session_start();
require_once '../../config/database.php';

$error   = '';
$success = '';

function validar_password($password) {
    if (strlen($password) < 8)
        return 'La contraseña debe tener al menos 8 caracteres.';
    if (!preg_match('/[A-Z]/', $password))
        return 'La contraseña debe tener al menos una letra mayúscula.';
    if (!preg_match('/[a-z]/', $password))
        return 'La contraseña debe tener al menos una letra minúscula.';
    if (!preg_match('/[0-9]/', $password))
        return 'La contraseña debe tener al menos un número.';
    if (!preg_match('/[\@\#\$\%\^\&\*\!\?\.\,\-\_]/', $password))
        return 'La contraseña debe tener al menos un carácter especial (@, #, $, %, etc.).';
    return '';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre   = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $email    = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm  = $_POST['confirm_password'];

    $error_pass = validar_password($password);

    if ($password !== $confirm) {
        $error = 'Las contraseñas no coinciden.';
    } elseif ($error_pass) {
        $error = $error_pass;
    } else {
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'El correo ya está registrado.';
        } else {
            $hash   = password_hash($password, PASSWORD_DEFAULT);
            $rol_id = 2;
            $stmt   = $pdo->prepare("INSERT INTO usuarios (nombre, apellido, email, password, rol_id) 
                                     VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$nombre, $apellido, $email, $hash, $rol_id]);
            $success = 'Cuenta creada exitosamente. <a href="/eduka/login.php">Inicia sesión</a>';
        }
    }
}
?>
<?php include '../../includes/header.php'; ?>
<?php include '../../includes/navbar.php'; ?>

<div class="auth-container">
    <div class="auth-card">
        <h2>Crear Cuenta</h2>

        <?php if ($error): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
        <?php if ($success): ?>
            <p class="success"><?= $success ?></p>
        <?php endif; ?>

        <form method="POST" id="form-registro">
            <label>Nombre</label>
            <input type="text" name="nombre" required value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>">

            <label>Apellido</label>
            <input type="text" name="apellido" required value="<?= htmlspecialchars($_POST['apellido'] ?? '') ?>">

            <label>Correo electrónico</label>
            <input type="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">

            <label>Contraseña</label>
            <div class="input-password">
                <input type="password" name="password" id="password" required>
                <button type="button" class="toggle-pass" onclick="togglePassword('password', 'icon-pass')">
                    <span id="icon-pass">👁️</span>
                </button>
            </div>

            <!-- Indicador de requisitos -->
            <ul class="requisitos" id="requisitos">
                <li id="req-len">Mínimo 8 caracteres</li>
                <li id="req-may">Al menos una mayúscula</li>
                <li id="req-min">Al menos una minúscula</li>
                <li id="req-num">Al menos un número</li>
                <li id="req-esp">Al menos un carácter especial (@, #, $, %...)</li>
            </ul>

            <label>Confirmar contraseña</label>
            <div class="input-password">
                <input type="password" name="confirm_password" id="confirm_password" required>
                <button type="button" class="toggle-pass" onclick="togglePassword('confirm_password', 'icon-confirm')">
                    <span id="icon-confirm">👁️</span>
                </button>
            </div>
            <p id="msg-confirm" style="font-size:0.85rem; margin-top:0.3rem;"></p>

            <button type="submit" class="btn-primary" style="margin-top:1rem; width:100%;">Registrarse</button>
        </form>
        <p>¿Ya tienes cuenta? <a href="/eduka/login.php">Inicia sesión</a></p>
    </div>
</div>

<script>
// Mostrar / ocultar contraseña
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

// Validación en tiempo real
const passInput    = document.getElementById('password');
const confirmInput = document.getElementById('confirm_password');
const msgConfirm   = document.getElementById('msg-confirm');

const requisitos = {
    'req-len': /.{8,}/,
    'req-may': /[A-Z]/,
    'req-min': /[a-z]/,
    'req-num': /[0-9]/,
    'req-esp': /[\@\#\$\%\^\&\*\!\?\.\,\-\_]/
};

passInput.addEventListener('input', function () {
    const val = this.value;
    for (const [id, regex] of Object.entries(requisitos)) {
        const el = document.getElementById(id);
        if (regex.test(val)) {
            el.classList.add('req-ok');
            el.classList.remove('req-fail');
        } else {
            el.classList.add('req-fail');
            el.classList.remove('req-ok');
        }
    }
    validarConfirm();
});

confirmInput.addEventListener('input', validarConfirm);

function validarConfirm() {
    if (confirmInput.value === '') {
        msgConfirm.textContent = '';
        return;
    }
    if (passInput.value === confirmInput.value) {
        msgConfirm.textContent = '✅ Las contraseñas coinciden';
        msgConfirm.style.color = '#27ae60';
    } else {
        msgConfirm.textContent = '❌ Las contraseñas no coinciden';
        msgConfirm.style.color = '#e74c3c';
    }
}
</script>

<?php include '../../includes/footer.php'; ?>
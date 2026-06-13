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
    csrf_verificar();
    $nombre   = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $email    = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm  = $_POST['confirm_password'];
    $rol_id   = (int)$_POST['rol_id'];

    $error_pass = validar_password($password);

    if (empty($nombre) || empty($apellido) || empty($email) || empty($rol_id)) {
        $error = 'Todos los campos son obligatorios.';
    } elseif ($password !== $confirm) {
        $error = 'Las contraseñas no coinciden.';
    } elseif ($error_pass) {
        $error = $error_pass;
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
        <p class="error" style="margin-bottom:1rem;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <div class="form-card">
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= csrf_generar() ?>">
            <label>Nombre *</label>
            <input type="text" name="nombre" required value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>">

            <label>Apellido *</label>
            <input type="text" name="apellido" required value="<?= htmlspecialchars($_POST['apellido'] ?? '') ?>">

            <label>Correo electrónico *</label>
            <input type="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">

            <label>Contraseña *</label>
            <div class="input-password">
                <input type="password" name="password" id="password" required>
                <button type="button" class="toggle-pass" onclick="togglePassword('password', 'icon-pass')">
                    <span id="icon-pass">👁️</span>
                </button>
            </div>

            <ul class="requisitos">
                <li id="req-len">Mínimo 8 caracteres</li>
                <li id="req-may">Al menos una mayúscula</li>
                <li id="req-min">Al menos una minúscula</li>
                <li id="req-num">Al menos un número</li>
                <li id="req-esp">Al menos un carácter especial (@, #, $, %...)</li>
            </ul>

            <label>Confirmar contraseña *</label>
            <div class="input-password">
                <input type="password" name="confirm_password" id="confirm_password" required>
                <button type="button" class="toggle-pass" onclick="togglePassword('confirm_password', 'icon-confirm')">
                    <span id="icon-confirm">👁️</span>
                </button>
            </div>
            <p id="msg-confirm" style="font-size:0.85rem; margin-top:0.3rem;"></p>

            <label>Rol *</label>
            <select name="rol_id" required>
                <option value="">-- Selecciona un rol --</option>
                <?php foreach ($roles as $r): ?>
                    <option value="<?= $r['id'] ?>" <?= (isset($_POST['rol_id']) && $_POST['rol_id'] == $r['id']) ? 'selected' : '' ?>>
                        <?= ucfirst($r['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit" class="btn-primary" style="margin-top:1.2rem;">Crear Usuario</button>
        </form>
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
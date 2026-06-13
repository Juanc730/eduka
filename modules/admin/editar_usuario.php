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
    $rol_id   = (int)$_POST['rol_id'];
    $password = $_POST['password'];
    $confirm  = $_POST['confirm_password'];

    if (empty($nombre) || empty($apellido) || empty($email) || empty($rol_id)) {
        $error = 'Todos los campos son obligatorios.';
    } elseif (!empty($password) && $password !== $confirm) {
        $error = 'Las contraseñas no coinciden.';
    } elseif (!empty($password)) {
        $error_pass = validar_password($password);
        if ($error_pass) $error = $error_pass;
    }

    if (!$error) {
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
        <p class="error" style="margin-bottom:1rem;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <div class="form-card">
        <form method="POST">
            <label>Nombre *</label>
            <input type="text" name="nombre" required value="<?= htmlspecialchars($usuario['nombre']) ?>">

            <label>Apellido *</label>
            <input type="text" name="apellido" required value="<?= htmlspecialchars($usuario['apellido']) ?>">

            <label>Correo electrónico *</label>
            <input type="email" name="email" required value="<?= htmlspecialchars($usuario['email']) ?>">

            <label>Nueva contraseña <small>(dejar vacío para no cambiarla)</small></label>
            <div class="input-password">
                <input type="password" name="password" id="password">
                <button type="button" class="toggle-pass" onclick="togglePassword('password', 'icon-pass')">
                    <span id="icon-pass">👁️</span>
                </button>
            </div>

            <ul class="requisitos" id="requisitos" style="display:none;">
                <li id="req-len">Mínimo 8 caracteres</li>
                <li id="req-may">Al menos una mayúscula</li>
                <li id="req-min">Al menos una minúscula</li>
                <li id="req-num">Al menos un número</li>
                <li id="req-esp">Al menos un carácter especial (@, #, $, %...)</li>
            </ul>

            <label>Confirmar nueva contraseña <small>(solo si cambias la contraseña)</small></label>
            <div class="input-password">
                <input type="password" name="confirm_password" id="confirm_password">
                <button type="button" class="toggle-pass" onclick="togglePassword('confirm_password', 'icon-confirm')">
                    <span id="icon-confirm">👁️</span>
                </button>
            </div>
            <p id="msg-confirm" style="font-size:0.85rem; margin-top:0.3rem;"></p>

            <label>Rol *</label>
            <select name="rol_id" required>
                <?php foreach ($roles as $r): ?>
                    <option value="<?= $r['id'] ?>" <?= $usuario['rol_id'] == $r['id'] ? 'selected' : '' ?>>
                        <?= ucfirst($r['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit" class="btn-primary" style="margin-top:1.2rem;">Guardar Cambios</button>
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
const listaReq     = document.getElementById('requisitos');

const requisitos = {
    'req-len': /.{8,}/,
    'req-may': /[A-Z]/,
    'req-min': /[a-z]/,
    'req-num': /[0-9]/,
    'req-esp': /[\@\#\$\%\^\&\*\!\?\.\,\-\_]/
};

passInput.addEventListener('input', function () {
    const val = this.value;

    // Mostrar u ocultar lista de requisitos
    listaReq.style.display = val.length > 0 ? 'block' : 'none';

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
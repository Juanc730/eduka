<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../middleware/auth_middleware.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    json_response([], 200);
}

if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    json_error('Método no permitido', 405);
}

verificar_rol(['administrador']);

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
        return 'La contraseña debe tener al menos un carácter especial.';
    return '';
}

$input = json_decode(file_get_contents('php://input'), true);

$id       = (int)($input['id'] ?? 0);
$nombre   = trim($input['nombre'] ?? '');
$apellido = trim($input['apellido'] ?? '');
$email    = trim($input['email'] ?? '');
$rol_id   = (int)($input['rol_id'] ?? 0);
$password = $input['password'] ?? '';
$confirm  = $input['confirm_password'] ?? '';

if (empty($id) || empty($nombre) || empty($apellido) || empty($email) || empty($rol_id)) {
    json_error('Todos los campos son obligatorios.', 400);
}

$stmt = $pdo->prepare("SELECT id FROM usuarios WHERE id = ?");
$stmt->execute([$id]);
if (!$stmt->fetch()) {
    json_error('Usuario no encontrado.', 404);
}

if (!empty($password)) {
    if ($password !== $confirm) {
        json_error('Las contraseñas no coinciden.', 400);
    }
    $error_pass = validar_password($password);
    if ($error_pass) {
        json_error($error_pass, 400);
    }
}

$stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ? AND id != ?");
$stmt->execute([$email, $id]);
if ($stmt->fetch()) {
    json_error('El correo ya está en uso por otro usuario.', 409);
}

if (!empty($password)) {
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE usuarios SET nombre=?, apellido=?, email=?, rol_id=?, password=? WHERE id=?");
    $stmt->execute([$nombre, $apellido, $email, $rol_id, $hash, $id]);
} else {
    $stmt = $pdo->prepare("UPDATE usuarios SET nombre=?, apellido=?, email=?, rol_id=? WHERE id=?");
    $stmt->execute([$nombre, $apellido, $email, $rol_id, $id]);
}

json_success([], 'Usuario actualizado correctamente.');
?>
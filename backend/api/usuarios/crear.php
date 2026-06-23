<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../helpers/validators.php';
require_once __DIR__ . '/../../middleware/auth_middleware.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    json_response([], 200);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('Método no permitido', 405);
}

verificar_rol(['administrador']);


$input = json_decode(file_get_contents('php://input'), true);

$nombre   = trim($input['nombre'] ?? '');
$apellido = trim($input['apellido'] ?? '');
$email    = trim($input['email'] ?? '');
$password = $input['password'] ?? '';
$confirm  = $input['confirm_password'] ?? '';
$rol_id   = (int)($input['rol_id'] ?? 0);

if (empty($nombre) || empty($apellido) || empty($email) || empty($password) || empty($rol_id)) {
    json_error('Todos los campos son obligatorios.', 400);
}

if ($password !== $confirm) {
    json_error('Las contraseñas no coinciden.', 400);
}

$error_pass = validar_password($password);
if ($error_pass) {
    json_error($error_pass, 400);
}

$stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
$stmt->execute([$email]);
if ($stmt->fetch()) {
    json_error('El correo ya está registrado.', 409);
}

$hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $pdo->prepare("INSERT INTO usuarios (nombre, apellido, email, password, rol_id) VALUES (?, ?, ?, ?, ?)");
$stmt->execute([$nombre, $apellido, $email, $hash, $rol_id]);

json_success(['id' => $pdo->lastInsertId()], 'Usuario creado exitosamente.');
?>
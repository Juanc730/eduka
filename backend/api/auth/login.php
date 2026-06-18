<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../helpers/jwt.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    json_response([], 200);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('Método no permitido', 405);
}

$input = json_decode(file_get_contents('php://input'), true);

$email    = trim($input['email'] ?? '');
$password = $input['password'] ?? '';

if (empty($email) || empty($password)) {
    json_error('Correo y contraseña son obligatorios.', 400);
}

// Verificar límite de intentos (igual que antes)
$ip     = $_SERVER['REMOTE_ADDR'];
$limite = 5;
$tiempo = 10;

$stmt = $pdo->prepare("SELECT COUNT(*) AS intentos FROM login_intentos 
                       WHERE ip = ? AND fecha > DATE_SUB(NOW(), INTERVAL ? MINUTE)");
$stmt->execute([$ip, $tiempo]);
$intentos_actuales = $stmt->fetch()['intentos'];

if ($intentos_actuales >= $limite) {
    json_error("Demasiados intentos fallidos. Espera $tiempo minutos.", 429);
}

$stmt = $pdo->prepare("SELECT u.*, r.nombre AS rol 
                       FROM usuarios u 
                       JOIN roles r ON u.rol_id = r.id 
                       WHERE u.email = ? AND u.activo = 1");
$stmt->execute([$email]);
$usuario = $stmt->fetch();

if (!$usuario || !password_verify($password, $usuario['password'])) {
    $pdo->prepare("INSERT INTO login_intentos (email, ip) VALUES (?, ?)")->execute([$email, $ip]);

    $restantes = $limite - ($intentos_actuales + 1);
    if ($restantes <= 0) {
        json_error("Demasiados intentos fallidos. Espera $tiempo minutos.", 429);
    }
    json_error("Correo o contraseña incorrectos. Te quedan $restantes intento(s).", 401);
}

// Login exitoso: limpiar intentos
$pdo->prepare("DELETE FROM login_intentos WHERE ip = ?")->execute([$ip]);

// Generar JWT
$token = jwt_generar([
    'usuario_id' => $usuario['id'],
    'nombre'     => $usuario['nombre'],
    'rol'        => $usuario['rol']
]);

json_success([
    'token'   => $token,
    'usuario' => [
        'id'     => $usuario['id'],
        'nombre' => $usuario['nombre'],
        'email'  => $usuario['email'],
        'rol'    => $usuario['rol']
    ]
], 'Login exitoso');
?>
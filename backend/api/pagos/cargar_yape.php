<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../middleware/auth_middleware.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    json_response([], 200);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('Método no permitido', 405);
}

verificar_rol(['administrador']);

$input = json_decode(file_get_contents('php://input'), true);

$codigo           = strtoupper(trim($input['codigo'] ?? ''));
$monto            = (float)($input['monto'] ?? 0);
$nombre_pagador   = trim($input['nombre_pagador'] ?? '');
$telefono_pagador = trim($input['telefono_pagador'] ?? '');
$fecha_operacion  = $input['fecha_operacion'] ?? '';

if (empty($codigo) || empty($monto) || empty($fecha_operacion)) {
    json_error('Código, monto y fecha son obligatorios.', 400);
}

$stmt = $pdo->prepare("SELECT id FROM yape_operaciones WHERE codigo = ?");
$stmt->execute([$codigo]);
if ($stmt->fetch()) {
    json_error('Ese código ya está registrado.', 409);
}

$stmt = $pdo->prepare("INSERT INTO yape_operaciones (codigo, monto, nombre_pagador, telefono_pagador, fecha_operacion)
                       VALUES (?, ?, ?, ?, ?)");
$stmt->execute([$codigo, $monto, $nombre_pagador, $telefono_pagador, $fecha_operacion]);

json_success([], "Código $codigo registrado correctamente.");
?>
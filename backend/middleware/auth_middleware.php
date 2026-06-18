<?php
require_once __DIR__ . '/../helpers/jwt.php';
require_once __DIR__ . '/../helpers/response.php';

function verificar_autenticacion() {
    $token = jwt_obtener_token_header();

    if (!$token) {
        json_error('No autorizado. Token no proporcionado.', 401);
    }

    $payload = jwt_verificar($token);

    if (!$payload) {
        json_error('No autorizado. Token inválido o expirado.', 401);
    }

    return $payload; // contiene usuario_id, nombre, rol
}

function verificar_rol($roles_permitidos) {
    $payload = verificar_autenticacion();

    if (!in_array($payload['rol'], $roles_permitidos)) {
        json_error('No tienes permisos para acceder a este recurso.', 403);
    }

    return $payload;
}
?>
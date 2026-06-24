<?php
// Clave secreta para firmar los tokens
define('JWT_SECRET', 'eduka_clave_secreta_2026_cambiar_en_produccion');
define('JWT_EXPIRACION', 3600); // 1 hora en segundos

function base64url_encode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function base64url_decode($data) {
    return base64_decode(strtr($data, '-_', '+/'));
}

function jwt_generar($payload_data) {
    // Header
    $header = json_encode(['alg' => 'HS256', 'typ' => 'JWT']);

    // Payload (agregamos tiempo de emisión y expiración)
    $payload_data['iat'] = time();
    $payload_data['exp'] = time() + JWT_EXPIRACION;
    $payload = json_encode($payload_data);

    // Codificar header y payload
    $header_encoded  = base64url_encode($header);
    $payload_encoded = base64url_encode($payload);

    // Crear firma
    $firma = hash_hmac('sha256', "$header_encoded.$payload_encoded", JWT_SECRET, true);
    $firma_encoded = base64url_encode($firma);

    return "$header_encoded.$payload_encoded.$firma_encoded";
}

function jwt_verificar($token) {
    $partes = explode('.', $token);

    if (count($partes) !== 3) {
        return false;
    }

    list($header_encoded, $payload_encoded, $firma_encoded) = $partes;

    // Verificar firma
    $firma_esperada = hash_hmac('sha256', "$header_encoded.$payload_encoded", JWT_SECRET, true);
    $firma_esperada_encoded = base64url_encode($firma_esperada);

    if (!hash_equals($firma_esperada_encoded, $firma_encoded)) {
        return false; // Firma inválida, el token fue modificado
    }

    // Decodificar payload
    $payload = json_decode(base64url_decode($payload_encoded), true);

    // Verificar expiración
    if (!isset($payload['exp']) || $payload['exp'] < time()) {
        return false; // Token expirado
    }

    return $payload;
}

function jwt_obtener_token_header() {
    $headers = [];

    if (function_exists('apache_request_headers')) {
        $headers = apache_request_headers();
    } else {
        foreach ($_SERVER as $key => $value) {
            if (substr($key, 0, 5) === 'HTTP_') {
                $header_name = str_replace('_', '-', substr($key, 5));
                $headers[$header_name] = $value;
            }
        }
    }

    $auth_header = $headers['Authorization'] ?? $headers['authorization'] ?? '';

    if (preg_match('/Bearer\s(\S+)/', $auth_header, $matches)) {
        return $matches[1];
    }

    return null;
}
?>
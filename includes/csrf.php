<?php
function csrf_generar() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_verificar() {
    if (
        empty($_SESSION['csrf_token']) ||
        empty($_POST['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
    ) {
        http_response_code(403);
        die('Error de seguridad: token CSRF inválido. <a href="/eduka/index.php">Volver al inicio</a>');
    }
    // Regenerar token después de cada uso
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
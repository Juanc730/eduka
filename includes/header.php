<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/csrf.php';

// Verificar que el usuario sigue activo en la base de datos
if (isset($_SESSION['usuario_id'])) {
    require_once __DIR__ . '/../config/database.php';
    $stmt = $pdo->prepare("SELECT activo FROM usuarios WHERE id = ?");
    $stmt->execute([$_SESSION['usuario_id']]);
    $usuario_activo = $stmt->fetch();

    if (!$usuario_activo || !$usuario_activo['activo']) {
        session_destroy();
        header('Location: /eduka/login.php?error=Tu cuenta ha sido desactivada. Contacta al administrador.');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? $page_title . ' — ' : '' ?>Instituto Eduka</title>
    <link rel="stylesheet" href="/eduka/assets/css/style.css">
</head>
<body>  
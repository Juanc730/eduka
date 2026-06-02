<?php
require_once '../../includes/header.php';
require_once '../../config/database.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'administrador') {
    header('Location: /eduka/login.php');
    exit;
}

$id = (int)$_GET['id'];

// No permitir desactivarse a sí mismo
if ($id === $_SESSION['usuario_id']) {
    header('Location: /eduka/modules/admin/usuarios.php?error=No puedes desactivar tu propia cuenta');
    exit;
}

$stmt = $pdo->prepare("UPDATE usuarios SET activo = NOT activo WHERE id = ?");
$stmt->execute([$id]);

header('Location: /eduka/modules/admin/usuarios.php?msg=Estado del usuario actualizado');
exit;
?>
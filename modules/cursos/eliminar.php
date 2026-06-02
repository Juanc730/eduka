<?php
require_once '../../config/database.php';
require_once '../../includes/header.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'administrador') {
    header('Location: /eduka/login.php');
    exit;
}

// Eliminar no borra el registro de la base de datos, solo lo marca como inactivo.
// Esto para mantener el historial de matrículas intacto.

$id = (int)$_GET['id'];
$stmt = $pdo->prepare("UPDATE cursos SET estado = 'inactivo' WHERE id = ?");
$stmt->execute([$id]);

header('Location: /eduka/modules/cursos/lista.php?msg=Curso eliminado correctamente');
exit;
?>


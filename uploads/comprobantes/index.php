<?php
// Si alguien intenta entrar directamente a http://localhost/eduka/uploads/comprobantes/,
// lo redirige al inicio en lugar de mostrar el listado de archivos.
header('Location: /eduka/index.php');
exit;
?>
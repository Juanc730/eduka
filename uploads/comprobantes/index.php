<?php
// Si alguien intenta entrar directamente a http://localhost/eduka/uploads/comprobantes/,
// lo redirige al login en lugar de mostrar el listado de archivos.
header('Location: /eduka/frontend/pages/login.html');
exit;
?>
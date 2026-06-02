<?php
session_start();
session_destroy();
header('Location: /eduka/login.php');
exit;
?>
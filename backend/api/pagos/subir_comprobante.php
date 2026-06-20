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

$payload = verificar_rol(['estudiante']);

$matricula_id = (int)($_POST['matricula_id'] ?? 0);
$monto        = (float)($_POST['monto'] ?? 0);

if (empty($matricula_id) || empty($monto)) {
    json_error('Matrícula y monto son obligatorios.', 400);
}

$stmt = $pdo->prepare("SELECT * FROM matriculas WHERE id = ? AND estudiante_id = ?");
$stmt->execute([$matricula_id, $payload['usuario_id']]);
$matricula = $stmt->fetch();

if (!$matricula) {
    json_error('Matrícula no encontrada.', 404);
}

$stmt = $pdo->prepare("SELECT * FROM pagos WHERE matricula_id = ? AND estado = 'aprobado'");
$stmt->execute([$matricula_id]);
if ($stmt->fetch()) {
    json_error('Esta matrícula ya tiene un pago aprobado.', 409);
}

if (!isset($_FILES['comprobante']) || $_FILES['comprobante']['error'] !== 0) {
    json_error('Debes adjuntar un archivo de comprobante.', 400);
}

$archivo    = $_FILES['comprobante'];
$extension  = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
$permitidos = ['jpg', 'jpeg', 'png', 'webp'];

if (!in_array($extension, $permitidos)) {
    json_error('Solo se permiten imágenes JPG, PNG o WEBP.', 400);
}

if ($archivo['size'] > 5 * 1024 * 1024) {
    json_error('El archivo no debe superar 5MB.', 400);
}

$carpeta = __DIR__ . '/../../../uploads/comprobantes/';
if (!is_dir($carpeta)) {
    mkdir($carpeta, 0755, true);
}

$nombre_archivo = 'comp_' . $matricula_id . '_' . time() . '.' . $extension;
$ruta           = $carpeta . $nombre_archivo;

if (!move_uploaded_file($archivo['tmp_name'], $ruta)) {
    json_error('No se pudo guardar el archivo. Intenta nuevamente.', 500);
}

try {
    $pdo->beginTransaction();

    $pdo->prepare("DELETE FROM pagos WHERE matricula_id = ? AND estado = 'rechazado'")->execute([$matricula_id]);

    $stmt = $pdo->prepare("INSERT INTO pagos (matricula_id, monto, metodo, comprobante, metodo_verificacion, estado)
                           VALUES (?, ?, 'Yape', ?, 'comprobante', 'pendiente')");
    $stmt->execute([$matricula_id, $monto, $nombre_archivo]);

    $pdo->commit();

    json_success([], 'Comprobante enviado. El administrador lo revisará pronto.');
} catch (Exception $e) {
    $pdo->rollBack();
    json_error('Error al registrar el pago.', 500);
}
?>
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

$input         = json_decode(file_get_contents('php://input'), true);
$matricula_id  = (int)($input['matricula_id'] ?? 0);
$codigo        = strtoupper(trim($input['codigo_yape'] ?? ''));
$monto         = (float)($input['monto'] ?? 0);

if (empty($matricula_id) || empty($codigo) || empty($monto)) {
    json_error('Todos los campos son obligatorios.', 400);
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

$stmt = $pdo->prepare("SELECT * FROM yape_operaciones WHERE codigo = ? AND usado = 0");
$stmt->execute([$codigo]);
$operacion = $stmt->fetch();

if (!$operacion) {
    json_error('Código de operación inválido o ya fue utilizado.', 404);
}

if ((float)$operacion['monto'] !== $monto) {
    json_error('El monto ingresado no coincide con el registrado para este código.', 400);
}

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("INSERT INTO pagos (matricula_id, monto, metodo, codigo_yape, metodo_verificacion, estado)
                           VALUES (?, ?, 'Yape', ?, 'codigo', 'aprobado')");
    $stmt->execute([$matricula_id, $operacion['monto'], $codigo]);

    $pdo->prepare("UPDATE matriculas SET estado = 'confirmada' WHERE id = ?")->execute([$matricula_id]);
    $pdo->prepare("UPDATE yape_operaciones SET usado = 1 WHERE codigo = ?")->execute([$codigo]);

    $pdo->commit();

    json_success([], 'Pago verificado automáticamente. Tu matrícula ha sido confirmada.');
} catch (Exception $e) {
    $pdo->rollBack();
    json_error('Error al procesar el pago.', 500);
}
?>
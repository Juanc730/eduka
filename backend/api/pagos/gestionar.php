<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../middleware/auth_middleware.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    json_response([], 200);
}

if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    json_error('Método no permitido', 405);
}

verificar_rol(['administrador']);

$input  = json_decode(file_get_contents('php://input'), true);
$id     = (int)($input['id'] ?? 0);
$accion = $input['accion'] ?? '';

if (empty($id) || !in_array($accion, ['aprobar', 'rechazar'])) {
    json_error('Datos inválidos.', 400);
}

$stmt = $pdo->prepare("SELECT * FROM pagos WHERE id = ?");
$stmt->execute([$id]);
$pago = $stmt->fetch();

if (!$pago) {
    json_error('Pago no encontrado.', 404);
}

if ($pago['metodo_verificacion'] !== 'comprobante') {
    json_error('Solo los pagos por comprobante requieren aprobación manual.', 400);
}

try {
    $pdo->beginTransaction();

    if ($accion === 'aprobar') {
        $pdo->prepare("UPDATE pagos SET estado = 'aprobado' WHERE id = ?")->execute([$id]);
        $pdo->prepare("UPDATE matriculas SET estado = 'confirmada' WHERE id = ?")->execute([$pago['matricula_id']]);
    } else {
        $pdo->prepare("UPDATE pagos SET estado = 'rechazado' WHERE id = ?")->execute([$id]);
        $pdo->prepare("UPDATE matriculas SET estado = 'pendiente' WHERE id = ?")->execute([$pago['matricula_id']]);
    }

    $pdo->commit();

    json_success([], $accion === 'aprobar' ? 'Pago aprobado correctamente.' : 'Pago rechazado.');
} catch (Exception $e) {
    $pdo->rollBack();
    json_error('Error al procesar la acción.', 500);
}
?>
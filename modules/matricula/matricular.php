<?php
$page_title = 'Confirmar Matrícula';
require_once '../../includes/header.php';
require_once '../../config/database.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'estudiante') {
    header('Location: /eduka/login.php');
    exit;
}

$curso_id     = (int)$_GET['curso_id'];
$estudiante_id = $_SESSION['usuario_id'];

// Obtener datos del curso
$stmt = $pdo->prepare("SELECT c.*, u.nombre AS docente_nombre, u.apellido AS docente_apellido 
                       FROM cursos c 
                       LEFT JOIN usuarios u ON c.docente_id = u.id 
                       WHERE c.id = ? AND c.estado = 'activo'");
$stmt->execute([$curso_id]);
$curso = $stmt->fetch();

if (!$curso) {
    header('Location: /eduka/modules/cursos/lista.php?error=Curso no encontrado');
    exit;
}

// Verificar si ya está matriculado
$stmt = $pdo->prepare("SELECT id FROM matriculas WHERE estudiante_id = ? AND curso_id = ? AND estado != 'anulada'");
$stmt->execute([$estudiante_id, $curso_id]);
if ($stmt->fetch()) {
    header('Location: /eduka/modules/cursos/lista.php?error=Ya estás matriculado en este curso');
    exit;
}

// Verificar cupos
if ($curso['cupos_disponibles'] <= 0) {
    header('Location: /eduka/modules/cursos/lista.php?error=No hay cupos disponibles');
    exit;
}

// Confirmar matrícula
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();

        // Crear matrícula
        $stmt = $pdo->prepare("INSERT INTO matriculas (estudiante_id, curso_id, estado) VALUES (?, ?, 'pendiente')");
        $stmt->execute([$estudiante_id, $curso_id]);
        $matricula_id = $pdo->lastInsertId();

        // Reducir cupo
        $pdo->prepare("UPDATE cursos SET cupos_disponibles = cupos_disponibles - 1 WHERE id = ?")->execute([$curso_id]);

        $pdo->commit();
        header("Location: /eduka/modules/pagos/subir_pago.php?matricula_id=$matricula_id");
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = 'Ocurrió un error al procesar la matrícula. Intenta nuevamente.';
    }
}
?>

<?php include '../../includes/navbar.php'; ?>

<div class="dashboard-container">
    <div class="page-header">
        <h1>Confirmar Matrícula</h1>
        <a href="/eduka/modules/cursos/lista.php" class="btn-secondary">← Volver</a>
    </div>

    <?php if (isset($error)): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <div class="form-card">
        <h3><?= htmlspecialchars($curso['nombre']) ?></h3>
        <div class="curso-info" style="margin: 1rem 0;">
            <span>👨‍🏫 <?= htmlspecialchars($curso['docente_nombre'] . ' ' . $curso['docente_apellido']) ?></span>
            <span>🕐 <?= htmlspecialchars($curso['horario']) ?></span>
            <span>🪑 <?= $curso['cupos_disponibles'] ?> cupos disponibles</span>
        </div>
        <p><?= htmlspecialchars($curso['descripcion']) ?></p>

        <div class="aviso-pago">
            <strong>⚠️ Importante:</strong> Al confirmar tu matrícula, podrás pagar por <strong>Yape</strong> ingresando tu código de operación (verificación automática) o subiendo tu comprobante como imagen para revisión del administrador.
        </div>

        <form method="POST">
            <button type="submit" class="btn-primary" style="margin-top:1rem;">Confirmar y continuar al pago</button>
        </form>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
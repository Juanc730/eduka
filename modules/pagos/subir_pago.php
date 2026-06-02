<?php
require_once '../../includes/header.php';
require_once '../../config/database.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'estudiante') {
    header('Location: /eduka/login.php');
    exit;
}

$matricula_id  = (int)$_GET['matricula_id'];
$estudiante_id = $_SESSION['usuario_id'];

// Verificar que la matrícula pertenece al estudiante
$stmt = $pdo->prepare("SELECT m.*, c.nombre AS curso FROM matriculas m 
                       JOIN cursos c ON m.curso_id = c.id
                       WHERE m.id = ? AND m.estudiante_id = ?");
$stmt->execute([$matricula_id, $estudiante_id]);
$matricula = $stmt->fetch();

if (!$matricula) {
    header('Location: /eduka/modules/matricula/mis_matriculas.php');
    exit;
}

// Verificar que no tenga ya un pago aprobado
$stmt = $pdo->prepare("SELECT * FROM pagos WHERE matricula_id = ? AND estado = 'aprobado'");
$stmt->execute([$matricula_id]);
if ($stmt->fetch()) {
    header('Location: /eduka/modules/matricula/mis_matriculas.php');
    exit;
}

$error   = '';
$success = '';

// ── Procesar código Yape ──────────────────────────────────────────
if (isset($_POST['metodo']) && $_POST['metodo'] === 'codigo') {
    $codigo = strtoupper(trim($_POST['codigo_yape']));
    $monto  = (float)$_POST['monto'];

    // Consultar "API" Yape
    $stmt = $pdo->prepare("SELECT * FROM yape_operaciones WHERE codigo = ? AND usado = 0");
    $stmt->execute([$codigo]);
    $operacion = $stmt->fetch();

    if (!$operacion) {
        $error = 'Código de operación inválido o ya fue utilizado.';
    } elseif ((float)$operacion['monto'] !== $monto) {
        $error = 'El monto ingresado no coincide con el registrado para este código.';
    } else {
        try {
            $pdo->beginTransaction();

            // Registrar pago
            $stmt = $pdo->prepare("INSERT INTO pagos (matricula_id, monto, metodo, codigo_yape, metodo_verificacion, estado) 
                                   VALUES (?, ?, 'Yape', ?, 'codigo', 'aprobado')");
            $stmt->execute([$matricula_id, $operacion['monto'], $codigo]);

            // Confirmar matrícula automáticamente
            $pdo->prepare("UPDATE matriculas SET estado = 'confirmada' WHERE id = ?")->execute([$matricula_id]);

            // Marcar código como usado
            $pdo->prepare("UPDATE yape_operaciones SET usado = 1 WHERE codigo = ?")->execute([$codigo]);

            $pdo->commit();
            $success = 'codigo';
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = 'Error al procesar el pago. Intenta nuevamente.';
        }
    }
}

// ── Procesar comprobante imagen ───────────────────────────────────
if (isset($_POST['metodo']) && $_POST['metodo'] === 'comprobante') {
    if (!isset($_FILES['comprobante']) || $_FILES['comprobante']['error'] !== 0) {
        $error = 'Debes seleccionar un archivo de comprobante.';
    } else {
        $archivo   = $_FILES['comprobante'];
        $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
        $permitidos = ['jpg', 'jpeg', 'png', 'webp'];

        if (!in_array($extension, $permitidos)) {
            $error = 'Solo se permiten imágenes JPG, PNG o WEBP.';
        } elseif ($archivo['size'] > 5 * 1024 * 1024) {
            $error = 'El archivo no debe superar 5MB.';
        } else {
            $carpeta = '../../uploads/comprobantes/';
            if (!is_dir($carpeta)) mkdir($carpeta, 0755, true);

            $nombre_archivo = 'comp_' . $matricula_id . '_' . time() . '.' . $extension;
            $ruta           = $carpeta . $nombre_archivo;

            if (move_uploaded_file($archivo['tmp_name'], $ruta)) {
                try {
                    $pdo->beginTransaction();

                    // Eliminar pago rechazado anterior si existe
                    $pdo->prepare("DELETE FROM pagos WHERE matricula_id = ? AND estado = 'rechazado'")->execute([$matricula_id]);

                    // Registrar nuevo pago
                    $monto = (float)$_POST['monto'];
                    $stmt  = $pdo->prepare("INSERT INTO pagos (matricula_id, monto, metodo, comprobante, metodo_verificacion, estado) 
                                           VALUES (?, ?, 'Yape', ?, 'comprobante', 'pendiente')");
                    $stmt->execute([$matricula_id, $monto, $nombre_archivo]);

                    $pdo->commit();
                    $success = 'comprobante';
                } catch (Exception $e) {
                    $pdo->rollBack();
                    $error = 'Error al registrar el pago. Intenta nuevamente.';
                }
            } else {
                $error = 'No se pudo guardar el archivo. Intenta nuevamente.';
            }
        }
    }
}
?>

<?php include '../../includes/navbar.php'; ?>

<div class="dashboard-container">
    <div class="page-header">
        <h1>Registrar Pago</h1>
        <a href="/eduka/modules/matricula/mis_matriculas.php" class="btn-secondary">← Volver</a>
    </div>

    <div class="form-card" style="max-width:650px;">
        <h3 style="color:#1a3c5e; margin-bottom:0.3rem;"><?= htmlspecialchars($matricula['curso']) ?></h3>
        <p style="color:#666; margin-bottom:1.5rem;">Selecciona cómo deseas verificar tu pago:</p>

        <?php if ($error): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <?php if ($success === 'codigo'): ?>
            <div class="aviso-exito">
                ✅ <strong>¡Pago verificado automáticamente!</strong> Tu matrícula ha sido confirmada.
                <br><a href="/eduka/modules/matricula/mis_matriculas.php" class="btn-primary" style="margin-top:1rem; display:inline-block;">Ver mis matrículas</a>
            </div>
        <?php elseif ($success === 'comprobante'): ?>
            <div class="aviso-exito">
                📋 <strong>Comprobante enviado.</strong> El administrador lo revisará pronto.
                <br><a href="/eduka/modules/matricula/mis_matriculas.php" class="btn-primary" style="margin-top:1rem; display:inline-block;">Ver mis matrículas</a>
            </div>
        <?php else: ?>

        <!-- Tabs -->
        <div class="tabs">
            <button class="tab active" onclick="mostrarTab('codigo', this)">📱 Código Yape</button>
            <button class="tab" onclick="mostrarTab('comprobante', this)">🖼️ Subir comprobante</button>
        </div>

        <!-- Tab: Código Yape -->
        <div id="tab-codigo" class="tab-content">
            <div class="aviso-pago" style="margin-bottom:1rem;">
                <strong>📱 Verificación automática:</strong> Ingresa el código de operación que aparece en tu app Yape al completar el pago. La confirmación es inmediata.
            </div>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="metodo" value="codigo">
                <label>Código de operación Yape *</label>
                <input type="text" name="codigo_yape" placeholder="Ej: YPE-20260601-84521" required style="text-transform:uppercase;">
                <label>Monto pagado (S/) *</label>
                <input type="number" name="monto" step="0.01" min="1" placeholder="Ej: 150.00" required>
                <button type="submit" class="btn-primary" style="margin-top:1rem;">Verificar y confirmar</button>
            </form>
        </div>

        <!-- Tab: Comprobante -->
        <div id="tab-comprobante" class="tab-content" style="display:none;">
            <div class="aviso-pago" style="margin-bottom:1rem;">
                <strong>🖼️ Verificación manual:</strong> Sube una captura de tu pago Yape. El administrador la revisará y confirmará tu matrícula.
            </div>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="metodo" value="comprobante">
                <label>Monto pagado (S/) *</label>
                <input type="number" name="monto" step="0.01" min="1" placeholder="Ej: 150.00" required>
                <label>Imagen del comprobante * <small>(JPG, PNG o WEBP, máx. 5MB)</small></label>
                <input type="file" name="comprobante" accept=".jpg,.jpeg,.png,.webp" required>
                <button type="submit" class="btn-primary" style="margin-top:1rem;">Enviar comprobante</button>
            </form>
        </div>

        <?php endif; ?>
    </div>
</div>

<script>
function mostrarTab(tab, btn) {
    document.querySelectorAll('.tab-content').forEach(t => t.style.display = 'none');
    document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
    document.getElementById('tab-' + tab).style.display = 'block';
    btn.classList.add('active');
}
</script>

<?php include '../../includes/footer.php'; ?>
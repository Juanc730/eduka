<?php
$page_title = 'Códigos Yape';
require_once '../../includes/header.php';
require_once '../../config/database.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'administrador') {
    header('Location: /eduka/login.php');
    exit;
}

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verificar();
    $codigo   = strtoupper(trim($_POST['codigo']));
    $monto    = (float)$_POST['monto'];
    $pagador  = trim($_POST['nombre_pagador']);
    $telefono = trim($_POST['telefono_pagador']);
    $fecha    = $_POST['fecha_operacion'];

    if (empty($codigo) || empty($monto) || empty($fecha)) {
        $error = 'Código, monto y fecha son obligatorios.';
    } else {
        $stmt = $pdo->prepare("SELECT id FROM yape_operaciones WHERE codigo = ?");
        $stmt->execute([$codigo]);
        if ($stmt->fetch()) {
            $error = 'Ese código ya está registrado.';
        } else {
            $stmt = $pdo->prepare("INSERT INTO yape_operaciones (codigo, monto, nombre_pagador, telefono_pagador, fecha_operacion) 
                                   VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$codigo, $monto, $pagador, $telefono, $fecha]);
            $success = "Código $codigo registrado correctamente.";
        }
    }
}

// Listar códigos cargados
$operaciones = $pdo->query("SELECT * FROM yape_operaciones ORDER BY created_at DESC")->fetchAll();
?>

<?php include '../../includes/navbar.php'; ?>

<div class="dashboard-container">
    <div class="page-header">
        <h1>Códigos Yape</h1>
        <a href="/eduka/modules/pagos/admin_pagos.php" class="btn-secondary">← Volver</a>
    </div>

    <?php if ($error): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <?php if ($success): ?>
        <p class="success"><?= htmlspecialchars($success) ?></p>
    <?php endif; ?>

    <div class="form-card" style="margin-bottom:2rem;">
        <h3 style="margin-bottom:1rem;">Registrar nuevo código</h3>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= csrf_generar() ?>">
            <label>Código de operación *</label>
            <input type="text" name="codigo" placeholder="Ej: YPE-20260601-84521" style="text-transform:uppercase;" required>

            <label>Monto (S/) *</label>
            <input type="number" name="monto" step="0.01" min="1" required>

            <label>Nombre del pagador</label>
            <input type="text" name="nombre_pagador" placeholder="Nombre completo">

            <label>Teléfono Yape</label>
            <input type="text" name="telefono_pagador" placeholder="9XXXXXXXX">

            <label>Fecha y hora de operación *</label>
            <input type="datetime-local" name="fecha_operacion" required>

            <button type="submit" class="btn-primary" style="margin-top:1rem;">Registrar código</button>
        </form>
    </div>

    <h3 style="color:#1a3c5e; margin-bottom:1rem;">Códigos registrados</h3>
    <div class="table-container">
        <table class="tabla">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Monto</th>
                    <th>Pagador</th>
                    <th>Teléfono</th>
                    <th>Fecha operación</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($operaciones as $op): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($op['codigo']) ?></strong></td>
                        <td>S/ <?= number_format($op['monto'], 2) ?></td>
                        <td><?= htmlspecialchars($op['nombre_pagador'] ?: '—') ?></td>
                        <td><?= htmlspecialchars($op['telefono_pagador'] ?: '—') ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($op['fecha_operacion'])) ?></td>
                        <td>
                            <?php if ($op['usado']): ?>
                                <span class="badge-inactivo">Usado</span>
                            <?php else: ?>
                                <span class="badge-activo">Disponible</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
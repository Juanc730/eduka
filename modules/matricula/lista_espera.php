<?php
require_once '../../includes/header.php';
require_once '../../config/database.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'estudiante') {
    header('Location: /eduka/login.php');
    exit;
}

$curso_id      = (int)$_GET['curso_id'];
$estudiante_id = $_SESSION['usuario_id'];

$stmt = $pdo->prepare("SELECT * FROM cursos WHERE id = ? AND estado = 'activo'");
$stmt->execute([$curso_id]);
$curso = $stmt->fetch();

if (!$curso) {
    header('Location: /eduka/modules/cursos/lista.php?error=Curso no encontrado');
    exit;
}

// Verificar si ya está en lista de espera
$stmt = $pdo->prepare("SELECT id FROM lista_espera WHERE estudiante_id = ? AND curso_id = ?");
$stmt->execute([$estudiante_id, $curso_id]);
if ($stmt->fetch()) {
    header('Location: /eduka/modules/cursos/lista.php?error=Ya estás en la lista de espera de este curso');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("INSERT INTO lista_espera (estudiante_id, curso_id) VALUES (?, ?)");
    $stmt->execute([$estudiante_id, $curso_id]);
    header('Location: /eduka/modules/matricula/mis_matriculas.php');
    exit;
}
?>

<?php include '../../includes/navbar.php'; ?>

<div class="dashboard-container">
    <div class="page-header">
        <h1>Lista de Espera</h1>
        <a href="/eduka/modules/cursos/lista.php" class="btn-secondary">← Volver</a>
    </div>

    <div class="form-card">
        <h3><?= htmlspecialchars($curso['nombre']) ?></h3>
        <p style="margin: 1rem 0;">Este curso no tiene cupos disponibles en este momento. Puedes unirte a la lista de espera y serás notificado cuando se libere un cupo.</p>

        <div class="aviso-pago">
            <strong>📋 Información:</strong> Tu posición en la lista de espera se asigna por orden de inscripción.
        </div>

        <form method="POST">
            <button type="submit" class="btn-primary" style="margin-top:1rem;">Unirme a la lista de espera</button>
        </form>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
<?php
require_once '../../includes/header.php';
require_once '../../config/database.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'administrador') {
    header('Location: /eduka/login.php');
    exit;
}

$id = (int)$_GET['id'];
$curso = $pdo->prepare("SELECT * FROM cursos WHERE id = ?");
$curso->execute([$id]);
$curso = $curso->fetch();

if (!$curso) {
    header('Location: /eduka/modules/cursos/lista.php?error=Curso no encontrado');
    exit;
}

$docentes = $pdo->query("SELECT id, nombre, apellido FROM usuarios WHERE rol_id = 3 AND activo = 1")->fetchAll();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre      = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion']);
    $docente_id  = $_POST['docente_id'];
    $cupos       = (int)$_POST['cupos_totales'];
    $horario     = trim($_POST['horario']);

    if (empty($nombre) || empty($cupos) || empty($horario)) {
        $error = 'Por favor completa todos los campos obligatorios.';
    } else {
        $diff = $cupos - $curso['cupos_totales'];
        $nuevos_disponibles = max(0, $curso['cupos_disponibles'] + $diff);

        $stmt = $pdo->prepare("UPDATE cursos SET nombre=?, descripcion=?, docente_id=?, cupos_totales=?, cupos_disponibles=?, horario=? WHERE id=?");
        $stmt->execute([$nombre, $descripcion, $docente_id ?: null, $cupos, $nuevos_disponibles, $horario, $id]);
        header('Location: /eduka/modules/cursos/lista.php?msg=Curso actualizado correctamente');
        exit;
    }
}
?>

<?php include '../../includes/navbar.php'; ?>

<div class="dashboard-container">
    <div class="page-header">
        <h1>Editar Curso</h1>
        <a href="/eduka/modules/cursos/lista.php" class="btn-secondary">← Volver</a>
    </div>

    <?php if ($error): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <div class="form-card">
        <form method="POST">
            <label>Nombre del curso *</label>
            <input type="text" name="nombre" value="<?= htmlspecialchars($curso['nombre']) ?>" required>

            <label>Descripción</label>
            <textarea name="descripcion" rows="3"><?= htmlspecialchars($curso['descripcion']) ?></textarea>

            <label>Docente</label>
            <select name="docente_id">
                <option value="">-- Sin asignar --</option>
                <?php foreach ($docentes as $d): ?>
                    <option value="<?= $d['id'] ?>" <?= $curso['docente_id'] == $d['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($d['nombre'] . ' ' . $d['apellido']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label>Cupos totales *</label>
            <input type="number" name="cupos_totales" value="<?= $curso['cupos_totales'] ?>" min="1" required>

            <label>Horario *</label>
            <input type="text" name="horario" value="<?= htmlspecialchars($curso['horario']) ?>" required>

            <button type="submit" class="btn-primary">Guardar Cambios</button>
        </form>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
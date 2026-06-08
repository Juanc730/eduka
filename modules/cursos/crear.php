<?php
$page_title = 'Nuevo Curso';
require_once '../../includes/header.php';
require_once '../../config/database.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'administrador') {
    header('Location: /eduka/login.php');
    exit;
}

$error = '';

// Obtener docentes
$docentes = $pdo->query("SELECT id, nombre, apellido FROM usuarios WHERE rol_id = 3 AND activo = 1")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre     = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion']);
    $docente_id = $_POST['docente_id'];
    $cupos      = (int)$_POST['cupos_totales'];
    $horario    = trim($_POST['horario']);

    if (empty($nombre) || empty($cupos) || empty($horario)) {
        $error = 'Por favor completa todos los campos obligatorios.';
    } else {
        $stmt = $pdo->prepare("INSERT INTO cursos (nombre, descripcion, docente_id, cupos_totales, cupos_disponibles, horario) 
                               VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$nombre, $descripcion, $docente_id ?: null, $cupos, $cupos, $horario]);
        header('Location: /eduka/modules/cursos/lista.php?msg=Curso creado exitosamente');
        exit;
    }
}
?>


<?php include '../../includes/navbar.php'; ?>

<div class="dashboard-container">
    <div class="page-header">
        <h1>Nuevo Curso</h1>
        <a href="/eduka/modules/cursos/lista.php" class="btn-secondary">← Volver</a>
    </div>

    <?php if ($error): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <div class="form-card">
        <form method="POST">
            <label>Nombre del curso *</label>
            <input type="text" name="nombre" required>

            <label>Descripción</label>
            <textarea name="descripcion" rows="3"></textarea>

            <label>Docente</label>
            <select name="docente_id">
                <option value="">-- Sin asignar --</option>
                <?php foreach ($docentes as $d): ?>
                    <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['nombre'] . ' ' . $d['apellido']) ?></option>
                <?php endforeach; ?>
            </select>

            <label>Cupos totales *</label>
            <input type="number" name="cupos_totales" min="1" required>

            <label>Horario *</label>
            <input type="text" name="horario" placeholder="Ej: Lunes y Miércoles 7:00pm - 9:00pm" required>

            <button type="submit" class="btn-primary">Crear Curso</button>
        </form>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
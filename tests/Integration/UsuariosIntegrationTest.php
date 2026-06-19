<?php
use PHPUnit\Framework\TestCase;

class UsuariosIntegrationTest extends TestCase
{
    private $pdo;

    protected function setUp(): void
    {
        require __DIR__ . '/../../backend/config/database_test.php';
        $this->pdo = $pdo;

        $this->pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
        $this->pdo->exec("TRUNCATE TABLE usuarios");
        $this->pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    }

    public function test_se_puede_listar_usuarios_con_su_rol()
    {
        $hash = password_hash('Eduka2026@', PASSWORD_DEFAULT);
        $stmt = $this->pdo->prepare("INSERT INTO usuarios (nombre, apellido, email, password, rol_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute(['Juan', 'Pérez', 'juan@eduka.com', $hash, 2]);

        $stmt = $this->pdo->query("SELECT u.*, r.nombre AS rol FROM usuarios u JOIN roles r ON u.rol_id = r.id");
        $usuarios = $stmt->fetchAll();

        $this->assertCount(1, $usuarios);
        $this->assertEquals('estudiante', $usuarios[0]['rol']);
    }

    public function test_busqueda_por_nombre_filtra_correctamente()
    {
        $hash = password_hash('Eduka2026@', PASSWORD_DEFAULT);
        $stmt = $this->pdo->prepare("INSERT INTO usuarios (nombre, apellido, email, password, rol_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute(['Carlos', 'Docente', 'carlos@eduka.com', $hash, 3]);
        $stmt->execute(['Ana', 'García', 'ana@eduka.com', $hash, 2]);

        $termino = '%carlos%';
        $stmt = $this->pdo->prepare("SELECT * FROM usuarios WHERE nombre LIKE ? OR apellido LIKE ?");
        $stmt->execute([$termino, $termino]);
        $resultado = $stmt->fetchAll();

        $this->assertCount(1, $resultado);
        $this->assertEquals('Carlos', $resultado[0]['nombre']);
    }

    public function test_no_permite_crear_usuario_con_email_duplicado()
    {
        $hash = password_hash('Eduka2026@', PASSWORD_DEFAULT);
        $stmt = $this->pdo->prepare("INSERT INTO usuarios (nombre, apellido, email, password, rol_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute(['Juan', 'Pérez', 'juan@eduka.com', $hash, 2]);

        $this->expectException(PDOException::class);
        $stmt->execute(['Otro', 'Usuario', 'juan@eduka.com', $hash, 2]);
    }

    public function test_editar_usuario_actualiza_los_datos_correctamente()
    {
        $hash = password_hash('Eduka2026@', PASSWORD_DEFAULT);
        $stmt = $this->pdo->prepare("INSERT INTO usuarios (nombre, apellido, email, password, rol_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute(['Juan', 'Pérez', 'juan@eduka.com', $hash, 2]);
        $id = $this->pdo->lastInsertId();

        $stmt = $this->pdo->prepare("UPDATE usuarios SET nombre = ?, rol_id = ? WHERE id = ?");
        $stmt->execute(['Juan Carlos', 3, $id]);

        $stmt = $this->pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
        $stmt->execute([$id]);
        $usuario = $stmt->fetch();

        $this->assertEquals('Juan Carlos', $usuario['nombre']);
        $this->assertEquals(3, $usuario['rol_id']);
    }

    public function test_toggle_invierte_el_estado_activo()
    {
        $hash = password_hash('Eduka2026@', PASSWORD_DEFAULT);
        $stmt = $this->pdo->prepare("INSERT INTO usuarios (nombre, apellido, email, password, rol_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute(['Juan', 'Pérez', 'juan@eduka.com', $hash, 2]);
        $id = $this->pdo->lastInsertId();

        // Confirmar que empieza activo
        $stmt = $this->pdo->prepare("SELECT activo FROM usuarios WHERE id = ?");
        $stmt->execute([$id]);
        $this->assertEquals(1, $stmt->fetch()['activo']);

        // Toggle
        $this->pdo->prepare("UPDATE usuarios SET activo = NOT activo WHERE id = ?")->execute([$id]);

        $stmt->execute([$id]);
        $this->assertEquals(0, $stmt->fetch()['activo']);

        // Toggle de nuevo
        $this->pdo->prepare("UPDATE usuarios SET activo = NOT activo WHERE id = ?")->execute([$id]);

        $stmt->execute([$id]);
        $this->assertEquals(1, $stmt->fetch()['activo']);
    }

    protected function tearDown(): void
    {
        $this->pdo = null;
    }
}
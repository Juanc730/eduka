<?php
use PHPUnit\Framework\TestCase;

class AuthIntegrationTest extends TestCase
{
    private $pdo;

    protected function setUp(): void
    {
        require __DIR__ . '/../../backend/config/database_test.php';
        $this->pdo = $pdo;

        // Limpiar tablas antes de cada prueba para que sean independientes
        $this->pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
        $this->pdo->exec("TRUNCATE TABLE usuarios");
        $this->pdo->exec("TRUNCATE TABLE login_intentos");
        $this->pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    }

    public function test_se_puede_registrar_un_usuario_en_la_base_de_datos()
    {
        $hash = password_hash('Eduka2026@', PASSWORD_DEFAULT);

        $stmt = $this->pdo->prepare("INSERT INTO usuarios (nombre, apellido, email, password, rol_id) VALUES (?, ?, ?, ?, ?)");
        $resultado = $stmt->execute(['Juan', 'Pérez', 'juan@eduka.com', $hash, 2]);

        $this->assertTrue($resultado);

        $stmt = $this->pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
        $stmt->execute(['juan@eduka.com']);
        $usuario = $stmt->fetch();

        $this->assertNotFalse($usuario);
        $this->assertEquals('Juan', $usuario['nombre']);
    }

    public function test_no_permite_emails_duplicados()
    {
        $hash = password_hash('Eduka2026@', PASSWORD_DEFAULT);

        $stmt = $this->pdo->prepare("INSERT INTO usuarios (nombre, apellido, email, password, rol_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute(['Juan', 'Pérez', 'juan@eduka.com', $hash, 2]);

        $this->expectException(PDOException::class);

        // Intentar insertar el mismo email otra vez
        $stmt->execute(['Otro', 'Usuario', 'juan@eduka.com', $hash, 2]);
    }

    public function test_password_hash_y_verify_funcionan_correctamente()
    {
        $password_original = 'Eduka2026@';
        $hash = password_hash($password_original, PASSWORD_DEFAULT);

        $stmt = $this->pdo->prepare("INSERT INTO usuarios (nombre, apellido, email, password, rol_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute(['Ana', 'García', 'ana@eduka.com', $hash, 2]);

        $stmt = $this->pdo->prepare("SELECT password FROM usuarios WHERE email = ?");
        $stmt->execute(['ana@eduka.com']);
        $usuario = $stmt->fetch();

        $this->assertTrue(password_verify($password_original, $usuario['password']));
        $this->assertFalse(password_verify('PasswordIncorrecta', $usuario['password']));
    }

    public function test_registra_intentos_fallidos_de_login()
    {
        $stmt = $this->pdo->prepare("INSERT INTO login_intentos (email, ip) VALUES (?, ?)");
        $stmt->execute(['test@eduka.com', '127.0.0.1']);
        $stmt->execute(['test@eduka.com', '127.0.0.1']);

        $stmt = $this->pdo->prepare("SELECT COUNT(*) AS total FROM login_intentos WHERE ip = ?");
        $stmt->execute(['127.0.0.1']);
        $resultado = $stmt->fetch();

        $this->assertEquals(2, $resultado['total']);
    }

    public function test_usuario_inactivo_no_deberia_poder_ser_encontrado_en_login()
    {
        $hash = password_hash('Eduka2026@', PASSWORD_DEFAULT);

        $stmt = $this->pdo->prepare("INSERT INTO usuarios (nombre, apellido, email, password, rol_id, activo) VALUES (?, ?, ?, ?, ?, 0)");
        $stmt->execute(['Carlos', 'López', 'carlos@eduka.com', $hash, 2]);

        // Simulamos la consulta que hace login.php (solo busca activos)
        $stmt = $this->pdo->prepare("SELECT * FROM usuarios WHERE email = ? AND activo = 1");
        $stmt->execute(['carlos@eduka.com']);
        $usuario = $stmt->fetch();

        $this->assertFalse($usuario);
    }

    protected function tearDown(): void
    {
        $this->pdo = null;
    }
}
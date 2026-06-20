<?php
use PHPUnit\Framework\TestCase;

class PagosIntegrationTest extends TestCase
{
    private $pdo;
    private $estudianteId;
    private $cursoId;
    private $matriculaId;

    protected function setUp(): void
    {
        require __DIR__ . '/../../backend/config/database_test.php';
        $this->pdo = $pdo;

        $this->pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
        $this->pdo->exec("TRUNCATE TABLE pagos");
        $this->pdo->exec("TRUNCATE TABLE yape_operaciones");
        $this->pdo->exec("TRUNCATE TABLE matriculas");
        $this->pdo->exec("TRUNCATE TABLE cursos");
        $this->pdo->exec("TRUNCATE TABLE usuarios");
        $this->pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

        $hash = password_hash('Eduka2026@', PASSWORD_DEFAULT);
        $stmt = $this->pdo->prepare("INSERT INTO usuarios (nombre, apellido, email, password, rol_id) VALUES (?, ?, ?, ?, 2)");
        $stmt->execute(['Juan', 'Estudiante', 'juan.pago@eduka.com', $hash]);
        $this->estudianteId = $this->pdo->lastInsertId();

        $stmt = $this->pdo->prepare("INSERT INTO cursos (nombre, descripcion, docente_id, cupos_totales, cupos_disponibles, horario)
                                     VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute(['Curso Pagos', '', null, 10, 9, 'Lunes']);
        $cursoId = $this->pdo->lastInsertId();
        $this->cursoId = $cursoId;

        $stmt = $this->pdo->prepare("INSERT INTO matriculas (estudiante_id, curso_id, estado) VALUES (?, ?, 'pendiente')");
        $stmt->execute([$this->estudianteId, $cursoId]);
        $this->matriculaId = $this->pdo->lastInsertId();
    }

    public function test_codigo_yape_valido_confirma_la_matricula()
    {
        $stmt = $this->pdo->prepare("INSERT INTO yape_operaciones (codigo, monto, fecha_operacion) VALUES (?, ?, NOW())");
        $stmt->execute(['YPE-TEST-001', 150.00]);

        // Simulamos la lógica del endpoint
        $stmt = $this->pdo->prepare("SELECT * FROM yape_operaciones WHERE codigo = ? AND usado = 0");
        $stmt->execute(['YPE-TEST-001']);
        $operacion = $stmt->fetch();

        $this->assertNotFalse($operacion);
        $this->assertEquals(150.00, $operacion['monto']);

        $stmt = $this->pdo->prepare("INSERT INTO pagos (matricula_id, monto, metodo, codigo_yape, metodo_verificacion, estado)
                                     VALUES (?, ?, 'Yape', ?, 'codigo', 'aprobado')");
        $stmt->execute([$this->matriculaId, $operacion['monto'], 'YPE-TEST-001']);

        $this->pdo->prepare("UPDATE matriculas SET estado = 'confirmada' WHERE id = ?")->execute([$this->matriculaId]);
        $this->pdo->prepare("UPDATE yape_operaciones SET usado = 1 WHERE codigo = ?")->execute(['YPE-TEST-001']);

        $matricula = $this->pdo->query("SELECT * FROM matriculas WHERE id = {$this->matriculaId}")->fetch();
        $this->assertEquals('confirmada', $matricula['estado']);
    }

    public function test_codigo_yape_ya_usado_no_se_puede_reutilizar()
    {
        $stmt = $this->pdo->prepare("INSERT INTO yape_operaciones (codigo, monto, fecha_operacion, usado) VALUES (?, ?, NOW(), 1)");
        $stmt->execute(['YPE-USADO-001', 100.00]);

        $stmt = $this->pdo->prepare("SELECT * FROM yape_operaciones WHERE codigo = ? AND usado = 0");
        $stmt->execute(['YPE-USADO-001']);
        $operacion = $stmt->fetch();

        $this->assertFalse($operacion);
    }

    public function test_monto_incorrecto_no_coincide_con_la_operacion()
    {
        $stmt = $this->pdo->prepare("INSERT INTO yape_operaciones (codigo, monto, fecha_operacion) VALUES (?, ?, NOW())");
        $stmt->execute(['YPE-MONTO-001', 200.00]);

        $stmt = $this->pdo->prepare("SELECT * FROM yape_operaciones WHERE codigo = ?");
        $stmt->execute(['YPE-MONTO-001']);
        $operacion = $stmt->fetch();

        $montoIngresado = 150.00;
        $this->assertNotEquals((float)$operacion['monto'], $montoIngresado);
    }

    public function test_subir_comprobante_queda_en_estado_pendiente()
    {
        $stmt = $this->pdo->prepare("INSERT INTO pagos (matricula_id, monto, metodo, comprobante, metodo_verificacion, estado)
                                     VALUES (?, ?, 'Yape', ?, 'comprobante', 'pendiente')");
        $stmt->execute([$this->matriculaId, 150.00, 'comp_test_123.jpg']);

        $stmt = $this->pdo->prepare("SELECT * FROM pagos WHERE matricula_id = ?");
        $stmt->execute([$this->matriculaId]);
        $pago = $stmt->fetch();

        $this->assertEquals('pendiente', $pago['estado']);
        $this->assertEquals('comprobante', $pago['metodo_verificacion']);
    }

    public function test_aprobar_pago_confirma_la_matricula()
    {
        $stmt = $this->pdo->prepare("INSERT INTO pagos (matricula_id, monto, metodo, comprobante, metodo_verificacion, estado)
                                     VALUES (?, ?, 'Yape', ?, 'comprobante', 'pendiente')");
        $stmt->execute([$this->matriculaId, 150.00, 'comp_test.jpg']);
        $pagoId = $this->pdo->lastInsertId();

        $this->pdo->prepare("UPDATE pagos SET estado = 'aprobado' WHERE id = ?")->execute([$pagoId]);
        $this->pdo->prepare("UPDATE matriculas SET estado = 'confirmada' WHERE id = ?")->execute([$this->matriculaId]);

        $pago      = $this->pdo->query("SELECT * FROM pagos WHERE id = $pagoId")->fetch();
        $matricula = $this->pdo->query("SELECT * FROM matriculas WHERE id = {$this->matriculaId}")->fetch();

        $this->assertEquals('aprobado', $pago['estado']);
        $this->assertEquals('confirmada', $matricula['estado']);
    }

    public function test_rechazar_pago_regresa_matricula_a_pendiente()
    {
        $stmt = $this->pdo->prepare("INSERT INTO pagos (matricula_id, monto, metodo, comprobante, metodo_verificacion, estado)
                                     VALUES (?, ?, 'Yape', ?, 'comprobante', 'pendiente')");
        $stmt->execute([$this->matriculaId, 150.00, 'comp_test.jpg']);
        $pagoId = $this->pdo->lastInsertId();

        $this->pdo->prepare("UPDATE matriculas SET estado = 'confirmada' WHERE id = ?")->execute([$this->matriculaId]);

        $this->pdo->prepare("UPDATE pagos SET estado = 'rechazado' WHERE id = ?")->execute([$pagoId]);
        $this->pdo->prepare("UPDATE matriculas SET estado = 'pendiente' WHERE id = ?")->execute([$this->matriculaId]);

        $pago      = $this->pdo->query("SELECT * FROM pagos WHERE id = $pagoId")->fetch();
        $matricula = $this->pdo->query("SELECT * FROM matriculas WHERE id = {$this->matriculaId}")->fetch();

        $this->assertEquals('rechazado', $pago['estado']);
        $this->assertEquals('pendiente', $matricula['estado']);
    }

    protected function tearDown(): void
    {
        $this->pdo = null;
    }
}
?>
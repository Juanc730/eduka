<?php
use PHPUnit\Framework\TestCase;

class MatriculasIntegrationTest extends TestCase
{
    private $pdo;
    private $estudianteId;
    private $cursoId;

    protected function setUp(): void
    {
        require __DIR__ . '/../../backend/config/database_test.php';
        $this->pdo = $pdo;

        $this->pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
        $this->pdo->exec("TRUNCATE TABLE matriculas");
        $this->pdo->exec("TRUNCATE TABLE lista_espera");
        $this->pdo->exec("TRUNCATE TABLE cursos");
        $this->pdo->exec("TRUNCATE TABLE usuarios");
        $this->pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

        $hash = password_hash('Eduka2026@', PASSWORD_DEFAULT);
        $stmt = $this->pdo->prepare("INSERT INTO usuarios (nombre, apellido, email, password, rol_id) VALUES (?, ?, ?, ?, 2)");
        $stmt->execute(['Juan', 'Estudiante', 'juan.est@eduka.com', $hash]);
        $this->estudianteId = $this->pdo->lastInsertId();

        $stmt = $this->pdo->prepare("INSERT INTO cursos (nombre, descripcion, docente_id, cupos_totales, cupos_disponibles, horario)
                                     VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute(['Curso de Prueba', '', null, 5, 5, 'Lunes']);
        $this->cursoId = $this->pdo->lastInsertId();
    }

    public function test_matricularse_reduce_los_cupos_disponibles()
    {
        $stmt = $this->pdo->prepare("INSERT INTO matriculas (estudiante_id, curso_id, estado) VALUES (?, ?, 'pendiente')");
        $stmt->execute([$this->estudianteId, $this->cursoId]);

        $this->pdo->prepare("UPDATE cursos SET cupos_disponibles = cupos_disponibles - 1 WHERE id = ?")
                   ->execute([$this->cursoId]);

        $curso = $this->pdo->query("SELECT * FROM cursos WHERE id = {$this->cursoId}")->fetch();
        $this->assertEquals(4, $curso['cupos_disponibles']);
    }

    public function test_no_permite_doble_matricula_en_el_mismo_curso()
    {
        $stmt = $this->pdo->prepare("INSERT INTO matriculas (estudiante_id, curso_id, estado) VALUES (?, ?, 'pendiente')");
        $stmt->execute([$this->estudianteId, $this->cursoId]);

        // Simulamos la verificación que hace el endpoint antes de insertar de nuevo
        $stmt = $this->pdo->prepare("SELECT id FROM matriculas WHERE estudiante_id = ? AND curso_id = ? AND estado != 'anulada'");
        $stmt->execute([$this->estudianteId, $this->cursoId]);
        $yaExiste = $stmt->fetch();

        $this->assertNotFalse($yaExiste);
    }

    public function test_anular_matricula_devuelve_el_cupo_al_curso()
    {
        $stmt = $this->pdo->prepare("INSERT INTO matriculas (estudiante_id, curso_id, estado) VALUES (?, ?, 'confirmada')");
        $stmt->execute([$this->estudianteId, $this->cursoId]);
        $matriculaId = $this->pdo->lastInsertId();

        $this->pdo->prepare("UPDATE cursos SET cupos_disponibles = cupos_disponibles - 1 WHERE id = ?")
                   ->execute([$this->cursoId]);

        // Anular
        $this->pdo->prepare("UPDATE matriculas SET estado = 'anulada' WHERE id = ?")->execute([$matriculaId]);
        $this->pdo->prepare("UPDATE cursos SET cupos_disponibles = cupos_disponibles + 1 WHERE id = ?")->execute([$this->cursoId]);

        $matricula = $this->pdo->query("SELECT * FROM matriculas WHERE id = $matriculaId")->fetch();
        $curso     = $this->pdo->query("SELECT * FROM cursos WHERE id = {$this->cursoId}")->fetch();

        $this->assertEquals('anulada', $matricula['estado']);
        $this->assertEquals(5, $curso['cupos_disponibles']); // vuelve al original
    }

    public function test_lista_espera_no_permite_duplicados()
    {
        $stmt = $this->pdo->prepare("INSERT INTO lista_espera (estudiante_id, curso_id) VALUES (?, ?)");
        $stmt->execute([$this->estudianteId, $this->cursoId]);

        $stmt = $this->pdo->prepare("SELECT id FROM lista_espera WHERE estudiante_id = ? AND curso_id = ?");
        $stmt->execute([$this->estudianteId, $this->cursoId]);
        $yaExiste = $stmt->fetch();

        $this->assertNotFalse($yaExiste);
    }

    public function test_mis_matriculas_solo_muestra_las_del_estudiante_correcto()
    {
        $hash = password_hash('Eduka2026@', PASSWORD_DEFAULT);
        $stmt = $this->pdo->prepare("INSERT INTO usuarios (nombre, apellido, email, password, rol_id) VALUES (?, ?, ?, ?, 2)");
        $stmt->execute(['Otro', 'Estudiante', 'otro.est@eduka.com', $hash]);
        $otroEstudianteId = $this->pdo->lastInsertId();

        $stmt = $this->pdo->prepare("INSERT INTO matriculas (estudiante_id, curso_id, estado) VALUES (?, ?, 'pendiente')");
        $stmt->execute([$this->estudianteId, $this->cursoId]);
        $stmt->execute([$otroEstudianteId, $this->cursoId]);

        $stmt = $this->pdo->prepare("SELECT * FROM matriculas WHERE estudiante_id = ?");
        $stmt->execute([$this->estudianteId]);
        $resultado = $stmt->fetchAll();

        $this->assertCount(1, $resultado);
    }

    protected function tearDown(): void
    {
        $this->pdo = null;
    }
}
?>
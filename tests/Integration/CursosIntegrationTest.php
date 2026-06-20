<?php
use PHPUnit\Framework\TestCase;

class CursosIntegrationTest extends TestCase
{
    private $pdo;
    private $docenteId;

    protected function setUp(): void
    {
        require __DIR__ . '/../../backend/config/database_test.php';
        $this->pdo = $pdo;

        $this->pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
        $this->pdo->exec("TRUNCATE TABLE cursos");
        $this->pdo->exec("TRUNCATE TABLE usuarios");
        $this->pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

        // Crear un docente de prueba para usar en los cursos
        $hash = password_hash('Eduka2026@', PASSWORD_DEFAULT);
        $stmt = $this->pdo->prepare("INSERT INTO usuarios (nombre, apellido, email, password, rol_id) VALUES (?, ?, ?, ?, 3)");
        $stmt->execute(['Carlos', 'Docente', 'carlos@eduka.com', $hash]);
        $this->docenteId = $this->pdo->lastInsertId();
    }

    public function test_se_puede_crear_un_curso_con_docente_asignado()
    {
        $stmt = $this->pdo->prepare("INSERT INTO cursos (nombre, descripcion, docente_id, cupos_totales, cupos_disponibles, horario)
                                     VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute(['Programación Web', 'Curso introductorio', $this->docenteId, 20, 20, 'Lunes y Miércoles']);

        $stmt = $this->pdo->prepare("SELECT * FROM cursos WHERE nombre = ?");
        $stmt->execute(['Programación Web']);
        $curso = $stmt->fetch();

        $this->assertNotFalse($curso);
        $this->assertEquals(20, $curso['cupos_disponibles']);
        $this->assertEquals($this->docenteId, $curso['docente_id']);
    }

    public function test_se_puede_crear_un_curso_sin_docente_asignado()
    {
        $stmt = $this->pdo->prepare("INSERT INTO cursos (nombre, descripcion, docente_id, cupos_totales, cupos_disponibles, horario)
                                     VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute(['Curso Sin Docente', '', null, 15, 15, 'Viernes']);

        $stmt = $this->pdo->prepare("SELECT * FROM cursos WHERE nombre = ?");
        $stmt->execute(['Curso Sin Docente']);
        $curso = $stmt->fetch();

        $this->assertNotFalse($curso);
        $this->assertNull($curso['docente_id']);
    }

    public function test_editar_curso_ajusta_cupos_disponibles_proporcionalmente()
    {
        $stmt = $this->pdo->prepare("INSERT INTO cursos (nombre, descripcion, docente_id, cupos_totales, cupos_disponibles, horario)
                                     VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute(['Curso Test', '', $this->docenteId, 20, 15, 'Lunes']); // ya tiene 5 ocupados
        $id = $this->pdo->lastInsertId();

        // Simulamos la lógica del endpoint: aumentar cupos_totales de 20 a 25 (+5)
        $curso = $this->pdo->query("SELECT * FROM cursos WHERE id = $id")->fetch();
        $diferencia = 25 - $curso['cupos_totales'];
        $nuevosDisponibles = max(0, $curso['cupos_disponibles'] + $diferencia);

        $stmt = $this->pdo->prepare("UPDATE cursos SET cupos_totales = ?, cupos_disponibles = ? WHERE id = ?");
        $stmt->execute([25, $nuevosDisponibles, $id]);

        $actualizado = $this->pdo->query("SELECT * FROM cursos WHERE id = $id")->fetch();
        $this->assertEquals(25, $actualizado['cupos_totales']);
        $this->assertEquals(20, $actualizado['cupos_disponibles']); // 15 + 5
    }

    public function test_eliminar_curso_lo_marca_como_inactivo_sin_borrarlo()
    {
        $stmt = $this->pdo->prepare("INSERT INTO cursos (nombre, descripcion, docente_id, cupos_totales, cupos_disponibles, horario)
                                     VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute(['Curso a Eliminar', '', $this->docenteId, 10, 10, 'Martes']);
        $id = $this->pdo->lastInsertId();

        $this->pdo->prepare("UPDATE cursos SET estado = 'inactivo' WHERE id = ?")->execute([$id]);

        $curso = $this->pdo->query("SELECT * FROM cursos WHERE id = $id")->fetch();
        $this->assertEquals('inactivo', $curso['estado']);

        // Confirmar que el registro sigue existiendo (no se borró)
        $this->assertNotFalse($curso);
    }

    public function test_listar_cursos_activos_excluye_los_inactivos()
    {
        $stmt = $this->pdo->prepare("INSERT INTO cursos (nombre, descripcion, docente_id, cupos_totales, cupos_disponibles, horario, estado)
                                     VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute(['Curso Activo', '', $this->docenteId, 10, 10, 'Lunes', 'activo']);
        $stmt->execute(['Curso Inactivo', '', $this->docenteId, 10, 10, 'Martes', 'inactivo']);

        $stmt = $this->pdo->query("SELECT * FROM cursos WHERE estado = 'activo'");
        $cursosActivos = $stmt->fetchAll();

        $this->assertCount(1, $cursosActivos);
        $this->assertEquals('Curso Activo', $cursosActivos[0]['nombre']);
    }

    public function test_mis_cursos_filtra_solo_por_el_docente_correcto()
    {
        // Crear un segundo docente
        $hash = password_hash('Eduka2026@', PASSWORD_DEFAULT);
        $stmt = $this->pdo->prepare("INSERT INTO usuarios (nombre, apellido, email, password, rol_id) VALUES (?, ?, ?, ?, 3)");
        $stmt->execute(['Ana', 'Otra Docente', 'ana.docente@eduka.com', $hash]);
        $otroDocenteId = $this->pdo->lastInsertId();

        $stmt = $this->pdo->prepare("INSERT INTO cursos (nombre, descripcion, docente_id, cupos_totales, cupos_disponibles, horario)
                                     VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute(['Curso de Carlos', '', $this->docenteId, 10, 10, 'Lunes']);
        $stmt->execute(['Curso de Ana', '', $otroDocenteId, 10, 10, 'Martes']);

        $stmt = $this->pdo->prepare("SELECT * FROM cursos WHERE docente_id = ?");
        $stmt->execute([$this->docenteId]);
        $cursosDeCarlos = $stmt->fetchAll();

        $this->assertCount(1, $cursosDeCarlos);
        $this->assertEquals('Curso de Carlos', $cursosDeCarlos[0]['nombre']);
    }

    protected function tearDown(): void
    {
        $this->pdo = null;
    }
}
?>
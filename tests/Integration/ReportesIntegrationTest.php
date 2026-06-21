<?php
use PHPUnit\Framework\TestCase;

class ReportesIntegrationTest extends TestCase
{
    private $pdo;
    private $docenteId;
    private $otroDocenteId;
    private $cursoId;

    protected function setUp(): void
    {
        require __DIR__ . '/../../backend/config/database_test.php';
        $this->pdo = $pdo;

        $this->pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
        $this->pdo->exec("TRUNCATE TABLE pagos");
        $this->pdo->exec("TRUNCATE TABLE matriculas");
        $this->pdo->exec("TRUNCATE TABLE cursos");
        $this->pdo->exec("TRUNCATE TABLE usuarios");
        $this->pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

        $hash = password_hash('Eduka2026@', PASSWORD_DEFAULT);

        $stmt = $this->pdo->prepare("INSERT INTO usuarios (nombre, apellido, email, password, rol_id) VALUES (?, ?, ?, ?, 3)");
        $stmt->execute(['Carlos', 'Docente', 'carlos.rep@eduka.com', $hash]);
        $this->docenteId = $this->pdo->lastInsertId();

        $stmt->execute(['Ana', 'OtraDocente', 'ana.rep@eduka.com', $hash]);
        $this->otroDocenteId = $this->pdo->lastInsertId();

        $stmt = $this->pdo->prepare("INSERT INTO cursos (nombre, descripcion, docente_id, cupos_totales, cupos_disponibles, horario)
                                     VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute(['Curso Reportes', '', $this->docenteId, 10, 7, 'Lunes']);
        $this->cursoId = $this->pdo->lastInsertId();
    }

    public function test_reporte_matriculas_solo_muestra_cursos_del_docente_correcto()
    {
        $stmt = $this->pdo->prepare("INSERT INTO cursos (nombre, descripcion, docente_id, cupos_totales, cupos_disponibles, horario)
                                     VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute(['Curso de Otra Docente', '', $this->otroDocenteId, 10, 10, 'Martes']);

        $stmt = $this->pdo->prepare("SELECT id, nombre FROM cursos WHERE docente_id = ? AND estado = 'activo'");
        $stmt->execute([$this->docenteId]);
        $cursosDelDocente = $stmt->fetchAll();

        $this->assertCount(1, $cursosDelDocente);
        $this->assertEquals('Curso Reportes', $cursosDelDocente[0]['nombre']);
    }

    public function test_reporte_matriculas_calcula_totales_correctamente()
    {
        $hash = password_hash('Eduka2026@', PASSWORD_DEFAULT);
        $stmt = $this->pdo->prepare("INSERT INTO usuarios (nombre, apellido, email, password, rol_id) VALUES (?, ?, ?, ?, 2)");
        $stmt->execute(['Est1', 'Prueba', 'est1.rep@eduka.com', $hash]);
        $est1 = $this->pdo->lastInsertId();
        $stmt->execute(['Est2', 'Prueba', 'est2.rep@eduka.com', $hash]);
        $est2 = $this->pdo->lastInsertId();

        $stmt = $this->pdo->prepare("INSERT INTO matriculas (estudiante_id, curso_id, estado) VALUES (?, ?, ?)");
        $stmt->execute([$est1, $this->cursoId, 'confirmada']);
        $stmt->execute([$est2, $this->cursoId, 'pendiente']);

        $stmt = $this->pdo->prepare("SELECT * FROM matriculas WHERE curso_id = ?");
        $stmt->execute([$this->cursoId]);
        $matriculas = $stmt->fetchAll();

        $confirmadas = count(array_filter($matriculas, fn($m) => $m['estado'] === 'confirmada'));
        $pendientes  = count(array_filter($matriculas, fn($m) => $m['estado'] === 'pendiente'));

        $this->assertCount(2, $matriculas);
        $this->assertEquals(1, $confirmadas);
        $this->assertEquals(1, $pendientes);
    }

    public function test_reporte_pagos_calcula_total_recaudado_solo_de_aprobados()
    {
        $hash = password_hash('Eduka2026@', PASSWORD_DEFAULT);
        $stmt = $this->pdo->prepare("INSERT INTO usuarios (nombre, apellido, email, password, rol_id) VALUES (?, ?, ?, ?, 2)");
        $stmt->execute(['Est', 'Prueba', 'est.pagos.rep@eduka.com', $hash]);
        $estId = $this->pdo->lastInsertId();

        $stmt = $this->pdo->prepare("INSERT INTO matriculas (estudiante_id, curso_id, estado) VALUES (?, ?, 'confirmada')");
        $stmt->execute([$estId, $this->cursoId]);
        $matId = $this->pdo->lastInsertId();

        $stmt = $this->pdo->prepare("INSERT INTO pagos (matricula_id, monto, metodo, metodo_verificacion, estado) VALUES (?, ?, 'Yape', 'codigo', ?)");
        $stmt->execute([$matId, 100.00, 'aprobado']);
        $stmt->execute([$matId, 50.00, 'pendiente']); // no debe contar en el total recaudado

        $stmt = $this->pdo->prepare("SELECT * FROM pagos WHERE matricula_id = ?");
        $stmt->execute([$matId]);
        $pagos = $stmt->fetchAll();

        $totalAprobado = array_sum(array_map(fn($p) => $p['estado'] === 'aprobado' ? (float)$p['monto'] : 0, $pagos));

        $this->assertEquals(100.00, $totalAprobado);
    }

    public function test_reporte_cursos_calcula_ocupados_correctamente()
    {
        $stmt = $this->pdo->query("SELECT *, (cupos_totales - cupos_disponibles) AS ocupados FROM cursos WHERE id = {$this->cursoId}");
        $curso = $stmt->fetch();

        // cupos_totales = 10, cupos_disponibles = 7 → ocupados debería ser 3
        $this->assertEquals(3, $curso['ocupados']);
    }

    public function test_reporte_cursos_excluye_cursos_inactivos()
    {
        $stmt = $this->pdo->prepare("INSERT INTO cursos (nombre, descripcion, docente_id, cupos_totales, cupos_disponibles, horario, estado)
                                     VALUES (?, ?, ?, ?, ?, ?, 'inactivo')");
        $stmt->execute(['Curso Inactivo Reporte', '', $this->docenteId, 10, 10, 'Miércoles']);

        $stmt = $this->pdo->query("SELECT * FROM cursos WHERE estado = 'activo'");
        $cursosActivos = $stmt->fetchAll();

        $nombresActivos = array_column($cursosActivos, 'nombre');
        $this->assertNotContains('Curso Inactivo Reporte', $nombresActivos);
    }

    protected function tearDown(): void
    {
        $this->pdo = null;
    }
}
?>
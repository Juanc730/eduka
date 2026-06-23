# Instituto Eduka — Sistema de Matrícula Web

Sistema web de gestión de matrículas para el Instituto Eduka. Permite a estudiantes matricularse en cursos, realizar pagos (verificación automática por código Yape o manual por comprobante), y a administradores y docentes gestionar cursos, usuarios, matrículas y generar reportes.

## Arquitectura

API REST desacoplada de un frontend estático:

- **Backend:** PHP 8.2 + MySQL, autenticación mediante JSON Web Tokens (JWT)
- **Frontend:** HTML + JavaScript puro (sin frameworks), consumiendo la API vía `fetch`
- **Base de datos:** MySQL (XAMPP en desarrollo local)

```
Navegador (Frontend) ──fetch/JWT──> API REST (Backend PHP) ──PDO──> MySQL
```

## Estructura del proyecto

```
eduka/
├── backend/
│   ├── api/                 # Endpoints organizados por módulo
│   │   ├── auth/
│   │   ├── usuarios/
│   │   ├── cursos/
│   │   ├── matriculas/
│   │   ├── pagos/
│   │   └── reportes/
│   ├── config/               # Conexión a base de datos
│   ├── helpers/               # JWT, respuestas JSON, CSRF
│   └── middleware/            # Verificación de autenticación y roles
├── frontend/
│   ├── assets/
│   │   ├── css/
│   │   └── js/                # Cliente API (fetch + manejo de token)
│   └── pages/                 # Una página HTML por vista
├── database/
│   └── schema.sql              # Esquema completo de la base de datos
├── tests/
│   ├── Unit/                   # Pruebas unitarias (PHPUnit)
│   ├── Integration/             # Pruebas de integración (PHPUnit)
│   ├── cypress/                 # Pruebas funcionales E2E (Cypress)
│   └── jmeter/                  # Plan de pruebas de carga (JMeter)
├── uploads/comprobantes/        # Comprobantes de pago subidos por estudiantes
├── .github/workflows/           # CI/CD (GitHub Actions)
├── PRUEBAS.md                   # Casos de prueba manuales
├── PRUEBAS_ESTRES.md            # Resultados de pruebas de carga (JMeter)
└── PRUEBAS_SEGURIDAD.md         # Resultados de escaneo de seguridad (OWASP ZAP)
```

## Módulos funcionales

|     Módulo    |                                 Descripción                                 |
|---------------|-----------------------------------------------------------------------------|
| Autenticación | Registro, login con JWT, límite de intentos, verificación de sesión         |
|    Usuarios   | Gestión de estudiantes, docentes y administradores (solo admin)             |
|     Cursos    | Creación, edición y consulta de cursos con cupos                            |
|   Matrículas  | Inscripción, lista de espera, gestión administrativa                        |
|     Pagos     | Verificación automática por código Yape (simulado) o manual por comprobante |
|    Reportes   | Matrículas por curso, estado de pagos, ocupación de cursos                  |

## Roles del sistema

- **Administrador:** acceso completo a todos los módulos
- **Docente:** ve sus cursos asignados y reportes de sus propios estudiantes
- **Estudiante:** consulta cursos, se matricula, registra pagos

## Instalación local

### Requisitos
- XAMPP (PHP 8.2+, MySQL, Apache)
- Composer
- Node.js 20+ y npm
- Java (para ejecutar JMeter)

### Pasos

1. Clona el repositorio dentro de `htdocs/`:
   ```bash
   git clone <url-del-repo> eduka
   cd eduka
   ```

2. Configura la base de datos:
   - Crea una base de datos `eduka_db` en phpMyAdmin
   - Ejecuta el script `database/schema.sql`
   - Copia `backend/config/database.example.php` a `backend/config/database.php` y ajusta las credenciales si es necesario

3. Instala las dependencias de PHP:
   ```bash
   composer install
   ```

4. Instala las dependencias de Node (para Cypress):
   ```bash
   npm install
   ```

5. Inicia Apache y MySQL desde el panel de XAMPP

6. Abre el sistema en el navegador:
   ```
   http://localhost/eduka/frontend/pages/login.html
   ```

### Cuenta de administrador por defecto
Después de ejecutar el schema, crea un usuario administrador insertándolo manualmente o usando el endpoint de registro y cambiando su `rol_id` a `1` en la base de datos.

## Ejecutar las pruebas

**Pruebas unitarias e integración (PHPUnit):**
```bash
vendor/bin/phpunit --testsuite Unit
vendor/bin/phpunit --testsuite Integration
```
Requiere una base de datos separada `eduka_test` con el mismo esquema, configurada en `backend/config/database_test.php`.

**Pruebas funcionales (Cypress):**
```bash
cd tests
npx cypress open
```

**Pruebas de carga (JMeter):**
```bash
cd apache-jmeter-5.6.3/bin
./jmeter -n -t ../../tests/jmeter/plan-carga-login.jmx -l resultados.jtl -e -o reporte-html
```

## Integración continua (CI/CD)

El proyecto usa GitHub Actions con tres workflows independientes:

|       Workflow      |       Disparador      |                     Qué hace                     |
|---------------------|-----------------------|--------------------------------------------------|
|     `tests.yml`     | Cada push/PR a `main` | Verificación de sintaxis PHP → PHPUnit → Cypress |
| `security-scan.yml` | Cada push/PR a `main` | Escaneo de seguridad con OWASP ZAP               |
|  `stress-test.yml`  | Cada push/PR a `main` | Pruebas de carga con JMeter                      |

## Tecnologías utilizadas

- **Backend:** PHP 8.2, PDO, JWT (implementación manual)
- **Frontend:** HTML5, CSS3, JavaScript (ES6+)
- **Base de datos:** MySQL
- **Testing:** PHPUnit, Cypress, Apache JMeter, OWASP ZAP
- **CI/CD:** GitHub Actions
- **Control de versiones:** Git / GitHub

## Autor
Juan Carlos De la Cruz Corrales

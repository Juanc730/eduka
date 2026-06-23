# Plan de Pruebas — Instituto Eduka

## Información general
- **Proyecto:** Sistema de matrícula Instituto Eduka — API REST + Frontend desacoplado
- **Versión:** 2.0 (arquitectura API + JWT)
- **Fecha:** junio 2026
- **Backend:** PHP 8.2, MySQL, autenticación JWT
- **Frontend:** HTML + JavaScript puro (sin frameworks)
- **Herramientas:** XAMPP, navegador web, Thunder Client / Postman

## Cuentas de prueba sugeridas
|      Rol      |             Correo              |  Contraseña |
|---------------|---------------------------------|-------------|
| Administrador |         admin@eduka.com         | password123 |
|   Estudiante  |   (crear desde registro.html)   | ——————————— |
|    Docente    | (crear desde panel de usuarios) | ——————————— |

---

## Módulo 1 — Autenticación

|  ID |     Caso de prueba     |   Datos de entrada   |         Resultado esperado         |          Resultado obtenido          | Estado |
|-----|------------------------|----------------------|------------------------------------|--------------------------------------|--------|
| P01 | Login con credenciales |    admin@eduka.com   | Devuelve token JWT, redirige a     |                                      |        |
|     | correctas              |      password123     | dashboard.html                     |                                      |        |
|-----|------------------------|----------------------|------------------------------------|--------------------------------------|--------|
| P02 | Login con contraseña   |   admin@eduka.com    | Error 401, mensaje "Correo o       |                                      |        |
|     | incorrecta             |  contraseña errónea  | contraseña incorrectos" con        |                                      |        |
|     |                        |                      |  contador de intentos restantes    |                                      |        |
|-----|------------------------|----------------------|------------------------------------|--------------------------------------|--------|
| P03 | Login con email        |      correo no       | Error 401, mismo mensaje genérico  |                                      |        |
|     | inexistente            |      registrado      | (no revela si el email existe)     |                                      |        |
|-----|------------------------|----------------------|------------------------------------|--------------------------------------|--------|
| P04 | Bloqueo tras 5         | 5 intentos con       | Error 429, formulario oculto,      |                                      |        |
|     | intentos fallidos      | contraseña           | mensaje de bloqueo temporal        |                                      |        |
|     |                        | incorrecta desde la  |                                    |                                      |        |
|     |                        | misma IP             |                                    |                                      |        |
|-----|------------------------|----------------------|------------------------------------|--------------------------------------|--------|
| P05 | Registro con datos     | nombre, apellido,    | Cuenta creada con rol estudiante,  |                                      |        |
|     | completos y contraseña | email nuevo,         | redirige al login                  |                                      |        |
|     | válida                 | contraseña que       |                                    |                                      |        |
|     |                        | cumple los 5         |                                    |                                      |        |
|     |                        | requisitos           |                                    |                                      |        |
|-----|------------------------|----------------------|------------------------------------|--------------------------------------|--------|
| P06 | Registro con email ya  |    email existente   | Error 409, "El correo ya está      |                                      |        |
|     | registrado             |                      | registrado"                        |                                      |        |
|-----|------------------------|----------------------|------------------------------------|--------------------------------------|--------|
| P07 | Registro con           | password ≠           | Error 400, "Las contraseñas no     |                                      |        |
|     | contraseñas que no     | confirm_password     | coinciden"                         |                                      |        |
|     | coinciden              |                      |                                    |                                      |        |
|-----|------------------------|----------------------|------------------------------------|--------------------------------------|--------|
| P08 | Registro con           | contraseña sin       | Error 400 con el requisito         |                                      |        |
|     | contraseña débil       | mayúscula/número/esp | específico que falta               |                                      |        |
|     |                        | ecial                |                                    |                                      |        |
|-----|------------------------|----------------------|------------------------------------|--------------------------------------|--------|
| P09 | Verificación de        | escribir en el campo | Lista de requisitos se marca ✓ o   |                                      |        |
|     | requisitos de          | contraseña           | ✗ dinámicamente                    |                                      |        |
|     | contraseña en tiempo   |                      |                                    |                                      |        |
|     | real                   |                      |                                    |                                      |        |
|-----|------------------------|----------------------|------------------------------------|--------------------------------------|--------|
| P10 | Botón mostrar/ocultar  | clic en ícono 👁️    | Alterna el campo entre tipo         |                                     |        |
|     | contraseña             |                      | password y texto visible           |                                      |        |
|-----|------------------------|----------------------|------------------------------------|--------------------------------------|--------|
| P11 | Acceso a endpoint      | GET a /auth/me.php   | Error 401                          |                                      |        |
|     | protegido sin token    | sin header           |                                    |                                      |        |
|     |                        | Authorization        |                                    |                                      |        |
|-----|------------------------|----------------------|------------------------------------|--------------------------------------|--------|
| P12 | Acceso a endpoint      | header Authorization | Error 401                          |                                      |        |
|     | protegido con token    | con token corrupto   |                                    |                                      |        |
|     | inválido o expirado    |                      |                                    |                                      |        |
|-----|------------------------|----------------------|------------------------------------|--------------------------------------|--------|
| P13 | Cerrar sesión          | clic en "Cerrar      | Token eliminado de localStorage,   |                                      |        |
|     |                        | sesión" + confirmar  | redirige al login                  |                                      |        |
|-----|------------------------|----------------------|------------------------------------|--------------------------------------|--------|
| P14 | Acceso a               | abrir URL            | Redirige automáticamente a         |                                      |        |
|     | dashboard.html sin     | directamente sin     | login.html                         |                                      |        |
|     | sesión iniciada        | token                |                                    |                                      |        |
|-----|------------------------|----------------------|------------------------------------|--------------------------------------|--------|
| P15 | Cuenta desactivada     | admin desactiva al   | La siguiente petición protegida    |                                      |        |
|     | intenta usar un token  | usuario mientras     | devuelve error y cierra la sesión  |                                      |        |
|     | ya emitido             | tiene sesión activa  |                                    |                                      |        |

---

## Módulo 2 — Gestión de Usuarios (Admin)

|  ID |     Caso de prueba     |   Datos de entrada   |         Resultado esperado         |          Resultado obtenido          | Estado |
|-----|------------------------|----------------------|------------------------------------|--------------------------------------|--------|
| P16 | Listar usuarios con    | acceder a            | Tabla con 10 usuarios por página,  |                                      |        |
|     | paginación             | usuarios.html        | controles de paginación            |                                      |        |
|-----|------------------------|----------------------|------------------------------------|--------------------------------------|--------|
| P17 | Buscar usuario por     | escribir término en  | Resultados filtrados desde la base |                                      |        |
|     | nombre, correo o rol   | el buscador          | de datos, contador de resultados   |                                      |        |
|-----|------------------------|----------------------|------------------------------------|--------------------------------------|--------|
| P18 | Crear usuario docente  | datos completos, rol | Usuario creado, aparece en la      |                                      |        |
|     |                        | = docente,           | lista                              |                                      |        |
|     |                        | contraseña válida    |                                    |                                      |        |
|-----|------------------------|----------------------|------------------------------------|--------------------------------------|--------|
| P19 | Crear usuario con      | email ya existente   | Error 409 mostrado en el modal     |                                      |        |
|     | email duplicado        |                      |                                    |                                      |        |
|-----|------------------------|----------------------|------------------------------------|--------------------------------------|--------|
| P20 | Editar usuario sin     | dejar campos de      | Datos actualizados, contraseña     |                                      |        |
|     | cambiar la contraseña  | contraseña vacíos    | original se mantiene               |                                      |        |
|-----|------------------------|----------------------|------------------------------------|--------------------------------------|--------|
| P21 | Editar usuario         | nueva contraseña     | Contraseña actualizada con nuevo   |                                      |        |
|     | cambiando la           | válida +             | hash                               |                                      |        |
|     | contraseña             | confirmación         |                                    |                                      |        |
|-----|------------------------|----------------------|------------------------------------|--------------------------------------|--------|
| P22 | Activar / desactivar   | clic en botón        | Estado cambia, badge se actualiza  |                                      |        |
|     | usuario                | correspondiente +    |                                    |                                      |        |
|     |                        | confirmar            |                                    |                                      |        |
|-----|------------------------|----------------------|------------------------------------|--------------------------------------|--------|
| P23 | Intentar que el admin  | admin intenta        | Error 403, "No puedes desactivar   |                                      |        |
|     | se desactive a sí      | desactivar su propia | tu propia cuenta"                  |                                      |        |
|     | mismo                  | cuenta               |                                    |                                      |        |
|-----|------------------------|----------------------|------------------------------------|--------------------------------------|--------|
| P24 | Acceso de un           | token de estudiante  | Error 403                          |                                      |        |
|     | no-administrador a     | o docente            |                                    |                                      |        |
|     | /usuarios/listar.php   |                      |                                    |                                      |        |

---

## Módulo 3 — Gestión de Cursos

|  ID |     Caso de prueba     |   Datos de entrada   |         Resultado esperado         |          Resultado obtenido          | Estado |
|-----|------------------------|----------------------|------------------------------------|--------------------------------------|--------|
| P25 | Listar cursos activos  | cualquier rol        | Lista de cursos con indicador de   |                                      |        |
|     |                        | autenticado          | cupos por colores                  |                                      |        |
|-----|------------------------|----------------------|------------------------------------|--------------------------------------|--------|
| P26 | Crear curso con        | nombre, docente,     | Curso creado y visible en la lista |                                      |        |
|     | docente asignado       | cupos, horario       |                                    |                                      |        |
|-----|------------------------|----------------------|------------------------------------|--------------------------------------|--------|
| P27 | Crear curso sin campos | nombre vacío         | Error 400                          |                                      |        |
|     | obligatorios           |                      |                                    |                                      |        |
|-----|------------------------|----------------------|------------------------------------|--------------------------------------|--------|
| P28 | Editar curso           | cupos_totales mayor  | cupos_disponibles se ajusta        |                                      |        |
|     | aumentando cupos       | al actual            | proporcionalmente (+diferencia)    |                                      |        |
|     | totales                |                      |                                    |                                      |        |
|-----|------------------------|----------------------|------------------------------------|--------------------------------------|--------|
| P29 | Eliminar curso         | clic en "Eliminar" + | Curso pasa a estado inactivo, ya   |                                      |        |
|     | (lógicamente)          | confirmar            | no aparece en listados activos     |                                      |        |
|-----|------------------------|----------------------|------------------------------------|--------------------------------------|--------|
| P30 | Estudiante ve          | cursos con distintos | Verde (>50%), amarillo (≤50%),     |                                      |        |
|     | indicador de cupos por | niveles de ocupación | naranja (≤25%), rojo (0)           |                                      |        |
|     | color                  |                      |                                    |                                      |        |
|-----|------------------------|----------------------|------------------------------------|--------------------------------------|--------|
| P31 | Estudiante no ve       | acceder a            | Solo se muestran botones de        |                                      |        |
|     | botones de             | cursos.html como     | matrícula                          |                                      |        |
|     | editar/eliminar        | estudiante           |                                    |                                      |        |
|-----|------------------------|----------------------|------------------------------------|--------------------------------------|--------|
| P32 | Docente ve solo sus    | acceder a            | Lista filtrada únicamente por      |                                      |        |
|     | cursos asignados       | mis-cursos.html      | docente_id del usuario logueado    |                                      |        |
|-----|------------------------|----------------------|------------------------------------|--------------------------------------|--------|
| P33 | Acceso de estudiante a | token de estudiante  | Error 403                          |                                      |        |
|     | /cursos/crear.php      |                      |                                    |                                      |        |

---

## Módulo 4 — Matrículas

|  ID |     Caso de prueba     |   Datos de entrada   |         Resultado esperado         |          Resultado obtenido          | Estado |
|-----|------------------------|----------------------|------------------------------------|--------------------------------------|--------|
| P34 | Matricularse en curso  | clic en              | Matrícula creada en estado         |                                      |        |
|     | con cupos disponibles  | "Matricularme" +     | pendiente, cupo del curso se       |                                      |        |
|     |                        | confirmar            | reduce en 1                        |                                      |        |
|-----|------------------------|----------------------|------------------------------------|--------------------------------------|--------|
| P35 | Intentar matricularse  | mismo estudiante,    | Error 409, "Ya estás matriculado   |                                      |        |
|     | dos veces en el mismo  | mismo curso          | en este curso"                     |                                      |        |
|     | curso                  |                      |                                    |                                      |        |
|-----|------------------------|----------------------|------------------------------------|--------------------------------------|--------|
| P36 | Matricularse en curso  | curso con            | Botón cambia a "Unirme a lista de  |                                      |        |
|     | sin cupos              | cupos_disponibles= 0 | espera"                            |                                      |        |
|-----|------------------------|----------------------|------------------------------------|--------------------------------------|--------|
| P37 | Unirse a lista de      | clic en el botón     | Registro creado en tabla           |                                      |        |
|     | espera                 | correspondiente      | lista_espera                       |                                      |        |
|-----|------------------------|----------------------|------------------------------------|--------------------------------------|--------|
| P38 | Ver Mis Matrículas     | acceder a            | Tabla con curso, estado, y acción  |                                      |        |
|     |                        | mis-matriculas.html  | según si tiene pago registrado     |                                      |        |
|     |                        | como estudiante      |                                    |                                      |        |
|-----|------------------------|----------------------|------------------------------------|--------------------------------------|--------|
| P39 | Admin filtra           | seleccionar          | Solo se muestran matrículas con    |                                      |        |
|     | matrículas por estado  | "pendiente" en el    | ese estado                         |                                      |        |
|     |                        | filtro               |                                    |                                      |        |
|-----|------------------------|----------------------|------------------------------------|--------------------------------------|--------|
| P40 | Admin busca matrícula  | escribir término de  | Resultados filtrados desde base de |                                      |        |
|     | por estudiante o curso | búsqueda             | datos                              |                                      |        |
|-----|------------------------|----------------------|------------------------------------|--------------------------------------|--------|
| P41 | Admin anula una        | clic en "Anular" +   | Estado cambia a anulada, cupo del  |                                      |        |
|     | matrícula              | confirmar            | curso se incrementa en 1           |                                      |        |
|-----|------------------------|----------------------|------------------------------------|--------------------------------------|--------|
| P42 | Acceso de estudiante a | token de estudiante  | Error 403                          |                                      |        |
|     | /matriculas/listar.php |                      |                                    |                                      |        |
|     | (admin)                |                      |                                    |                                      |        |

---

## Módulo 5 — Pagos

|  ID |     Caso de prueba     |   Datos de entrada   |         Resultado esperado         |          Resultado obtenido          | Estado |
|-----|------------------------|----------------------|------------------------------------|--------------------------------------|--------|
| P43 | Pago con código Yape   | código precargado    | Pago se aprueba automáticamente,   |                                      |        |
|     | válido y monto         | por el admin         | matrícula pasa a confirmada        |                                      |        |
|     | correcto               |                      |                                    |                                      |        |
|-----|------------------------|----------------------|------------------------------------|--------------------------------------|--------|
| P44 | Pago con código Yape   | código no registrado | Error 404, "Código de operación    |                                      |        |
|     | inexistente            |                      | inválido"                          |                                      |        |
|-----|------------------------|----------------------|------------------------------------|--------------------------------------|--------|
| P45 | Pago con código Yape   | código previamente   | Error 404 (el filtro usado=0 lo    |                                      |        |
|     | ya utilizado           | usado en otro pago   | excluye)                           |                                      |        |
|-----|------------------------|----------------------|------------------------------------|--------------------------------------|--------|
| P46 | Pago con monto que no  | monto distinto al    | Error 400, "El monto no coincide"  |                                      |        |
|     | coincide con el código | registrado para ese  |                                    |                                      |        |
|     |                        | código               |                                    |                                      |        |
|-----|------------------------|----------------------|------------------------------------|--------------------------------------|--------|
| P47 | Subir comprobante con  | archivo JPG/PNG/WEBP | Pago registrado en estado          |                                      |        |
|     | imagen válida          | menor a 5MB          | pendiente                          |                                      |        |
|-----|------------------------|----------------------|------------------------------------|--------------------------------------|--------|
| P48 | Subir comprobante con  | archivo .pdf o .exe  | Error 400, "Solo se permiten       |                                      |        |
|     | archivo no permitido   |                      | imágenes..."                       |                                      |        |
|-----|------------------------|----------------------|------------------------------------|--------------------------------------|--------|
| P49 | Admin aprueba pago con | clic en "Aprobar" +  | Pago aprobado, matrícula           |                                      |        |
|     | comprobante            | confirmar            | confirmada                         |                                      |        |
|-----|------------------------|----------------------|------------------------------------|--------------------------------------|--------|
| P50 | Admin rechaza pago con | clic en "Rechazar" + | Pago rechazado, matrícula vuelve a |                                      |        |
|     | comprobante            | confirmar            | pendiente                          |                                      |        |
|-----|------------------------|----------------------|------------------------------------|--------------------------------------|--------|
| P51 | Reintentar pago tras   | subir nuevo          | Pago anterior rechazado se         |                                      |        |
|     | rechazo                | comprobante para la  | elimina, se crea uno nuevo         |                                      |        |
|     |                        | misma matrícula      | pendiente                          |                                      |        |
|-----|------------------------|----------------------|------------------------------------|--------------------------------------|--------|
| P52 | Ver Mis Pagos          | acceder a            | Lista de pagos con método de       |                                      |        |
|     |                        | mis-pagos.html       | verificación y estado              |                                      |        |
|-----|------------------------|----------------------|------------------------------------|--------------------------------------|--------|
| P53 | Admin carga un nuevo   | código, monto, fecha | Código disponible para             |                                      |        |
|     | código Yape            | de operación         | verificación de estudiantes        |                                      |        |
|-----|------------------------|----------------------|------------------------------------|--------------------------------------|--------|
| P54 | Admin intenta cargar   | código ya existente  | Error 409                          |                                      |        |
|     | un código duplicado    |                      |                                    |                                      |        |

---

## Módulo 6 — Reportes

|  ID |     Caso de prueba     |   Datos de entrada   |         Resultado esperado         |          Resultado obtenido          | Estado |
|-----|------------------------|----------------------|------------------------------------|--------------------------------------|--------|
| P55 | Admin ve los 3 tipos   | acceder a            | Tarjetas: matrículas, pagos,       |                                      |        |
|     | de reporte             | reportes.html        | ocupación de cursos                |                                      |        |
|-----|------------------------|----------------------|------------------------------------|--------------------------------------|--------|
| P56 | Docente ve solo "Mis   | acceder a            | Solo aparece la tarjeta de         |                                      |        |
|     | estudiantes"           | reportes.html como   | matrículas, sin pagos ni ocupación |                                      |        |
|     |                        | docente              |                                    |                                      |        |
|-----|------------------------|----------------------|------------------------------------|--------------------------------------|--------|
| P57 | Reporte de matrículas  | seleccionar un curso | Tabla y resumen con confirmados,   |                                      |        |
|     | por curso (admin)      |                      | pendientes, total recaudado        |                                      |        |
|-----|------------------------|----------------------|------------------------------------|--------------------------------------|--------|
| P58 | Reporte de matrículas  | docente selecciona   | Solo ve matrículas de cursos que   |                                      |        |
|     | filtrado por docente   | uno de sus cursos    | le pertenecen                      |                                      |        |
|-----|------------------------|----------------------|------------------------------------|--------------------------------------|--------|
| P59 | Docente intenta ver    | manipular curso_id   | Error 403                          |                                      |        |
|     | curso que no le        | en la URL del        |                                    |                                      |        |
|     | pertenece              | request              |                                    |                                      |        |
|-----|------------------------|----------------------|------------------------------------|--------------------------------------|--------|
| P60 | Reporte de pagos con   | acceder como admin   | Total recaudado solo suma pagos en |                                      |        |
|     | totales correctos      |                      | estado aprobado                    |                                      |        |
|-----|------------------------|----------------------|------------------------------------|--------------------------------------|--------|
| P61 | Reporte de ocupación   | acceder como admin   | Barra de progreso por curso,       |                                      |        |
|     | de cursos              |                      | colores según % de ocupación       |                                      |        |
|-----|------------------------|----------------------|------------------------------------|--------------------------------------|--------|
| P62 | Estudiante intenta     | navegar directamente | Redirige a dashboard.html          |                                      |        |
|     | acceder a              | a la URL             |                                    |                                      |        |
|     | reportes.html          |                      |                                    |                                      |        |

---

## Pruebas no funcionales (automatizadas, ver documentos separados)

|     Tipo de prueba    |  Herramienta  |      Documento de referencia      |
|-----------------------|---------------|-----------------------------------|
|       Unitarias       |    PHPUnit    |     Incluidas en `tests/Unit/`    |
|      Integración      |    PHPUnit    | Incluidas en `tests/Integration/` |
|   Funcionales (E2E)   |    Cypress    | Incluidas en `tests/cypress/e2e/` |
|    Estrés / carga     | Apache JMeter |        `PRUEBAS_ESTRES.md`        |
|       Seguridad       |   OWASP ZAP   |      `PRUEBAS_SEGURIDAD.md`       |

Todas las pruebas automatizadas (excepto JMeter y ZAP, que corren en workflows separados) se ejecutan en cada `push` a la rama `main` mediante GitHub Actions, definido en `.github/workflows/tests.yml`.

---

## Resultados finales

| Total casos manuales | Aprobados | Fallidos | Pendientes |
|----------------------|-----------|----------|------------|
|          62          |    62     |    0     |     0      |

---

## Notas
- Columna **Estado**: completar con ✅ Aprobado, ❌ Fallido o ⏳ Pendiente
- Columna **Resultado obtenido**: describir brevemente lo observado al ejecutar la prueba
- Los casos marcados con códigos de error HTTP (401, 403, 404, 409, etc.) reflejan las respuestas reales de la API, verificables también con Thunder Client o Postman
- Para reproducir cualquier caso de error de autorización (403), recuerda que los tokens JWT expiran 1 hora después de emitidos

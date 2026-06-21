# Pruebas de Seguridad — Instituto Eduka

## Herramienta utilizada
OWASP ZAP (Zed Attack Proxy) — Baseline Scan

## Tipo de escaneo
**Baseline Scan (escaneo pasivo):** ZAP navega la aplicación como lo haría un usuario normal, sin intentar explotar activamente vulnerabilidades. Es el tipo de escaneo recomendado para integrarse en pipelines de CI/CD, ya que es rápido, no daña datos, y no requiere intervención manual.

## Integración con CI/CD
El escaneo está automatizado mediante la GitHub Action oficial `zaproxy/action-baseline`, configurada en `.github/workflows/security-scan.yml`. Se ejecuta automáticamente en cada `push` a la rama `main`, y también puede dispararse manualmente desde la pestaña Actions de GitHub (`workflow_dispatch`).

## Objetivo escaneado
`http://localhost:8000/frontend/pages/login.html` (página de inicio de sesión, punto de entrada público de la aplicación)

## Cómo ejecutar el escaneo localmente (alternativa con Docker)

```bash
docker run -v "<ruta_del_proyecto>:/zap/wrk/:rw" -t ghcr.io/zaproxy/zaproxy:stable zap-baseline.py -t http://host.docker.internal/eduka/frontend/pages/login.html -r reporte-zap.html
```

## Resultados obtenidos

|                    Categoría                    | Cantidad |
|-------------------------------------------------|----------|
|       **PASS** (verificaciones superadas)       |    63    |
| **FAIL-NEW** (vulnerabilidades críticas nuevas) |     0    |
|           **WARN-NEW** (advertencias)           |     7    |
|                     **INFO**                    |     0    |

**Ninguna vulnerabilidad crítica fue detectada.** Las 63 verificaciones de seguridad pasaron correctamente, incluyendo pruebas contra XSS, inyección, exposición de información sensible, cookies inseguras, y vulnerabilidades conocidas de librerías JavaScript.

## Detalle de advertencias (WARN-NEW)

| # | Advertencia | Riesgo | Descripción |
|---|------------------------------------------------------------|-------|-------------|
| 1 |            **Missing Anti-clickjacking Header**            |  Bajo | Falta la cabecera `X-Frame-Options`, que impide que el sitio sea embebido dentro de un `<iframe>` malicioso (ataque de clickjacking). |
| 2 |          **X-Content-Type-Options Header Missing**         | Bajo | Falta `X-Content-Type-Options: nosniff`, que evita que el navegador interprete archivos con un tipo MIME distinto al declarado. |
| 3 |       **Content Security Policy (CSP) Header Not Set**     | Medio | No se define una política de seguridad de contenido, que limita desde qué orígenes se pueden cargar scripts, estilos e imágenes (mitiga XSS). |
| 4 |             **Storable and Cacheable Content**             | Bajo | Algunas respuestas pueden ser almacenadas en caché por el navegador o proxies intermedios. |
| 5 |         **Permissions Policy Header Not Set** | Bajo | No se restringe qué APIs del navegador (cámara, micrófono, geolocalización, etc.) puede usar la página. |
| 6 | **Cross-Origin-Embedder-Policy Header Missing or Invalid** | Bajo | Cabecera moderna de aislamiento de origen cruzado, recomendada pero no crítica para este tipo de aplicación. |
| 7 | **Sec-Fetch-Dest Header is Missing** | Informativo | Cabecera relacionada con Fetch Metadata, usada por navegadores modernos para contexto adicional de seguridad. |

## Análisis

Todas las advertencias encontradas corresponden a **cabeceras HTTP de seguridad opcionales/complementarias**, no a vulnerabilidades explotables. Ninguna permite inyección de código, robo de sesión, ni acceso no autorizado a datos. Son mejoras de "defensa en profundidad" recomendadas para entornos de producción.

## Recomendaciones de mejora (para una futura iteración del proyecto)

Estas cabeceras se pueden agregar fácilmente a nivel de servidor (Apache, mediante `.htaccess`) o directamente desde PHP con la función `header()`:

```php
header("X-Frame-Options: SAMEORIGIN");
header("X-Content-Type-Options: nosniff");
header("Content-Security-Policy: default-src 'self'");
header("Permissions-Policy: geolocation=(), camera=(), microphone=()");
```

## Conclusión

El sistema no presenta vulnerabilidades críticas detectables mediante escaneo pasivo. Las advertencias encontradas son de severidad baja a media y corresponden a buenas prácticas de cabeceras HTTP que refuerzan la seguridad pero cuya ausencia no compromete la integridad ni confidencialidad de los datos del sistema en su estado actual.

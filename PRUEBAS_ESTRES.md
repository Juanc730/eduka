# Pruebas de Estrés — Instituto Eduka

## Herramienta utilizada
Apache JMeter 5.6.3

## Configuración de la prueba
- **Usuarios virtuales (Threads):** 20
- **Ramp-up period:** 5 segundos
- **Loop Count:** 10 iteraciones por usuario
- **Total de peticiones por endpoint:** 200
- **Total de peticiones en la prueba combinada:** 600

## Endpoints evaluados
1. `POST /backend/api/auth/login.php` — autenticación
2. `GET /backend/api/cursos/listar.php` — listado de cursos
3. `GET /backend/api/usuarios/listar.php` — listado de usuarios (admin)

## Cómo ejecutar la prueba

```bash
cd apache-jmeter-5.6.3/bin
jmeter -n -t plan-carga-login.jmx -l resultados.jtl -e -o reporte-html
```

El reporte HTML se genera en la carpeta `reporte-html/index.html`.

## Resultados obtenidos

| Endpoint | # Peticiones | Promedio (ms) | Mínimo (ms) | Máximo (ms) | Throughput (req/s) | Error % |
|---|---|---|---|---|---|---|
| GET Listar Cursos | 200 | 12.41 | 4 | 46 | 41.64 | 0.00% |
| GET Listar Usuarios | 200 | 11.69 | 4 | 43 | 41.32 | 0.00% |
| POST Login | 200 | 184.93 | 144 | 228 | 29.88 | 0.00% |
| **Total combinado** | **600** | **69.68** | **4** | **228** | **89.65** | **0.00%** |

## Conclusiones

1. **Estabilidad:** el sistema procesó 600 peticiones concurrentes sin generar ningún error (0.00% en los tres endpoints), lo que indica que la API soporta correctamente cargas moderadas de tráfico simultáneo.

2. **Rendimiento de lectura:** los endpoints de listado (cursos y usuarios) muestran tiempos de respuesta muy bajos (11-12ms en promedio), apropiados para operaciones de lectura con consultas SQL que incluyen JOIN.

3. **Rendimiento del login:** el endpoint de autenticación es significativamente más lento (~185ms en promedio) que los endpoints de lectura. Esto es **esperado y deseable**: la función `password_verify()` de PHP utiliza el algoritmo bcrypt, que está diseñado deliberadamente para ser computacionalmente costoso como medida de seguridad contra ataques de fuerza bruta. Un login más rápido sería indicio de un hash de contraseña débil, no de buen rendimiento.

4. **Capacidad general:** con 20 usuarios concurrentes, el sistema mantiene tiempos de respuesta aceptables (todos por debajo de 230ms en el peor caso) y un throughput combinado de ~90 peticiones por segundo, sin signos de degradación ni caída del servicio.

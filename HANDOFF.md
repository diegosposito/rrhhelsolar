# HANDOFF — RRHH El Solar Uruguay

Sistema de RRHH / fichaje (control de ingresos-egresos) para **El Solar Uruguay**.
Reescritura del legacy PHP5/Symfony 1.4 a **Laravel 12 + Filament 3 + Pest**, dockerizado.

## En producción desde el 2026-08-12

Vive en `https://rrhh.elsolaruruguay.com.ar`, en el servidor `138.99.6.109`,
detrás de nginx-proxy con certificados Let's Encrypt. Se levanta desde
`/data/elsolaruruguay` junto a los otros dos sistemas:

```bash
docker compose -f docker-compose.produccion.yml up -d   # 8 servicios
```

El DNS está en **Cloudflare, en modo «DNS only» (nube gris)**, a propósito: con
la nube naranja Cloudflare termina el TLS con su propio certificado, y si su
modo SSL queda en «Flexible» habla HTTP contra el origen, nginx-proxy lo
redirige a HTTPS y se arma un bucle infinito. Si algún día se activa el proxy
naranja, hay que poner SSL/TLS en **Full (strict)**.

Los certificados se renuevan solos: acme-companion revisa cada hora y renueva
un mes antes del vencimiento. Verificar con:

```bash
echo | openssl s_client -connect rrhh.elsolaruruguay.com.ar:443 \
  -servername rrhh.elsolaruruguay.com.ar 2>/dev/null | openssl x509 -noout -enddate
docker logs nginx-proxy-acme --tail 20
```

Necesita el **puerto 80 abierto** desde internet: Let's Encrypt valida por
HTTP-01. Cerrarlo «porque total todo va por HTTPS» rompe la renovación.

## Estado actual (2026-07-27)

**Construido y verde (68 tests Pest passing):**

| Rebanada | Qué | Estado |
|---|---|---|
| 0 | Fundación: Laravel 12 + Filament 3 + Pest, Docker (PHP 8.3-fpm + nginx + MySQL 8), locale `es` | ✅ |
| 1 | Dominio: 6 entidades (migraciones/modelos/factories) + reglas de fichaje con TDD | ✅ |
| 2 | Panel admin Filament: 5 CRUD + branding (sol SVG, magenta) + acceso por rol | ✅ |
| 3 | Kiosco de fichaje `/registro`: teclado DNI, ENTRADA/SALIDA, últimos 50, observaciones | ✅ |
| 4 | Reporte "Gestión Horas Trabajadas" + PDF (dompdf) | ✅ |
| 5 | Migración de datos desde la base vieja (`app:migrar-legacy`) | ✅ |
| 6 | Configuración de quincenas por período + rangos en el reporte de detalle | ✅ |

**Alcance funcional cerrado.** El próximo paso es el despliegue a producción.

### Configuración de quincenas (rebanada 6)

`/admin` → Horarios → Períodos de Quincena. Cada período (mes/año) define las
dos quincenas por fechas inicio/fin, con **cobertura total contigua**: la 1ra
arranca el día 1, la 2da termina el último día del mes y quedan pegadas — solo
se elige el día de corte. Si un período no tiene config, el reporte cae al
default histórico 1-15 / 16-fin (así los meses migrados del legacy no cambian).

**Solo se puede configurar/modificar el período actual** (mes y año en curso).
Ni pasado ni futuro.

Los rangos resueltos se muestran en el detalle de horas (pantalla y PDF), en el
encabezado y debajo de cada total de quincena.

## Cómo levantar en una máquina nueva

Requiere **Docker + Docker Compose**. No hace falta PHP/Composer/Node local.

```bash
git clone <URL-DEL-REPO> rrhhelsolaruruguay
cd rrhhelsolaruruguay

# 1) Crear el .env (está gitignored). Copiar el ejemplo y ajustar:
cp .env.example .env
```

Asegurar estos valores en `.env` (los importantes para que corra en Docker):

```dotenv
APP_NAME="El Solar Uruguay"
APP_URL=http://localhost:8090
APP_LOCALE=es
APP_FALLBACK_LOCALE=es

DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=rrhh
DB_USERNAME=rrhh
DB_PASSWORD=rrhh

SESSION_DRIVER=database
SESSION_LIFETIME=43200
```

```bash
# 2) Levantar el stack (nginx en 8090 porque 8080 lo ocupa el legacy elsolar-web)
WEB_PORT=8090 docker compose up -d --build

# 3) Instalar dependencias (vendor/ NO viaja por git)
docker compose exec app composer install

# 4) Generar APP_KEY y crear el esquema + datos demo
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate:fresh --seed
```

App: **http://localhost:8090**
- Panel admin: `/admin` — `admin@elsol.uy` / `password`
- Kiosco: `/login` → `kiosk@elsol.uy` / `password` (rol fichaje, va directo a `/registro`)
- Reporte: `/admin` → Horarios → Gestión Horas Trabajadas

Correr los tests: `docker compose exec app ./vendor/bin/pest`

## Lo que NO viaja por git (ojo)

1. **`referencia/`** (el legacy + `schema.sql`, ~50MB, con su propio `.git`): está gitignored y **no se sube a GitHub**. Para volver a correr la **migración de datos** vas a necesitar el dump `referencia/schema.sql` en la otra máquina → copialo aparte (USB, scp, nube).
2. **Datos de la BD**: viven en un volumen Docker, no viajan. Se regeneran con `migrate:fresh --seed` (datos demo). No hace falta transferirlos.
3. **`.env`**: gitignored (tiene el APP_KEY). Recrealo con los pasos de arriba.
4. **Memoria del asistente (engram)**: es local a esta máquina. El contexto completo de decisiones está en engram acá; el resumen esencial está en este documento y en el código/tests.

## Decisiones clave del negocio (resumen)

- **Alcance cerrado (6 entidades):** pacientes, obras sociales, personal, usuarios, roles, fichajes. El resto del legacy (sistema académico) se descarta.
- **Fichaje:** cada fila es entrada/salida por DNI. Reglas (blindadas con tests): no dos entradas sin cerrar; salida requiere entrada abierta; pares acotados al día (no cruzan medianoche); entrada huérfana no suma horas. Horas al segundo (HH:MM:SS).
- **Auth:** esquema propio simple `usuarios` + `roles` (2 roles: `admin`, `fichaje`). No se migra el sfGuardPlugin. Solo 3 usuarios activos reales; passwords nuevas (no se migra el hash sha1).
- **Kiosco:** rol `fichaje` solo ve `/registro`, backend-enforced; sesión larga; invalidación remota (admin pone usuario en inactivo → cae la sesión). Sin rate limiting.
- **Migración de datos (ejecutada):** solo de las 6 entidades. Personal: DNI único (el dueño depura duplicados en origen; ante duplicado sobrevive el de más fichajes y se reapuntan sus punches). Ciudad = texto libre (sin tabla). Pacientes: ~39 reales, un solo teléfono = `celular`. Fichajes: solo `anulado=false`.
- **`fichajes.contabiliza`** ← `horarios.controlar` del legacy. Es la columna que decide si un fichaje suma horas. El legacy solo contabiliza los `controlar=1`; los demás aparecen en el detalle como par abierto (rojo) pero **no suman**. Ignorarla infla los totales.

## Migrar los datos del legacy

Con `referencia/schema.sql` presente:

```bash
bash setup_migrar.sh                      # levanta el stack + importa + migra
# o, si el stack ya está arriba:
docker compose exec app php artisan app:migrar-legacy
```

### Re-migrar con un backup más nuevo (sistema ya en uso)

`app:migrar-legacy` es re-ejecutable por diseño: trunca `fichajes`,
`pacientes`, `personal` y `obras_sociales`, y **no toca `users`, `roles` ni
`periodos_quincena`**. Se puede correr las veces que haga falta sin perder
usuarios, contraseñas ni la configuración de quincenas.

**Nunca usar `setup_migrar.sh` para re-migrar**: su paso 4 ejecuta
`migrate:fresh --seed`, que borra todas las tablas, resetea las contraseñas al
valor del seeder y elimina la config de quincenas.

Y el truncate corta en los dos sentidos: **la última migración tiene que ser
antes de que la gente empiece a usar el sistema nuevo**. Migrar después les
borra los fichajes que hayan cargado.

```bash
docker compose exec -T db mysqldump -uroot -prootsecret rrhh > rrhh_antes.sql
docker compose exec -T db mysql -uroot -prootsecret -e "
  DROP DATABASE IF EXISTS rrhh_legacy;
  CREATE DATABASE rrhh_legacy CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
  GRANT ALL PRIVILEGES ON rrhh_legacy.* TO 'rrhh'@'%'; FLUSH PRIVILEGES;"
docker compose exec -T db mysql -uroot -prootsecret rrhh_legacy < subset.sql
docker compose exec app php artisan app:migrar-legacy
```

## Trampas conocidas (para no repetirlas)

- **URLs en componentes Filament:** usar `:href="$url"`, **nunca** `href="{{ $url }}"`. El componente escapa el href por dentro; interpolar lo escapa dos veces (`&amp;amp;`) y el browser manda `?a=1&amp;b=2` → PHP parsea la clave como `amp;b` y se pierde todo query param después del primero. Falla en silencio: sin error, sin log, solo datos del período equivocado.
- **`tests/Unit` debe existir:** `phpunit.xml` declara ese testsuite y en PHPUnit 11+ un directorio inexistente aborta la corrida entera.
- **Dump legacy con fechas `0000-00-00`:** MySQL 8 en modo estricto las rechaza. El header del dump lleva `SET SESSION sql_mode='';`.
- **El dump completo del legacy NO importa en MySQL 8.** Corta con `ERROR 6125: Failed to add the foreign key constraint. Missing unique key for constraint 'hipi' in the referenced table 'profesionalesasociado'` — una tabla del sistema académico cuya FK MySQL 5.7 toleraba sin índice único. `FOREIGN_KEY_CHECKS=0` **no** evita el error: el índice referenciado tiene que existir igual. Por eso se importa un subset con las 4 tablas que lee el migrador (`personas`, `horarios`, `paciente`, `obras_sociales`), que además no tienen FKs. De 233 tablas y 49 MB queda en 4 y 3,5 MB.
- **Acentos del legacy en latin1:** la base vieja guarda `ó` como el byte `F3`, no como UTF-8. El migrador los convierte bien (`C3B3`). Si al consultar ves `Sp?sito`, es el cliente `mysql` mostrando mal: agregá `--default-character-set=utf8mb4`. No es corrupción de datos.
- **`LETSENCRYPT_EMAIL` va en los CUATRO `.env`** (`webelsolar/`, `rrhhelsolar/`, `vcomunicaciones/`, `dockers/nginxproxy/`). El compose raíz usa `include`, que carga el `.env` de cada subproyecto por separado; no hay uno central. Si falta en uno solo, el `up` entero falla al interpolar y el error nombra únicamente el primer servicio que evaluó (`services.site`), lo que despista.

## Stack / puertos

- App PHP-FPM 8.3 (servicio `app`), nginx (`web`, host `WEB_PORT`→80, default 8082), MySQL 8 (`db`, host `DB_HOST_PORT`→3306, default 3306).
- MySQL desde el host: `127.0.0.1:3306`, `rrhh`/`rrhh`/`rrhh`.

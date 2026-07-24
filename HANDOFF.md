# HANDOFF — RRHH El Solar Uruguay

Sistema de RRHH / fichaje (control de ingresos-egresos) para **El Solar Uruguay**.
Reescritura del legacy PHP5/Symfony 1.4 a **Laravel 12 + Filament 3 + Pest**, dockerizado.

## Estado actual (2026-07-24)

**Construido y verde (55 tests Pest passing):**

| Rebanada | Qué | Estado |
|---|---|---|
| 0 | Fundación: Laravel 12 + Filament 3 + Pest, Docker (PHP 8.3-fpm + nginx + MySQL 8), locale `es` | ✅ |
| 1 | Dominio: 6 entidades (migraciones/modelos/factories) + reglas de fichaje con TDD | ✅ |
| 2 | Panel admin Filament: 5 CRUD + branding (sol SVG, magenta) + acceso por rol | ✅ |
| 3 | Kiosco de fichaje `/registro`: teclado DNI, ENTRADA/SALIDA, últimos 50, observaciones | ✅ |
| 4 | Reporte "Gestión Horas Trabajadas" + PDF (dompdf) | ✅ |
| 5 | **Migración de datos desde la base vieja** | ⛔ PENDIENTE |

**Nada está commiteado con lógica de negocio pendiente**; el próximo paso es la rebanada 5.

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

1. **`referencia/`** (el legacy + `schema.sql`, ~50MB, con su propio `.git`): está gitignored y **no se sube a GitHub**. Para la **rebanada 5 (migración de datos)** vas a necesitar el dump `referencia/schema.sql` en la otra máquina → copialo aparte (USB, scp, nube).
2. **Datos de la BD**: viven en un volumen Docker, no viajan. Se regeneran con `migrate:fresh --seed` (datos demo). No hace falta transferirlos.
3. **`.env`**: gitignored (tiene el APP_KEY). Recrealo con los pasos de arriba.
4. **Memoria del asistente (engram)**: es local a esta máquina. El contexto completo de decisiones está en engram acá; el resumen esencial está en este documento y en el código/tests.

## Decisiones clave del negocio (resumen)

- **Alcance cerrado (6 entidades):** pacientes, obras sociales, personal, usuarios, roles, fichajes. El resto del legacy (sistema académico) se descarta.
- **Fichaje:** cada fila es entrada/salida por DNI. Reglas (blindadas con tests): no dos entradas sin cerrar; salida requiere entrada abierta; pares acotados al día (no cruzan medianoche); entrada huérfana no suma horas. Horas al segundo (HH:MM:SS).
- **Auth:** esquema propio simple `usuarios` + `roles` (2 roles: `admin`, `fichaje`). No se migra el sfGuardPlugin. Solo 3 usuarios activos reales; passwords nuevas (no se migra el hash sha1).
- **Kiosco:** rol `fichaje` solo ve `/registro`, backend-enforced; sesión larga; invalidación remota (admin pone usuario en inactivo → cae la sesión). Sin rate limiting.
- **Migración de datos (rebanada 5, pendiente):** solo de las 6 entidades. Personal: DNI único (el dueño depura duplicados en origen; ante duplicado sobrevive el de más fichajes y se reapuntan sus punches). Ciudad = texto libre (sin tabla). Pacientes: ~39 reales, un solo teléfono = `celular`. Fichajes: solo `anulado=false`.

## Stack / puertos

- App PHP-FPM 8.3 (servicio `app`), nginx (`web`, host `WEB_PORT`→80, default 8090), MySQL 8 (`db`, host 3307→3306).
- MySQL desde el host: `127.0.0.1:3307`, `rrhh`/`rrhh`/`rrhh`.

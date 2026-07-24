#!/usr/bin/env bash
# Levanta el stack y ejecuta la migración de datos del legacy (rebanada 5).
# Uso:  sudo bash setup_migrar.sh   (o simplemente: bash setup_migrar.sh si tu
#       usuario está en el grupo docker)
set -euo pipefail

cd "$(dirname "$0")"

# UID/GID del dueño real de los archivos (diego), para que el contenedor no
# escriba como root en el árbol del proyecto.
export UID_HOST=1000
export GID_HOST=1000
export WEB_PORT=8090
DC="docker compose"

echo "==> 1/7  Build + up del stack (nginx :$WEB_PORT, MySQL :3307)"
# UID es una variable readonly en bash; se pasa vía env para el build args.
env UID="$UID_HOST" GID="$GID_HOST" WEB_PORT="$WEB_PORT" $DC up -d --build

echo "==> 2/7  Esperando a que MySQL esté listo…"
for i in $(seq 1 60); do
  if $DC exec -T db mysqladmin ping -uroot -prootsecret --silent >/dev/null 2>&1; then
    echo "    MySQL OK"; break
  fi
  sleep 2
  [ "$i" = "60" ] && { echo "!! MySQL no respondió"; exit 1; }
done

echo "==> 3/7  composer install"
$DC exec -T app composer install --no-interaction --prefer-dist

echo "==> 4/7  APP_KEY + esquema + seeders (roles y usuarios admin/kiosk)"
$DC exec -T app php artisan key:generate --force
$DC exec -T app php artisan migrate:fresh --seed --force

echo "==> 5/7  Creando base legacy y otorgando permisos a 'rrhh'"
$DC exec -T db mysql -uroot -prootsecret -e \
  "CREATE DATABASE IF NOT EXISTS rrhh_legacy CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   GRANT ALL PRIVILEGES ON rrhh_legacy.* TO 'rrhh'@'%';
   FLUSH PRIVILEGES;"

echo "==> 6/7  Importando subset del legacy (referencia/legacy_subset.sql)"
$DC exec -T db mysql -uroot -prootsecret rrhh_legacy < referencia/legacy_subset.sql

echo "==> 7/7  Migrando datos al esquema nuevo"
$DC exec -T app php artisan app:migrar-legacy

echo
echo "==================================================================="
echo " Listo. App:  http://localhost:$WEB_PORT"
echo "   Admin:   /admin    admin@elsol.uy / password"
echo "   Kiosco:  /login    kiosk@elsol.uy / password  -> /registro"
echo "==================================================================="

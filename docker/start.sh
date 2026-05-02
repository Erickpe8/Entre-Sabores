#!/bin/sh
set -e

cd /var/www/html

echo "[start] Inicializando aplicacion..."

# Crear SQLite si aplica y no existe
if [ "${DB_CONNECTION}" = "sqlite" ]; then
  mkdir -p database
  if [ ! -f database/database.sqlite ]; then
    echo "[start] Creando database/database.sqlite..."
    touch database/database.sqlite
  fi
fi

# Permisos para runtime de Laravel
echo "[start] Configurando permisos..."
chmod -R 775 database storage bootstrap/cache 2>/dev/null || true
chown -R www-data:www-data database storage bootstrap/cache 2>/dev/null || true

LIGHT_START=""
case "${DOCKER_LIGHT_START:-}" in
  1|true|yes) LIGHT_START=1 ;;
  0|false|no) LIGHT_START=0 ;;
esac
if [ -z "${LIGHT_START}" ]; then
  if [ "${APP_ENV:-}" = "local" ]; then
    LIGHT_START=1
  else
    LIGHT_START=0
  fi
fi

if [ -f artisan ]; then
  if [ "${LIGHT_START}" = "1" ]; then
    echo "[start] Modo desarrollo (DOCKER_LIGHT_START / APP_ENV=local): sin config:route:view cache en arranque."
    php artisan migrate --force

    if [ "${DOCKER_RUN_SEED:-}" = "1" ]; then
      echo "[start] DOCKER_RUN_SEED=1: ejecutando seeders..."
      php artisan db:seed --force || true
    fi
  else
    echo "[start] Modo arranque completo: limpiando cache y regenerando..."
    php artisan optimize:clear || true

    echo "[start] Generando caches..."
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache

    echo "[start] Ejecutando migraciones..."
    php artisan migrate --force

    if [ "${APP_ENV}" = "local" ]; then
      echo "[start] APP_ENV=local, ejecutando seeders..."
      php artisan db:seed --force || true
    fi
  fi

  php artisan storage:link --force >/dev/null 2>&1 || true
fi

echo "[start] App lista. Iniciando supervisor..."
exec /usr/bin/supervisord -n -c /etc/supervisor/supervisord.conf

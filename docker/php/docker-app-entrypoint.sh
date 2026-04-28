#!/bin/sh
set -e
cd /var/www/html

if [ ! -d vendor ] || [ ! -f vendor/autoload.php ]; then
  echo "[docker] Instalando dependencias con Composer…"
  composer install --no-interaction --prefer-dist --no-progress
  chown -R www-data:www-data vendor
fi

chown -R www-data:www-data storage bootstrap/cache database 2>/dev/null || true
chmod -R 775 storage bootstrap/cache database 2>/dev/null || true

if [ "${DB_CONNECTION}" = "sqlite" ]; then
  mkdir -p database
  if [ ! -f database/database.sqlite ]; then
    echo "[docker] Creando database/database.sqlite..."
    touch database/database.sqlite
  fi
  chown www-data:www-data database/database.sqlite 2>/dev/null || true
  chmod 775 database/database.sqlite 2>/dev/null || true
fi

exec docker-php-entrypoint "$@"

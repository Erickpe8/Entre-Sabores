#!/bin/sh
set -e
cd /var/www/html

if [ ! -d vendor ] || [ ! -f vendor/autoload.php ]; then
  echo "[docker] Instalando dependencias con Composer…"
  composer install --no-interaction --prefer-dist --no-progress
  chown -R www-data:www-data vendor
fi

chown -R www-data:www-data storage bootstrap/cache database 2>/dev/null || true
chmod -R ug+rwX storage bootstrap/cache database 2>/dev/null || true

if [ -f database/database.sqlite ]; then
  chown www-data:www-data database/database.sqlite 2>/dev/null || true
  chmod ug+rw database/database.sqlite 2>/dev/null || true
fi


if [ -f artisan ]; then
  php artisan storage:link --force >/dev/null 2>&1 || true
fi

if [ -f artisan ]; then
  php artisan storage:link --force >/dev/null 2>&1 || true
fi

exec docker-php-entrypoint "$@"

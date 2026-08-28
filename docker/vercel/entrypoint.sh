#!/bin/sh
set -e

cd /app

echo "[vercel] Preparando Laravel..."

mkdir -p \
	storage/app/public \
	storage/framework/sessions \
	storage/framework/views \
	storage/framework/cache/data \
	storage/logs \
	bootstrap/cache

chmod -R ug+rwX storage bootstrap/cache 2>/dev/null || true

php artisan storage:link --force >/dev/null 2>&1 || true

if [ -n "${APP_KEY:-}" ]; then
	echo "[vercel] Generando caches de configuración..."
	php artisan config:cache
	php artisan route:cache
	php artisan view:cache
else
	echo "[vercel] APP_KEY no definida; omitiendo config:cache en arranque."
fi

if [ "${VERCEL_RUN_MIGRATIONS:-}" = "1" ]; then
	echo "[vercel] Ejecutando migraciones..."
	php artisan migrate --force
fi

echo "[vercel] Iniciando FrankenPHP..."
exec "$@"

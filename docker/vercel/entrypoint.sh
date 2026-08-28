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

# No ejecutar config:route:view cache ni migrate aquí: en cold start bloquean
# el HTTP hasta 300s (504) y config:cache impide leer env() en runtime.
# Las variables de Vercel se leen en cada request sin .env en disco.

echo "[vercel] Iniciando FrankenPHP..."
exec "$@"

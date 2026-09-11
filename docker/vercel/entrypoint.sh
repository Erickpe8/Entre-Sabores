#!/bin/sh
set -e

cd /app

# Vercel enruta la petición en cuanto el proceso arranca. Cualquier `php artisan`
# aquí retrasa el bind de $PORT y la primera visita tras scale-to-zero acaba en 504.
mkdir -p \
	storage/app/public \
	storage/framework/sessions \
	storage/framework/views \
	storage/framework/cache/data \
	storage/logs \
	bootstrap/cache

chmod -R ug+rwX storage bootstrap/cache 2>/dev/null || true

echo "[vercel] Iniciando FrankenPHP en :${PORT:-80}..."
exec "$@"

# Project Intelligence Index

Mapa de navegación para agentes. **Decide qué leer, no qué es verdad.** El código es la fuente de verdad.

## Generar

```bash
php artisan project:index --full
php artisan project:index --install-hooks
composer index   # alias de --if-stale
```

Artefactos versionables: `metadata.json`, `overview.json`, `index.json`, `graph.json`, `filemap.json`.
No versionar: `fingerprints.json` (caché local).

## Consultar

```bash
php artisan project:query overview
php artisan project:query relevant --q="maridaje"
php artisan project:query context social.wall
php artisan project:query path --q=app/Services/WallFeedService.php
php artisan project:query stale
```

Stdout = JSON. Por defecto la query refresca si el índice está stale. `--no-refresh` no crea índice.

## Stale

`php artisan project:index --check` (exit 1 si stale).

Razones: `missing-index`, `catalog-changed`, `source-files-changed`.
No es stale: working tree dirty ni commit distinto (van en `warnings[]`).

Actualización incremental: `php artisan project:index --if-stale` (silencioso si ya está al día).
Tras editar `Catalog.php`: siempre `--full`.

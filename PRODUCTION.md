# Producción — guía y checklist

Referencia operativa para despliegue con usuarios reales. **Última revisión:** 2026-05-03.

## Visión rápida

- Aplicación **stateful** (sesión + cookies) detrás de Blade; JSON del feed en el mismo origen.
- Cuellos de botella probables: **BD** (feed, notificaciones) y **storage** de imágenes; **Redis** para caché, sesión, colas y métricas opcionales.

Comportamiento funcional del feed (orden, mixto 70/30): [ARCHITECTURE.md](ARCHITECTURE.md).

## Variables de entorno

1. Partir de **`.env.example`**; no commitear secretos.
2. Alinear con **`.env.production.example`** (sesión, Redis, `APP_DEBUG=false`, mail, logging, monitoring).
3. `APP_KEY` **única** por entorno.

---

## Checklist de despliegue

### Seguridad

- [ ] `APP_ENV=production`, `APP_DEBUG=false`.
- [ ] `SESSION_ENCRYPT=true`, `SESSION_SECURE_COOKIE=true`, `SESSION_SAME_SITE` acorde al dominio.
- [ ] Sesión **Redis** o **database** compartida si hay varias réplicas PHP.
- [ ] **`TRUSTED_PROXIES`** si hay balanceador/CDN (`bootstrap/app.php`).
- [ ] Rate limiting activo — [SECURITY.md](SECURITY.md).
- [ ] CSRF en rutas web (Axios configurado).
- [ ] Notificaciones: no exponer `data` crudo — `NotificationApiPayload`.
- [ ] Backups de BD y **storage** si las imágenes son locales.

### Performance

- [ ] `npm run build` en CI o imagen.
- [ ] `CACHE_STORE=redis` si hay picos (tags, métricas).
- [ ] `QUEUE_CONNECTION=redis` + workers para correo/jobs.
- [ ] Índices — [DATABASE.md](DATABASE.md), [PERFORMANCE.md](PERFORMANCE.md).

### Infraestructura

- [ ] Health check `GET /up` en balanceador.
- [ ] Cron: `* * * * * php artisan schedule:run` (p. ej. `notifications:prune` en `routes/console.php`).
- [ ] `php artisan storage:link` si aplica.
- [ ] Opcional: S3 para medios; Redis con SLA acorde.

### Código

- [ ] `composer install --no-dev --optimize-autoloader`.
- [ ] `php artisan config:cache`, `route:cache`, `view:cache` cuando proceda.
- [ ] CI: `php artisan test` en release.

### Observabilidad

- [ ] Logs: `LOG_STACK` con canal `structured` si se agrega JSON — `config/logging.php`.
- [ ] `MONITORING_METRICS_ENABLED`, Redis; proteger `GET /internal/metrics` con `METRICS_TOKEN`.
- [ ] `GET /health` con `HEALTH_CHECK_TOKEN` si no debe ser público.
- [ ] Alertas sobre 5xx, latencia, caídas de `/up` o `/health`.

---

## Sesión y cookies

| Variable | Recomendación |
|----------|----------------|
| `SESSION_DRIVER` | `redis` multi-instancia |
| `SESSION_ENCRYPT` | `true` |
| `SESSION_SECURE_COOKIE` | `true` |
| `SESSION_DOMAIN` | Alcance correcto |

## HTTPS

`AppServiceProvider` fuerza `https` en `production` / `qa` o si `FORCE_HTTPS=true`.

## Rate limiting

Centralizado en `AppServiceProvider`; rutas en `routes/web.php` y `routes/auth.php`. Tabla: [SECURITY.md](SECURITY.md#rate-limiting-por-nombre-referencia).

## Colas (`QUEUE_CONNECTION=redis`)

1. `.env`: `QUEUE_CONNECTION=redis`, `REDIS_*` coherentes.
2. Supervisor (ejemplo):

```ini
[program:entre-sabores-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/entre-sabores/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
user=www-data
numprocs=2
stdout_logfile=/var/www/entre-sabores/storage/logs/worker.log
```

3. Tras desplegar: `php artisan queue:restart`.

## Cabeceras de seguridad

Middleware `SecurityHeaders`. Variables: `SECURITY_HEADERS_ENABLED`, `SECURITY_CSP_ENABLED`, `SECURITY_CSP_POLICY` — ver `config/security.php`.

## Assets

`npm run build`; hashes en `public/build`.

## Salud y observabilidad operativa

| Endpoint | Uso |
|----------|-----|
| `GET /up` | Probe liviano (Kubernetes / balanceador). |
| `GET /health` | JSON: `database`, `cache`, `queue_connection`. 200 / 503. Token opcional `HEALTH_CHECK_TOKEN`. |
| `GET /internal/metrics` | Posts/min, requests/min, 5xx/min (Redis); token `METRICS_TOKEN`. |

### Logs estructurados

Canal `structured` — JSON por línea; eventos `post.created`, `auth.*`; excepciones opcionales `LOG_STRUCTURED_EXCEPTIONS`.

### Métricas Redis (conceptual)

Prefijo `metrics:*` por minuto; requiere `CACHE_STORE=redis` y `MONITORING_METRICS_ENABLED=true`.

### Alertas (referencia)

Caídas de salud, picos de 5xx, latencia del feed, backlog de colas. Herramientas: Datadog, Grafana, Sentry, probes HTTP externos.

### Jobs y scheduler

- `notifications:prune` — diario (hora en `routes/console.php`).
- Cron del sistema cada minuto para `schedule:run`.

### Backups

BD diaria + política de retención; storage si es local. Probar restauración.

### Checklist operación (incidente)

- [ ] `/up` y `/health` OK.
- [ ] Workers activos; colas sin backlog anómalo.
- [ ] Scheduler ejecutándose.
- [ ] Logs sin picos de errores.
- [ ] Backups verificados.

## Documentación relacionada

- [SECURITY.md](SECURITY.md) — riesgos.
- [PERFORMANCE.md](PERFORMANCE.md) — rendimiento del feed.
- [DOCKER.md](DOCKER.md) — stack local.

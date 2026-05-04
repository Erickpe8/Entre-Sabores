# Producción — guía y checklist

Referencia operativa para despliegue con usuarios reales. **Última revisión:** 2026-05-04.

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
- [ ] `QUEUE_CONNECTION=redis` (o `database` si aplica) + **workers** para correo, jobs de IA y broadcasts (`ShouldBroadcast`).
- [ ] Si usas **Reverb** o Pusher: `BROADCAST_CONNECTION` coherente; build de front con `VITE_REVERB_*` o `VITE_PUSHER_*`; proceso `reverb:start` o servicio WS en producción.
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

## Colas

1. `.env`: `QUEUE_CONNECTION=redis` **o** `database` (tablas `jobs`, `failed_jobs` migradas). Redis suele ser preferible en producción multi-instancia.
2. Uno o más procesos `php artisan queue:work` con la misma conexión que la app.
3. Tras desplegar: `php artisan queue:restart`.

Ejemplo Supervisor con Redis:

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

En la imagen Docker de este repo, un proceso **`queue-worker`** equivalente ya está definido en Supervisor — véase [DOCKER.md](DOCKER.md#supervisor-un-solo-contenedor-app).

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

## Tiempo real (Reverb / Soketi / Pusher)

1. **Backend:** `BROADCAST_CONNECTION=reverb` (Laravel Reverb en el mismo proyecto) **o** `pusher` con variables `PUSHER_APP_*` alineadas con Soketi, Pusher Cloud, etc.
2. **Colas y workers:** los eventos `ShouldBroadcast` generan jobs en la cola por defecto. **Varias réplicas PHP** requieren la misma `QUEUE_CONNECTION` y uno o más procesos `php artisan queue:work` (o Horizon). Sin workers, los WS no salen aunque HTTP responda.
3. **Escalado horizontal:** el servidor WS absorbe conexiones; los cuellos habituales son **Redis** (cola), **workers** insuficientes (backlog de broadcasts y de análisis IA), y **rate** de jobs `BroadcastEvent`. Escalar workers antes que réplicas PHP si la cola crece.
4. **Frontend:** en la **misma** build que despliegas, Vite debe recibir **`VITE_REVERB_APP_KEY`** (y host/puerto según despliegue) para Reverb, o **`VITE_PUSHER_APP_KEY`** + host/puerto para Soketi/Pusher.
5. **HTTPS:** detrás de proxy, coherencia de esquema `wss` y cookies para `/broadcasting/auth`; si Echo usa el puerto dedicado de Reverb (p. ej. 9090), abrir firewall/balanceador según política.
6. **Coste / superficie:** eventos acotados (detalle de post: likes, comentarios, análisis listo + canal privado por usuario); sin WS en el feed completo.

**Métricas:** con Redis de caché activo, `GET /internal/metrics` puede exponer `broadcasts_emitted_last_minute`; logs `broadcast.emitted` en canal `structured` para latencia del dispatch local.

Ejemplo **Reverb** local/Docker: variables `REVERB_APP_*` y `VITE_REVERB_*` alineadas; servidor `php artisan reverb:start`. Ejemplo **Soketi**: `PUSHER_HOST=127.0.0.1`, `PUSHER_PORT=6001`, `PUSHER_SCHEME=http` y `VITE_PUSHER_*` equivalentes.

## Documentación relacionada

- [SECURITY.md](SECURITY.md) — riesgos.
- [PERFORMANCE.md](PERFORMANCE.md) — rendimiento del feed.
- [DOCKER.md](DOCKER.md) — stack local.

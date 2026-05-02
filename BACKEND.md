# Backend — Laravel

Convenciones, puntos de extensión y contratos del feed. **Última revisión:** 2026-05-03.

## Estructura relevante

| Ruta / clase | Función |
|--------------|---------|
| `app/Http/Controllers/WallController` | Muro: vista + `FilterPostsRequest` → `WallFeedService::respond()`. |
| `app/Services/WallFeedService.php` | **Núcleo del feed**: ramas «siguiendo», mixto 70/30, exploración global; filtros y orden. |
| `app/Http/Requests/FilterPostsRequest.php` | Valida `sort` ∈ `recent`, `popular`, `trending`; `following`; `page`/`per_page`; etiquetas; búsqueda. |
| `app/Http/Resources/PostResource.php` | Formato JSON único de posts en respuestas públicas. |
| `app/Policies/PostPolicy.php` | Autorización crear/actualizar posts. |
| `app/Http/Middleware/SecurityHeaders.php` | Cabeceras + CSP (producción). |
| `config/security.php` | CSP y overrides por `.env`. |

Otros requests destacados: `StorePostRequest`, `StorePostCommentRequest`, `UserPostsRequest`, etc.

## Feed HTTP — contrato

- **Endpoint:** `GET /posts/filter`
- **Throttle:** `feed-filter` (véase [SECURITY.md](SECURITY.md#rate-limiting-por-nombre-referencia)).
- **Parámetros clave:**
  - `sort` — orden/ranking (ver [ARCHITECTURE.md](ARCHITECTURE.md#feed-del-muro-wallfeedservice)).
  - `following=1` — solo cuentas seguidas (requiere sesión para contenido).
  - `page`, `per_page` (límite superior acotado en el Form Request).

La lógica **no** debe duplicarse en el controlador: mantenerla en `WallFeedService`.

## Rate limiting

Los limitadores con nombre se registran en `AppServiceProvider` y se asocian a rutas en `routes/web.php` / `routes/auth.php`. Tabla completa: [SECURITY.md](SECURITY.md#rate-limiting-por-nombre-referencia).

Ejemplos relevantes al dominio social:

- `create-post` — creación de publicaciones.
- `feed-filter` — lectura del muro.
- `follow-toggle` — seguir / dejar de seguir.
- `comment-store`, `like-toggle`, `notifications-api`, etc.

## Policies y autorización

- Posts: `PostPolicy` en rutas que lo declaren.
- Comentarios: validación de `parent_id` acotada al mismo post en `StorePostCommentRequest`.

## Buenas prácticas observadas

- **Eager loading** en el feed: usuario, etiquetas con columnas acotadas, `withCount` para likes/comentarios, `withExists` para «like propio» si hay sesión.
- **Búsqueda:** comodines escapados en `LIKE`.
- **Etiquetas múltiples:** subconsulta con `HAVING COUNT(DISTINCT …) = N`.
- **Notificaciones API:** `NotificationApiPayload::forApi` filtra claves del JSON `data`.

## Comandos útiles

```bash
composer dev    # servidor + cola + vite (según composer.json)
php artisan test
composer lint / composer format   # Laravel Pint
```

## Referencias cruzadas

- Diseño del feed y ramas: [ARCHITECTURE.md](ARCHITECTURE.md).
- Rendimiento y paginación: [PERFORMANCE.md](PERFORMANCE.md).
- Seguridad y throttles: [SECURITY.md](SECURITY.md).

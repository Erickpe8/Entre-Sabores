# Base de datos

**Última revisión:** 2026-05-03.

## Motor

- Desarrollo / producción típicos: **MySQL 8** ([DOCKER.md](DOCKER.md)).
- Tests: **SQLite en memoria** (`phpunit.xml`).

## Esquema principal (orden lógico)

1. **`users`** — Identidad, `username` único, perfil, preferencias JSON, país, redes.
2. **`posts`** — Evolución desde legacy; migración grande **refactor_posts_for_tags_and_comment_threads** añade descripción, imagen, hilos; parte del cambio es **irreversible** (`down()` excepción).
3. **`tags`** — Tipos (país, tipo de comida, experiencia, bebida); `iso_code` en países para banderas (`/public/flags`).
4. **`post_tag`** — N:M; índice en `tag_id` para filtros del feed.
5. **`comments`** — `parent_id` para hilos.
6. **`likes`** — Índice compuesto `(user_id, post_id)` para unicidad y consultas.
7. **`follows`** — `follower_id`, `following_id`; índice en `following_id` útil para feeds «siguiendo».

## Índices de rendimiento

Además de los anteriores:

- **`posts_user_id_created_at_index`** — feed por autor / orden temporal.
- **`notifications_notifiable_read_index`** — listados y filtros por `read_at`.

Migraciones de referencia: `add_likes_follows_post_tag_indexes`, `2026_05_03_100000_add_performance_indexes_posts_notifications`.

## Datos de referencia

Seeders: países, catálogo amplio de etiquetas (`TagSeeder`), datos de ejemplo opcionales.

## Observaciones

| Tema | Nota |
|------|------|
| Rollback | Migración grande de posts/tags puede impedir `migrate:rollback` limpio; backups antes de desplegar en equipos nuevos. |
| Borrado en cascada | Revisar políticas si se añaden borrados masivos de usuarios o posts. |

## Referencias

- Consultas del feed: [PERFORMANCE.md](PERFORMANCE.md).
- Relaciones en código: modelos en `app/Models/`.

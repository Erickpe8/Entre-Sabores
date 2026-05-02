# Entre Sabores

Red social gastronómica orientada al intercambio cultural (proyecto COIL México–Colombia): publicaciones con etiquetas, muro con exploración y modo «siguiendo», likes, comentarios en hilos, perfiles públicos y notificaciones.

## Feed del muro (lectura rápida)

| En la UI | Parámetro HTTP | Comportamiento backend (resumen) |
|----------|----------------|----------------------------------|
| **FYP** / **Siguiendo** | `following` ausente vs `following=1` | Fuente: exploración global vs solo cuentas que sigues (invitado en «Siguiendo» ve mensaje de login). Detalle en [ARCHITECTURE.md](ARCHITECTURE.md#feed-del-muro-wallfeedservice). |
| **Recientes** / **Populares** / **Tendencia** | `sort=recent` \| `popular` \| `trending` | Orden y reglas de ranking; con usuario autenticado en FYP y **Recientes**, aplica la **mezcla ~70 % seguidos + ~30 % global por engagement** si tiene seguidos. |

Los nombres de chips no coinciden literalmente con los valores de `sort` (son etiquetas de producto). El contrato de API sigue siendo **`sort` en inglés**.

## Entorno y producción

- **Local**: PHP 8.4, Composer, Node; base de datos según `.env` (MySQL recomendado alineado con Docker).
- **Producción**: seguir [PRODUCTION.md](PRODUCTION.md) (checklist) y [SECURITY.md](SECURITY.md). Referencia de variables: [.env.production.example](.env.production.example).
- **Madurez**: validaciones, policies, rate limits diferenciados, tests de características sociales; observabilidad (logs JSON, métricas Redis, `/health`, `/internal/metrics`) — ver [PRODUCTION.md](PRODUCTION.md).

## Stack

| Área | Tecnología |
|------|------------|
| Backend | Laravel 13, PHP 8.4 |
| Auth / scaffolding | Laravel Breeze |
| Frontend | Blade, Vite, Tailwind CSS, Alpine.js, Axios |
| Base de datos | MySQL (desarrollo con Docker; véase abajo) |

## Documentación del repositorio

| Documento | Contenido |
|-----------|-----------|
| [ARCHITECTURE.md](ARCHITECTURE.md) | Decisiones de arquitectura, **WallFeedService**, feed 70/30, observabilidad |
| [BACKEND.md](BACKEND.md) | Requests, servicios, policies, rate limiting |
| [FRONTEND.md](FRONTEND.md) | Vite, muro (`wall.js`), estado del feed, CSRF |
| [DATABASE.md](DATABASE.md) | Tablas, migraciones relevantes, índices |
| [CHANGELOG.md](CHANGELOG.md) | Historial |
| [PRODUCTION.md](PRODUCTION.md) | Despliegue, Redis, salud, métricas, backups |
| [SECURITY.md](SECURITY.md) | Superficie de seguridad y prácticas |
| [PERFORMANCE.md](PERFORMANCE.md) | Feed, caché invitados, índices, cuellos de botella |
| [DOCKER.md](DOCKER.md) | Stack PHP/Nginx/MySQL/phpMyAdmin/Redis |
| [.env.production.example](.env.production.example) | Variables de referencia para producción |

## Requisitos rápidos

- PHP 8.4, Composer, Node.js/npm
- Base de datos configurada en `.env` (copiar desde `.env.example`)

## Puesta en marcha (local sin Docker)

```bash
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate
npm install
npm run build   # o npm run dev durante desarrollo
php artisan serve
```

Script Composer útil: `composer setup` (instala dependencias, migraciones, build de front).

## Pruebas

```bash
php artisan test
```

## Docker

Desarrollo con PHP, Nginx, MySQL, phpMyAdmin y Redis: consulta [DOCKER.md](DOCKER.md) y levanta el stack con `docker compose up -d` desde la raíz del proyecto.

## Licencia

MIT (plantilla Laravel); el contenido específico del proyecto Entre Sabores pertenece al equipo del proyecto según los acuerdos académicos aplicables.

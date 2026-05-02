# Frontend — Blade, Vite y JavaScript

**Última revisión:** 2026-05-03.

## Empaquetado

- **Entrada:** `resources/js/app.js` importa Bootstrap (Axios), Flowbite, Alpine.js, Cropper.js y CSS.
- **Code splitting:** módulos bajo demanda según el DOM:
  - `#posts-container` → `resources/js/wall.js`
  - `#post-show-page` → `post-show.js`
  - `#profile-posts-grid` → `profilePosts.js`
  - `#nav-notifications-root` → `notificationsNav.js`

## Integración Laravel

- Meta **CSRF** en layout; `resources/js/bootstrap.js` configura Axios (`X-CSRF-TOKEN`, `withCredentials`).
- **Vite:** `@vite(['resources/css/app.css', 'resources/js/app.js'])` en layouts.
- **Config inyectada:** `wallConfig` (y otras) desde controladores; rutas relativas donde aplica para cookies de sesión.

## Muro (`wall.js`) — estado del feed

### Dos niveles de navegación (no mezclar)

| UI | Estado JS | Efecto |
|----|-----------|--------|
| Toggle **FYP** / **Siguiendo** (`[data-navbar-feed]`) | `state.following` | Añade `following=1` a la query cuando corresponde; «Siguiendo» sin sesión redirige a login. |
| Chips **Recientes** / **Populares** / **Tendencia** (`[data-sort]`) | `state.sort` | Valores internos: `recent`, `popular`, `trending` — deben coincidir con el backend. |

Los chips **no** usan la etiqueta «Para ti» para evitar duplicar el concepto del **FYP** (feed personalizado a nivel de fuente).

### Peticiones

- Cliente: **Axios** `GET` a la URL del feed (`config.filterUrl`, típicamente `/posts/filter`).
- Query construida en `buildQuery()`: `sort`, `following`, `search`, `page`, `per_page`.
- Al cambiar chip o toggle: `scheduleFetch()` resetea paginación (`page = 1`) y vuelve a cargar.
- Scroll infinito: solicita la página siguiente con los **mismos** parámetros de modo.

### Ayuda contextual y feedback

- Textos de ayuda bajo los chips se actualizan según `state.sort` y si `state.following` (badge «Siguiendo», hints distintos definidos en `SORT_UX` / `SORT_UX_FOLLOWING_HINT`).
- La URL del navegador se sincroniza con `history.replaceState` (`syncFeedQueryParam`), omitiendo `sort` en la URL cuando es el valor por defecto «recientes» si así está implementado.

### Archivos de vista

- `resources/views/wall/index.blade.php` — barra de búsqueda, tablist de orden, franja de contexto «Orden activo».

## UX / UI global

- Layout: Tailwind; rutas de muro/perfil con tema oscuro (`bg-slate-950`).
- Open Graph en detalle de post para enlaces compartidos.

## Hallazgos y mejoras opcionales

| Tema | Nota |
|------|------|
| CDN (Font Awesome) | Latencia y CSP; opción autopublicar en `public/`. |
| Accesibilidad | Tablist con `aria-selected`; revisión sistemática de foco/teclado pendiente de auditoría manual. |

## Referencias

- Comportamiento servidor del feed: [ARCHITECTURE.md](ARCHITECTURE.md).
- Seguridad CSRF: [SECURITY.md](SECURITY.md).

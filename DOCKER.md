# Entre Sabores — desarrollo con Docker

Stack: **PHP 8.4 (FPM)**, **Nginx**, **MySQL 8**, **phpMyAdmin** y **Redis** (caché/colas o futuros workers).

| Servicio     | Uso en el host (por defecto) |
|-------------|------------------------------|
| Aplicación  | <http://localhost:8080>      |
| phpMyAdmin  | <http://localhost:8081>      |
| MySQL       | `127.0.0.1:3307` (mapeo por defecto) |
| Redis       | `127.0.0.1:6380` (mapeo por defecto; en la red interna el puerto del servicio sigue siendo 6379) |

## Requisitos

- Docker y Docker Compose v2
- Archivo `.env` en la raíz (a partir de `.env.example`)

## Puesta en marcha

1. **Variables de entorno** (ajusta alineando usuario/clave y `APP_KEY` si hace falta):

   ```bash
   cp .env.example .env
   # Si APP_KEY está vacía:
   php artisan key:generate
   ```

   O, solo con contenedores:

   ```bash
   docker compose run --rm app php artisan key:generate
   ```

2. Asegúrate de que en `.env` tengas credenciales coherentes con el servicio `mysql` (o usa las por defecto del ejemplo: base `entre_sabores`, usuario `entre_sabores`, clave `secret`, root `DB_ROOT_PASSWORD=root`).

3. **Levantar todo**

   ```bash
   docker compose up -d --build
   ```

4. **Migraciones**

   ```bash
   docker compose exec app php artisan migrate
   ```

5. **Composer** (el primer arranque instala `vendor` si no existe; para actualizaciones:)

   ```bash
   docker compose exec app composer update
   ```

6. **Frontend (Vite)** no está en los contenedores: en el host, con Node instalado:

   ```bash
   npm install
   npm run dev
   ```

   Para solo assets de producción: `npm run build` (genera `public/build` en el volumen compartido).

## Variables útiles (`.env` o entorno al invocar compose)

- `DB_ROOT_PASSWORD` — clave de `root` en MySQL (debe coincidir con el `healthcheck` y con lo que uses en phpMyAdmin).
- `APP_PORT` — mapea el puerto de Nginx (por defecto 8080).
- `PMA_PORT` — phpMyAdmin (por defecto 8081).
- `DB_PORT_EXPOSED` / `REDIS_PORT_EXPOSED` — si necesitas evitar colisiones con instancias locales.

## Conexión desde el host a MySQL

- Host: `127.0.0.1`
- Puerto: el publicado (por defecto 3307).
- Usuario/contraseña: los de `DB_USERNAME` / `DB_PASSWORD` o `root` / `DB_ROOT_PASSWORD`.

Dentro de la red Docker, Laravel usa `DB_HOST=mysql` (fijado en `docker-compose`).

## Escalado futuro (referencia)

- Añade un servicio `queue` con `php artisan queue:work` (misma imagen `app` o `entre-sabores-app`, otro `command`).
- Usa el servicio `redis` y en `.env` define `REDIS_HOST=redis`, y opcionalmente `CACHE_STORE=redis` y `QUEUE_CONNECTION=redis` (Laravel admite Predis o extensión `phpredis` si la añades a la imagen).
- Tareas programadas: contenedor con `php artisan schedule:work` o un cron sidecar.
- Añade volúmenes o servicio de objetos (MinIO, S3) según almacenamiento de archivos.

## Nginx y recreación de contenedores

Tras un `--build` o al recrearse el servicio `app`, si ves **502** en el navegador, suele deberse a que Nginx mantiene la IP antigua de `app`. En esta configuración se usa el **resolver** de Docker (`127.0.0.11`) y `fastcgi_pass` con variable para volver a resolver el nombre. Aun así, un `docker compose restart nginx` restablece el servicio de inmediato.

## Comandos frecuentes

```bash
docker compose logs -f app
docker compose exec app php artisan tinker
docker compose down
```

Para borrar la base y volver a empezar (datos de MySQL en volumen `mysql_data`):

```bash
docker compose down -v
```

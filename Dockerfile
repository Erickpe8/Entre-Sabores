# PHP-FPM 8.4 + Nginx (supervisord) — Laravel 13, HTTP en el puerto 80 (CapRover / producción).
FROM php:8.4-fpm-bookworm

RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    ca-certificates \
    zip \
    unzip \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libicu-dev \
    nginx \
    supervisor \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-configure intl \
    && docker-php-ext-install -j"$(nproc)" \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    intl \
    opcache \
    zip \
    gd

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY docker/php/local.ini $PHP_INI_DIR/conf.d/99-local.ini
COPY docker/php/docker-app-entrypoint.sh /usr/local/bin/docker-app-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-app-entrypoint.sh

COPY docker/nginx/web.conf /etc/nginx/sites-available/laravel
RUN ln -sf /etc/nginx/sites-available/laravel /etc/nginx/sites-enabled/laravel \
    && rm -f /etc/nginx/sites-enabled/default

COPY docker/php/supervisord-laravel.conf /etc/supervisor/conf.d/laravel.conf

WORKDIR /var/www/html

COPY . .

RUN mkdir -p storage/app/public storage/logs storage/framework/sessions storage/framework/views storage/framework/cache/data bootstrap/cache

RUN cp .env.example .env \
    && composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader \
    && rm -f .env \
    && chown -R www-data:www-data storage bootstrap/cache vendor

RUN nginx -t

EXPOSE 80

ENTRYPOINT ["/usr/local/bin/docker-app-entrypoint.sh"]
CMD ["/usr/bin/supervisord", "-n", "-c", "/etc/supervisor/supervisord.conf"]

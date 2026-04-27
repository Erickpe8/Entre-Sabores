# PHP-FPM 8.4+ (composer.json: ^8.4, Laravel 13)
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

WORKDIR /var/www/html

COPY . .

RUN mkdir -p storage/app/public storage/logs storage/framework/sessions storage/framework/views storage/framework/cache/data bootstrap/cache

RUN cp .env.example .env \
    && composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader \
    && rm -f .env \
    && chown -R www-data:www-data storage bootstrap/cache vendor

ENTRYPOINT ["/usr/local/bin/docker-app-entrypoint.sh"]
CMD ["php-fpm"]

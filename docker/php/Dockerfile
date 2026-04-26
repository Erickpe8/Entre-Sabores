# PHP-FPM 8.4+ (alineado con composer.lock / vendor: Laravel 13 y dependencias)
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

ENTRYPOINT ["/usr/local/bin/docker-app-entrypoint.sh"]
CMD ["php-fpm"]

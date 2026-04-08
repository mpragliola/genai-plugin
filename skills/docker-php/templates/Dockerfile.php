# Multi-stage PHP Dockerfile
# Stages: base → dev → test → prod → worker → scheduler

# --- base: shared foundation ---
FROM php:8.4-fpm-alpine AS base

RUN apk add --no-cache linux-headers icu-dev libzip-dev oniguruma-dev \
    libpng-dev libjpeg-turbo-dev freetype-dev curl

RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    pdo_mysql mbstring intl zip opcache bcmath gd pcntl

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /var/www

# --- dev: xdebug, volume mount, no source copy ---
FROM base AS dev
RUN apk add --no-cache $PHPIZE_DEPS \
    && pecl install xdebug pcov redis \
    && docker-php-ext-enable xdebug pcov redis
COPY docker/php/php-dev.ini $PHP_INI_DIR/conf.d/zz-dev.ini
CMD ["php-fpm"]
EXPOSE 9000

# --- test: copy source + dev deps, CMD runs tests ---
FROM base AS test
COPY composer.json composer.lock ./
RUN composer install --no-scripts --no-autoloader --no-interaction
COPY . .
RUN composer dump-autoload --optimize
CMD ["php", "vendor/bin/phpunit", "--colors=always"]

# --- prod: no-dev, opcache, non-root, immutable ---
FROM base AS prod
COPY docker/php/php-prod.ini $PHP_INI_DIR/conf.d/zz-prod.ini
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-interaction --prefer-dist --optimize-autoloader
COPY . .
RUN composer dump-autoload --optimize --classmap-authoritative
# Laravel: route:cache and view:cache are safe here.
# WARNING: do NOT run config:cache at build time — it bakes env vars into the image.
#          Run it in an entrypoint script at container startup instead.
# RUN php artisan route:cache && php artisan view:cache
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache 2>/dev/null || true
USER www-data
HEALTHCHECK --interval=30s --timeout=5s --start-period=10s --retries=3 \
    CMD php-fpm -t || exit 1
CMD ["php-fpm"]
EXPOSE 9000

# --- worker: same image, queue worker CMD ---
FROM prod AS worker
CMD ["php", "artisan", "queue:work", "--sleep=3", "--tries=3", "--max-time=3600"]

# --- scheduler: same image, scheduler CMD ---
FROM prod AS scheduler
CMD ["php", "artisan", "schedule:work"]

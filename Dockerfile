FROM php:8.4-fpm-trixie AS base

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        libpq-dev libzip-dev libpng-dev libjpeg62-turbo-dev libfreetype-dev libicu-dev \
        unzip libreoffice-writer-nogui fonts-dejavu-core fonts-liberation swi-prolog-nox \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" pdo_pgsql pgsql gd zip intl bcmath exif opcache \
    && rm -rf /var/lib/apt/lists/*

COPY docker/php/php.ini /usr/local/etc/php/conf.d/zz-proyecto.ini
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html


FROM base AS vendor

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction --no-progress


FROM node:22-trixie-slim AS assets

WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci --no-audit --no-fund
COPY . .
COPY --from=vendor /var/www/html/vendor/laravel/framework/src/Illuminate/Pagination/resources/views ./vendor/laravel/framework/src/Illuminate/Pagination/resources/views
RUN npm run build


FROM base AS app

COPY --from=vendor /var/www/html/vendor ./vendor
COPY . .
COPY --from=assets /app/public/build ./public/build

RUN composer dump-autoload --optimize --no-dev --no-interaction \
    && mkdir -p storage/app/private storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs storage/prolog bootstrap/cache \
    && cp -R storage/app/private/plantillas /opt/plantillas \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod +x docker/app/entrypoint.sh

USER www-data

ENTRYPOINT ["/var/www/html/docker/app/entrypoint.sh"]
CMD ["php-fpm"]


FROM nginx:stable-alpine AS web

COPY docker/nginx/default.conf.template /etc/nginx/templates/default.conf.template
COPY --from=app /var/www/html/public /var/www/html/public

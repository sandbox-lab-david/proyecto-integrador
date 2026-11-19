#!/bin/sh
set -e

cd /var/www/html

mkdir -p storage/app/private/plantillas storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs storage/prolog
cp -R /opt/plantillas/. storage/app/private/plantillas/

if [ "${PREPARAR_LARAVEL:-true}" = "true" ]; then
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
fi

if [ "${EJECUTAR_MIGRACIONES:-false}" = "true" ]; then
    php artisan migrate --force
fi

exec "$@"

#!/bin/sh
set -eu

if [ "${1:-}" = "php-fpm" ]; then
    mkdir -p \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
        bootstrap/cache

    chown -R www-data:www-data storage bootstrap/cache
    chmod -R ug+rwX storage bootstrap/cache
fi

exec /usr/local/bin/docker-php-entrypoint "$@"

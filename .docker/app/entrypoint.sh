#!/usr/bin/env bash
set -euo pipefail

cd /var/www/html

git config --global --add safe.directory /var/www/html >/dev/null 2>&1 || true

if [ ! -f .env ] && [ -f .env.example ]; then
    cp .env.example .env
fi

mkdir -p \
    bootstrap/cache \
    database \
    storage/app/docker \
    storage/app/private \
    storage/app/public \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/testing \
    storage/framework/views \
    storage/logs/services

if [ "${DB_CONNECTION:-sqlite}" = "sqlite" ]; then
    touch database/database.sqlite
fi

if [ ! -d vendor ]; then
    composer install
fi

if [ -f artisan ] && ! grep -q '^APP_KEY=base64:' .env; then
    php artisan key:generate --force
fi

if [ -f artisan ]; then
    php artisan migrate --force || true
fi

chown -R www-data:www-data storage bootstrap/cache database || true
chmod -R 0777 storage bootstrap/cache database || true

exec "$@"

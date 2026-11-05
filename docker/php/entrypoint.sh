#!/bin/sh
set -e

cd /var/www/html

# The API uses no sessions or encryption, so a throw-away key is enough when none is supplied.
if [ -z "${APP_KEY:-}" ]; then
    export APP_KEY="$(php artisan key:generate --show)"
fi

php artisan migrate --force

if [ "${SEED_DEMO_DATA:-false}" = "true" ]; then
    php artisan db:seed --force
fi

chown -R www-data:www-data storage bootstrap/cache
touch /tmp/app-ready
exec "$@"

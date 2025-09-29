#!/bin/sh
set -e

# Link storage if not exists
if [ ! -L /var/www/html/public/storage ]; then
  php artisan storage:link || true
fi

# Set permissions (idempotent, lightweight)
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache || true

exec "$@"

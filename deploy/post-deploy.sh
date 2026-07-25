#!/bin/bash
set -e

cd "$(dirname "$0")/.."

rm -f bootstrap/cache/*.php

composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan queue:restart

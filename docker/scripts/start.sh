#!/bin/bash
set -e
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan migrate --force
php artisan route:list
service nginx start
php-fpm -F

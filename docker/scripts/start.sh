#!/bin/bash
ls -la /var/www/html/
ls -la /var/www/html/public/
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan migrate --force
service nginx start
php-fpm

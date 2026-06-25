#!/bin/bash
set -e

# Iniciar MySQL
service mysql start

# Esperar a que MySQL esté listo
sleep 5

# Crear base de datos y usuario
mysql -e "CREATE DATABASE IF NOT EXISTS test;"
mysql -e "CREATE USER IF NOT EXISTS 'alumno'@'localhost' IDENTIFIED BY 'alumno';"
mysql -e "GRANT ALL PRIVILEGES ON test.* TO 'alumno'@'localhost';"
mysql -e "FLUSH PRIVILEGES;"

# Laravel setup
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan migrate --force
php artisan route:list

# Iniciar nginx y PHP-FPM
service nginx start
php-fpm -F

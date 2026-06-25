FROM php:8.3-fpm

RUN apt-get update && apt-get install -y \
    nginx git curl unzip libzip-dev libpng-dev default-mysql-server \
    && docker-php-ext-install pdo_mysql zip gd \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY src/ /var/www/html/

RUN composer install --no-dev --optimize-autoloader

COPY docker/nginx/render.conf /etc/nginx/conf.d/default.conf
RUN rm -f /etc/nginx/sites-enabled/default

RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && mkdir -p /var/run/mysqld \
    && chown mysql:mysql /var/run/mysqld

COPY docker/scripts/start.sh /start.sh
RUN chmod +x /start.sh

EXPOSE 80

CMD ["/start.sh"]

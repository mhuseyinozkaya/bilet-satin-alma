
FROM php:8.2-apache

COPY ./src /var/www/html

RUN apt-get update && apt-get install -y libsqlite3-dev && docker-php-ext-install pdo pdo_sqlite

RUN chown -R www-data:www-data /var/www/html

EXPOSE 80

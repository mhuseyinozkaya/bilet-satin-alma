
FROM php:8.2-apache

COPY ./src /var/www/html
COPY ./database /var/www

RUN apt-get update && apt-get install -y libsqlite3-dev && docker-php-ext-install pdo pdo_sqlite
RUN chown -R www-data /var/www
EXPOSE 80

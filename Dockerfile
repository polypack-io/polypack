FROM serversideup/php:8.4-fpm-nginx-alpine

USER root

RUN install-php-extensions intl

USER www-data

COPY --chown=www-data:www-data . /var/www/html

RUN cd /var/www/html && composer install
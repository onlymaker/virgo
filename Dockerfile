FROM syncxplus/php:7.3.18-apache-stretch

LABEL maintainer=jibo@outlook.com

COPY . /var/www/

RUN mv /var/www/php.ini /usr/local/etc/php/php.ini && chown -R www-data:www-data /var/www

USER www-data

USER root

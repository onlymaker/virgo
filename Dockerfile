FROM syncxplus/php:7.3.11-apache-stretch

LABEL maintainer=jibo@outlook.com

COPY . /var/www/

RUN mv /var/www/docker-php-entrypoint /usr/local/bin/ \
  && mv /var/www/php.ini /usr/local/etc/php/php.ini \
  && chown -R www-data:www-data /var/www

USER www-data

USER root

COPY docker-php-entrypoint /usr/local/bin/

FROM syncxplus/php:7.3.11-cli-stretch

LABEL maintainer=jibo@outlook.com

WORKDIR /data/
COPY cli ./cli
COPY src ./src
COPY vendor ./vendor
COPY bootstrap.php ./

ENTRYPOINT ["docker-php-entrypoint"]
CMD ["php", "cli/background.php"]

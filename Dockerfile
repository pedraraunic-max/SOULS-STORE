FROM php:8.3-apache

RUN docker-php-ext-install pdo_mysql

WORKDIR /var/www/html
COPY . /var/www/html/
COPY docker/apache-entrypoint.sh /usr/local/bin/apache-render-entrypoint
RUN chmod +x /usr/local/bin/apache-render-entrypoint

EXPOSE 10000
CMD ["apache-render-entrypoint"]

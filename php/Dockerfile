FROM php:5.6-apache
COPY php.ini /usr/local/etc/php/
COPY apache-config.conf /etc/apache2/sites-enabled/000-default.conf

RUN docker-php-ext-install pdo pdo_mysql mysqli
RUN a2enmod rewrite && a2enmod headers

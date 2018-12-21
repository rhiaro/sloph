FROM php:5.6-apache

RUN apt-get update \
    && apt-get install libpng-dev libfreetype6-dev libjpeg62-turbo-dev -qy \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

COPY php.ini /usr/local/etc/php/
COPY apache-config.conf /etc/apache2/sites-enabled/000-default.conf

RUN docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
    && docker-php-ext-install pdo pdo_mysql mysqli gd
RUN a2enmod rewrite && a2enmod headers





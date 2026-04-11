FROM php:8.5-fpm

ARG UID=1000
ARG GID=1000

RUN usermod -u ${UID} www-data && groupmod -g ${GID} www-data

WORKDIR /var/www/html

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    && docker-php-ext-install zip pdo pdo_mysql \
    && pecl install redis xdebug \
    && docker-php-ext-enable xdebug \
    && docker-php-ext-enable redis \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY _docker/php/xdebug.ini /usr/local/etc/php/conf.d/99-xdebug.ini

RUN git config --global --add safe.directory /var/www/html \
    && mkdir -p /var/www/.composer && chown www-data:www-data /var/www/.composer

USER www-data

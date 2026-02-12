FROM php:8.2-apache

RUN apt-get update && apt-get upgrade -y && apt-get install -y \
    libzip-dev \
    zip \
    unzip \
 && docker-php-ext-install zip pdo pdo_mysql \
 && a2enmod rewrite \
 && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

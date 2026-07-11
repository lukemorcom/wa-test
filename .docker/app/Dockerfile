FROM php:8.4-cli

RUN apt-get update && apt-get install -y \
    unzip \
    libmariadb-dev \
    && docker-php-ext-install pdo pdo_mysql

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
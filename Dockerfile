# TechGate - imagen Docker para despliegue en AWS (entregable #2).
# PHP 8.4 (requerido por Laravel 13) sobre Apache. Compatible con
# Elastic Beanstalk (Docker), AWS App Runner y Amazon ECS.

FROM php:8.4-apache

# Extensiones de PHP necesarias (SQLite, GD, zip, intl para i18n).
RUN apt-get update && apt-get install -y --no-install-recommends \
        libsqlite3-dev libzip-dev libpng-dev libjpeg-dev libfreetype6-dev \
        libonig-dev libicu-dev unzip git \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_sqlite gd zip mbstring intl bcmath \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Apache: servir desde /public y permitir .htaccess (URLs amigables).
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf \
    && a2enmod rewrite

WORKDIR /var/www/html

# Dependencias primero (aprovecha la cache de capas).
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

# Codigo de la aplicacion.
COPY . .
RUN composer dump-autoload --optimize

# Permisos de storage y cache, y base SQLite.
RUN mkdir -p storage/framework/{sessions,views,cache} database \
    && touch database/database.sqlite \
    && chown -R www-data:www-data storage bootstrap/cache database \
    && chmod -R 775 storage bootstrap/cache

# Script de arranque: prepara env, clave, migra, siembra y levanta Apache.
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 80
ENTRYPOINT ["entrypoint.sh"]

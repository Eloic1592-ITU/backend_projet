# Étape 1 : Builder les dépendances avec Composer
FROM composer:2 AS build

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-interaction --prefer-dist --optimize-autoloader

# Étape 2 : Image finale avec PHP et Nginx
FROM php:8.2-fpm

# Installer extensions nécessaires à Laravel + PostgreSQL
RUN apt-get update && apt-get install -y \
    libpng-dev libjpeg-dev libfreetype6-dev zip unzip git curl nginx supervisor libpq-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_pgsql gd bcmath \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

# Copier code source
COPY . .

# Copier vendor depuis l’étape build
COPY --from=build /app/vendor ./vendor

# Config Nginx
COPY docker/nginx.conf /etc/nginx/conf.d/default.conf

# Donner droits à Laravel
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Exposer le port
EXPOSE 8080

# Supervisord lance PHP-FPM + Nginx
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
CMD ["supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]

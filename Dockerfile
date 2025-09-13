FROM php:8.2-fpm

# Installer dépendances système et PostgreSQL
RUN apt-get update && apt-get install -y \
    nginx \
    supervisor \
    git unzip libpq-dev libonig-dev libxml2-dev zip curl \
    && docker-php-ext-install pdo pdo_pgsql mbstring bcmath exif pcntl \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Copier configs depuis le dossier docker/
COPY ./docker/nginx.conf /etc/nginx/sites-available/default
COPY ./docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Copier le projet Laravel
WORKDIR /var/www
COPY . .

# Installer Composer et dépendances Laravel
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && composer install --no-dev --optimize-autoloader

# Droits sur storage et bootstrap/cache
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

EXPOSE 80

CMD ["/usr/bin/supervisord"]

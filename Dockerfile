# Étape 1 : Image PHP avec extensions nécessaires
FROM php:8.2-fpm

# Installer dépendances système + extensions
RUN apt-get update && apt-get install -y \
    libpq-dev unzip git curl \
    && docker-php-ext-install pdo pdo_pgsql bcmath

# Installer Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Définir le dossier de travail
WORKDIR /var/www/html

# Copier le projet
COPY . .

# Installer les dépendances Laravel
RUN composer install --no-dev --optimize-autoloader

# Donner les permissions à Laravel
RUN chown -R www-data:www-data storage bootstrap/cache

# Compiler les caches Laravel
RUN php artisan config:clear \
 && php artisan route:clear \
 && php artisan view:clear \
 && php artisan config:cache \
 && php artisan route:cache \
 && php artisan view:cache

# Exposer le port
EXPOSE 8000

# Lancer Laravel et exécuter les migrations automatiquement
CMD php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=8000

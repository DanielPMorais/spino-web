# Compile the React/Inertia assets separately, keeping the final image small.
FROM node:22-alpine AS frontend
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY resources ./resources
COPY public ./public
COPY vite.config.js postcss.config.js tailwind.config.js ./
RUN npm run build

FROM composer:2 AS dependencies
WORKDIR /app
# Laravel runs `artisan package:discover` as part of Composer's install hooks.
# It therefore needs the application files, not only composer.json and lockfile.
COPY . .
RUN composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction

FROM php:8.3-apache
WORKDIR /var/www/html

RUN apt-get update \
    && apt-get install -y --no-install-recommends libpq-dev libonig-dev libzip-dev \
    && docker-php-ext-install pdo_pgsql pgsql mbstring bcmath zip \
    && a2enmod rewrite \
    && sed -ri 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf \
    && rm -rf /var/lib/apt/lists/*

COPY . .
COPY --from=dependencies /app/vendor ./vendor
COPY --from=frontend /app/public/build ./public/build

RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R ug+rwx storage bootstrap/cache

EXPOSE 80

# Migrations and the demo seed are safe to run again before each deployment.
CMD ["sh", "-c", "php artisan migrate --force && php artisan db:seed --force && apache2-foreground"]

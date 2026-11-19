FROM php:8.3-apache

RUN apt-get update && apt-get install -y --no-install-recommends \
        libpq-dev libzip-dev zip unzip git curl \
    && docker-php-ext-install pdo_pgsql zip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Skeleton Laravel officiel (config, artisan, public, vendor de base)
RUN composer create-project "laravel/laravel:^12.0" . --no-interaction --prefer-dist \
    && composer require laravel/sanctum --no-interaction

# Notre code applicatif (copie par-dessus le skeleton)
COPY composer.json ./
COPY app ./app
COPY bootstrap ./bootstrap
COPY database ./database
COPY resources ./resources
COPY routes ./routes
COPY artisan ./
COPY .env.example ./
RUN composer update --no-interaction --prefer-dist \
    && composer dump-autoload --optimize

# Apache : document root sur public/
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -i 's#/var/www/html#/var/www/html/public#g' /etc/apache2/sites-available/000-default.conf \
    && a2enmod rewrite headers

# Permissions ecriture pour Apache (www-data) : vues compilees, logs, cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache     && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 8000
ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["apache2-foreground"]

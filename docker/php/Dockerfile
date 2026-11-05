FROM php:8.5-fpm-alpine AS base

RUN docker-php-ext-install pdo_mysql
COPY docker/php/php.ini /usr/local/etc/php/conf.d/zz-app.ini
WORKDIR /var/www/html

FROM base AS vendor

RUN apk add --no-cache git unzip
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY composer.json composer.lock ./
RUN composer install --no-interaction --no-progress --prefer-dist --no-scripts --no-autoloader

COPY . .
RUN composer dump-autoload --optimize --no-scripts

FROM base

COPY --from=vendor /var/www/html /var/www/html
COPY docker/php/entrypoint.sh /usr/local/bin/app-entrypoint

# Empty placeholder: `php artisan test` insists on reading .env. Configuration comes from the container environment.
RUN chmod +x /usr/local/bin/app-entrypoint \
    && touch .env \
    && php artisan package:discover --ansi \
    && chown -R www-data:www-data storage bootstrap/cache

ENTRYPOINT ["app-entrypoint"]
CMD ["php-fpm"]

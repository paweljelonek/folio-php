FROM php:8.3-fpm-alpine

RUN apk add --no-cache \
    git \
    unzip \
    icu-dev \
    && docker-php-ext-install \
        opcache \
        intl

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

COPY . .

RUN mkdir -p var/cache \
    && chown -R www-data:www-data var/

EXPOSE 9000

CMD ["php-fpm"]

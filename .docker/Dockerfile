# Stage 1: Base
FROM php:8.4-fpm-alpine AS builder

RUN apk add --no-cache unzip git icu-dev libxml2-dev \
    && docker-php-ext-install intl soap opcache

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY composer.json composer.lock symfony.lock ./

RUN composer install --no-dev --no-scripts --no-progress --prefer-dist --optimize-autoloader

COPY . .

RUN composer dump-autoload --no-dev --classmap-authoritative && \
    composer run-script --no-dev post-install-cmd

# Stage 1: Final
FROM php:8.4-fpm-alpine

RUN apk add --no-cache libxml2 icu-libs

RUN apk add --no-cache --virtual .build-deps libxml2-dev icu-dev \
    && docker-php-ext-install intl soap opcache \
    && apk del .build-deps

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

ARG APP_ENV=prod
ENV APP_ENV=${APP_ENV}

COPY .docker/php-conf/php.${APP_ENV}.ini /usr/local/etc/php/conf.d/app.ini
COPY .docker/php-conf/php-fpm.conf /usr/local/etc/php-fpm.d/www.conf

WORKDIR /app

COPY --from=builder /app /app

RUN mkdir -p var && \
    chown -R www-data:www-data var

EXPOSE 9000

CMD ["php-fpm"]

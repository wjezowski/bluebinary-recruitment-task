FROM php:8.4-fpm-alpine AS builder

WORKDIR /var/www/html

RUN apk update
RUN apk add icu-dev
RUN docker-php-ext-configure intl
RUN docker-php-ext-install intl

COPY --from=composer /usr/bin/composer /usr/bin/composer

FROM builder AS final

COPY ./ /var/www/html/

RUN composer install
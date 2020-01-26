FROM composer:1.8 AS composer

RUN composer global require hirak/prestissimo

COPY composer.json composer.json
COPY stubs stubs
RUN  composer install --no-dev --optimize-autoloader --prefer-dist


FROM php:7.4-cli as rector
WORKDIR /rector

RUN docker-php-ext-configure opcache --enable-opcache \
    && docker-php-ext-install opcache

# Copy configuration
COPY .docker/php/opcache.ini /usr/local/etc/php/conf.d/conf.d/

COPY --from=composer /app .
COPY . .

ENTRYPOINT [ "bin/rector" ]


## Used for getrector.org/demo
FROM rector as rector-secured

COPY .docker/php/security.ini /usr/local/etc/php/conf.d/security.ini

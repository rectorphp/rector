FROM php:7.4-cli-alpine as rector
WORKDIR /rector

RUN apk update && apk upgrade && apk add --no-cache $PHPIZE_DEPS git libzip-dev \
    && docker-php-ext-install zip opcache

# Installing composer and prestissimo globally
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

ENV COMPOSER_ALLOW_SUPERUSER=1 COMPOSER_MEMORY_LIMIT=-1
RUN composer global require hirak/prestissimo --prefer-dist --no-progress --no-suggest --classmap-authoritative --no-plugins --no-scripts

# Copy configuration
COPY .docker/php/opcache.ini $PHP_INI_DIR/conf.d/opcache.ini

COPY composer.json composer.json
COPY stubs stubs

# This is to make parsing version possible
COPY .git .git

RUN  composer install --no-dev --optimize-autoloader --prefer-dist

RUN mkdir /tmp/opcache

COPY . .

# To warmup opcache a little
RUN bin/rector list

ENTRYPOINT [ "bin/rector" ]

# Cleanup
RUN apk del $PHPIZE_DEPS git libzip-dev \
    && rm -rf /var/cache/apk/* \
    && rm -rf ~/.composer /usr/bin/composer \
    && rm -rf $PHP_INI_DIR/conf.d/docker-php-ext-zip.ini

## Used for getrector.org/demo
FROM rector as rector-secured

COPY .docker/php/security.ini $PHP_INI_DIR/conf.d/security.ini

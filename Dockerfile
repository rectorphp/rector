FROM php:7.4-cli as rector
WORKDIR /rector

# Install php extensions
RUN apt-get update && apt-get install -y \
        git \
        unzip \
        g++ \
        libzip-dev \
    && pecl -q install \
        zip \
    && docker-php-ext-configure \
        opcache --enable-opcache \
    && docker-php-ext-enable \
        zip \
        opcache

# Installing composer and prestissimo globally
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

ENV COMPOSER_ALLOW_SUPERUSER=1 COMPOSER_MEMORY_LIMIT=-1
RUN composer global require hirak/prestissimo --prefer-dist --no-progress --no-suggest --classmap-authoritative --no-plugins --no-scripts

# Copy configuration
COPY .docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

COPY composer.json composer.json
COPY stubs stubs

# This is to make parsing version possible
COPY .git .git

RUN  composer install --no-dev --optimize-autoloader --prefer-dist

RUN mkdir /tmp/opcache

COPY . .

# To warmup opcache a little
RUN bin/rector list 2>&1 > /dev/null

ENTRYPOINT [ "bin/rector" ]


## Used for getrector.org/demo
FROM rector as rector-secured

COPY .docker/php/security.ini /usr/local/etc/php/conf.d/security.ini

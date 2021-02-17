ARG PHP_VERSION=8.0
FROM php:${PHP_VERSION}-cli as base

RUN apt-get update && apt-get install -y \
    libzip4 \
    libicu63 \
    && rm -rf /var/lib/apt/lists/*

FROM base as build

WORKDIR /build

# Install php extensions
RUN apt-get update && apt-get install -y \
        g++ \
        git \
        libicu-dev \
        libzip-dev \
        unzip \
        wget \
        zip \
    && pecl -q install \
        zip \
    && docker-php-ext-configure intl \
    && docker-php-ext-configure opcache --enable-opcache \
    && docker-php-ext-install \
        intl \
        opcache \
        zip

COPY --from=composer:2.0.9 /usr/bin/composer /usr/bin/composer

ENV COMPOSER_ALLOW_SUPERUSER=1 COMPOSER_MEMORY_LIMIT=-1 COMPOSER_NO_INTERACTION=1

# This is to make parsing version possible
COPY .git .git

# First copy composer.json only to leverage the build cache
COPY composer.json composer.json
RUN composer install --no-dev --no-progress --no-autoloader --prefer-dist

# Add source and generate full autoloader
COPY . .
RUN composer dump-autoload --optimize --classmap-authoritative --no-dev

FROM build as build-scoped

RUN wget https://github.com/humbug/php-scoper/releases/download/0.14.0/php-scoper.phar -N --no-verbose

# Run php-scoper, results go to /scoped
RUN rm -f "phpstan-for-rector.neon" \
    && php -d memory_limit=-1 php-scoper.phar add-prefix bin config packages rules src templates vendor composer.json --output-dir /scoped --config scoper.php \
    && composer dump-autoload --optimize --classmap-authoritative --no-dev --working-dir /scoped

FROM base as runtime

COPY --from=build /usr/local/lib/php /usr/local/lib/php
COPY --from=build /usr/local/etc/php /usr/local/etc/php
COPY .docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

ENV PATH /rector/bin:$PATH

ENTRYPOINT [ "rector" ]

VOLUME ["/project"]
WORKDIR "/project"

FROM runtime as rector

COPY --from=build /build /rector
RUN chmod +x /rector/bin/rector

RUN mkdir -p /tmp/opcache \
    && /rector/bin/rector list > /dev/null \
    && chmod 777 -R /tmp

FROM runtime as rector-scoped

COPY --from=build-scoped /scoped /rector
RUN chmod +x /rector/bin/rector

RUN mkdir -p /tmp/opcache \
    && /rector/bin/rector list > /dev/null \
    && chmod 777 -R /tmp

## Used for getrector.org/demo
FROM rector as rector-secured

COPY .docker/php/security.ini /usr/local/etc/php/conf.d/security.ini

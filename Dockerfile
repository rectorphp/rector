FROM php:7.4-cli as rector
WORKDIR /rector

# Install php extensions
RUN apt-get update && apt-get install -y \
        git \
        unzip \
        g++ \
        libzip-dev \
        libicu-dev \
    && rm -rf /var/lib/apt/lists/* \
    && pecl -q install \
        zip \
    && docker-php-ext-configure \
        opcache --enable-opcache \
    && docker-php-ext-enable \
        zip \
        opcache \
    && docker-php-ext-configure intl \
    && docker-php-ext-install intl

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

ENV COMPOSER_ALLOW_SUPERUSER=1 COMPOSER_MEMORY_LIMIT=-1

# Copy configuration
COPY .docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

COPY composer.json composer.json
COPY stubs stubs

# This is to make parsing version possible
COPY .git .git

RUN composer install --no-dev --optimize-autoloader --prefer-dist \
    && composer clear-cache

RUN mkdir /tmp/opcache

COPY . .

# To warmup opcache a little
RUN bin/rector list

RUN chmod 777 -R /tmp

ENTRYPOINT [ "rector" ]

ENV PATH /rector/bin:$PATH

VOLUME ["/project"]
WORKDIR "/project"

## Used for getrector.org/demo
FROM rector as rector-secured

COPY .docker/php/security.ini /usr/local/etc/php/conf.d/security.ini

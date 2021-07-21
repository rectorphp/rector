ARG PHP_VERSION=8.0
FROM php:${PHP_VERSION}-cli as base

RUN apt-get update && apt-get install -y \
    libzip4 \
    libicu63 \
    && rm -rf /var/lib/apt/lists/*

FROM base as build

WORKDIR /rector

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

# Add source
COPY . .

# Build runtime image
FROM base as rector

COPY --from=build /usr/local/lib/php /usr/local/lib/php
COPY --from=build /usr/local/etc/php /usr/local/etc/php
COPY .docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

ENV PATH /rector/bin:$PATH

# Add source
COPY . /rector

ENTRYPOINT [ "rector" ]

VOLUME ["/project"]
WORKDIR "/project"

RUN mkdir -p /tmp/opcache

RUN chmod +x /rector/bin/rector
RUN /rector/bin/rector list

RUN mkdir -p /tmp/opcache \
    && /rector/bin/rector list \
    && chmod 777 -R /tmp

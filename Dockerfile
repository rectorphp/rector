FROM php:8-cli-alpine

WORKDIR /etc/rector

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN mkdir -p /etc/rector

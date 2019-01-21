FROM composer:latest AS composer

ENV COMPOSER_ALLOW_SUPERUSER 1

COPY ./composer.json /app

RUN composer install --no-dev


FROM php:7.1-cli

WORKDIR /rector

## Copy rector's vendor to /rector folder
COPY . /rector
COPY --from=composer /app .

ENTRYPOINT ["bin/rector"]

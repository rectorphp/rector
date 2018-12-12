FROM composer:latest AS composer

COPY ./composer.json /app

RUN composer install --no-dev


FROM php:7.1-cli

WORKDIR /rector

COPY . /rector
COPY --from=composer /app .

CMD ["bin/rector", "process", "/project", "--dry-run"]

# TODO: dev with xdebug extension for local development
FROM composer:1.8 AS composer

COPY composer.json composer.json

RUN composer global require hirak/prestissimo && \
  composer install --prefer-dist --no-scripts --no-dev --no-autoloader && \
  rm -rf /root/.composer

FROM php:7.1-cli-alpine

COPY --from=composer /app .

WORKDIR /rector

COPY . /rector

ENTRYPOINT [ "bin/rector" ]

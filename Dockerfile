FROM composer:1.8 AS composer

RUN composer global require hirak/prestissimo

COPY composer.json composer.json
COPY stubs stubs
RUN  composer install --no-dev --optimize-autoloader --prefer-dist


FROM php:7.4-cli
WORKDIR /rector

RUN groupadd -g 1000 rector
RUN useradd -u 1000 -ms /bin/bash -g rector rector

COPY . /rector
COPY --from=composer /app /rector

COPY --chown=rector:rector . /rector

USER rector

ENTRYPOINT [ "bin/rector" ]

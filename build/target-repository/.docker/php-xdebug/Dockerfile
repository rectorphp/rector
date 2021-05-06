################################################
##   Docker image used for debugging Rector   ##
################################################

ARG PHP_VERSION=8.0

FROM rector/rector:php${PHP_VERSION}

RUN pecl install xdebug

COPY .docker/php-xdebug/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

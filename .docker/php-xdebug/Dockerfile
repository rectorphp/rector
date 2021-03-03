################################################
##   Docker image used for debugging Rector   ##
################################################

ARG RECTOR_IMAGE="rector"

FROM rector/${RECTOR_IMAGE}:latest

RUN pecl install xdebug

COPY .docker/php-xdebug/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

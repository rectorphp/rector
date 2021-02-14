################################################
##   Docker image used for debugging Rector   ##
################################################

FROM rector/rector:latest

RUN pecl install xdebug

COPY .docker/php-xdebug/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

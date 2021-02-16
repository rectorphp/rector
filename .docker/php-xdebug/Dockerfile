################################################
##   Docker image used for debugging Rector   ##
################################################

ARG RECTOR_IMAGE="rector"

FROM rector/${RECTOR_IMAGE}:latest

# To overcome open_basedir restrictions for secured version
RUN [ ! -f "/usr/local/etc/php/conf.d/security.ini" ] || mv /usr/local/etc/php/conf.d/security.ini /tmp/security.ini

RUN pecl install xdebug

COPY .docker/php-xdebug/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

# We are done, put it back to original place
RUN [ ! -f "/tmp/security.ini" ] || mv /tmp/security.ini /usr/local/etc/php/conf.d/security.ini

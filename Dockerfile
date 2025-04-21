ARG PHP_VERSION=7.4

FROM php:${PHP_VERSION}-cli-alpine AS php_build

COPY --from=composer:2.0 /usr/bin/composer /usr/bin/composer

WORKDIR /arkitect

COPY bin-stub ./bin-stub
COPY src ./src
COPY composer.json ./composer.json
COPY box.json ./box.json
COPY phpunit.xml ./phpunit.xml
COPY psalm.xml ./psalm.xml

RUN  composer install --no-dev --optimize-autoloader --prefer-dist

RUN apk add zip git bash make icu-dev

# install xdebug
RUN apk --update add --no-cache --virtual .build-deps linux-headers $PHPIZE_DEPS \
    && pecl install xdebug-3.1.5 \
    && docker-php-ext-enable xdebug \
    && pecl clear-cache \
    && apk del .build-deps \
    && rm -rf /tmp/pear \
    && rm -rf /var/cache/apk/* \
    && echo "zend_extension=xdebug.so" > /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.mode=debug" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.start_with_request=yes" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.client_host=host.docker.internal" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.client_port=9003" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini;

ENV PATH="/arkitect/bin-stub:${PATH}"

ENTRYPOINT [ "phparkitect"]

FROM php:cli-alpine

RUN apk --update add curl \
                $PHPIZE_DEPS
RUN pecl install xdebug-3.0.4 \
    && docker-php-ext-enable xdebug

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer

RUN mkdir -p /data/app

WORKDIR /data/app

COPY . /data/app

VOLUME /data/app

CMD ["/bin/sh"]

ENTRYPOINT ["/bin/sh", "-c"]


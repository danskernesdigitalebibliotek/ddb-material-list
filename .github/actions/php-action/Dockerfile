FROM php:7.2-alpine

RUN apk add --no-cache --virtual .build-deps \
        g++ make autoconf bash && \
        pecl install xdebug && \
        docker-php-ext-enable xdebug && \
        apk del .build-deps

ENTRYPOINT ["php"]
CMD ["-v"]

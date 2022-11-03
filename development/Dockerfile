FROM danskernesdigitalebibliotek/php7.4-fpm-base

RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && pecl install xdebug-3.0.0\
    && docker-php-ext-enable xdebug \
    && apk del -f .build-deps


# Install node and npm
RUN apk add --update nodejs npm

# Install dredd and speccy API specification testing tools.
RUN npm install --global dredd@12 speccy

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

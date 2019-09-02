FROM php:7.2-fpm-alpine

# Use the default production configuration
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

RUN apk add --update --no-cache --no-progress \
    fcgi

RUN docker-php-ext-install -j$(nproc) mysqli opcache pcntl pdo_mysql

# Add our own PHP settings
COPY ./config/10-logging.ini $PHP_INI_DIR/conf.d/
COPY ./config/90-materiallist.ini $PHP_INI_DIR/conf.d/
# Add our own FPM settings
COPY ./config/zz-access-log.conf /usr/local/etc/php-fpm.d/
COPY ./config/zz-pm.conf /usr/local/etc/php-fpm.d/

# Add composer in from the official composer image (also alpine).
COPY --from=composer:1.8 /usr/bin/composer /usr/bin/composer

# Install POSIX-compliant wait-for-command application
# See https://github.com/ettore26/wait-for-command
ADD \
    https://raw.githubusercontent.com/ettore26/wait-for-command/master/wait-for-command.sh /usr/local/bin/wait-for-command
RUN \
    chmod +x /usr/local/bin/wait-for-command

# Install php-fpm-healtcheck script.
# See https://github.com/renatomefi/php-fpm-healthcheck
RUN set -eux \
    && curl -o /usr/local/bin/php-fpm-healthcheck https://raw.githubusercontent.com/renatomefi/php-fpm-healthcheck/master/php-fpm-healthcheck \
    && chmod +x /usr/local/bin/php-fpm-healthcheck

RUN mkdir -p /var/www

WORKDIR /var/www

EXPOSE 9000

CMD ["php-fpm"]

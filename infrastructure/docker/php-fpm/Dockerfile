FROM php:7.3-fpm-alpine

ARG COMPOSER_VER=1.8.6

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

# Install composer using the latest installer.
# We compare the downloaded installer with a hash from a second source to reduce
# the risk of downloading a compromised installer.
RUN set -eux \
    && curl -o /tmp/composer-installer https://getcomposer.org/installer \
    && curl -o /tmp/composer-installer.sig https://composer.github.io/installer.sig \
    && php -r "if (hash('SHA384', file_get_contents('/tmp/composer-installer')) !== trim(file_get_contents('/tmp/composer-installer.sig'))) { unlink('/tmp/composer-installer'); echo 'Invalid installer' . PHP_EOL; exit(1); }" \
    && php /tmp/composer-installer --version=$COMPOSER_VER --filename=composer --install-dir=/usr/local/bin \
    && php -r "unlink('/tmp/composer-installer');" \
    && php -r "unlink('/tmp/composer-installer.sig');"

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

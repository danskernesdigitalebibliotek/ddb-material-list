# Dir to put release into in target image.
ARG TARGET_DIR=/var/www/web
ARG BUILDER_IMAGE

# -----------------------------------------------------------------------------
# Build a release using the FPM image and add required build tools on top
# of it.
FROM ${BUILDER_IMAGE} as builder

# Composer version to build with.
ARG COMPOSER_VER=1.8.5
# These argumentes must be passed in at build time.
ARG REVISION
ARG GIT_REPOSITORY

# Install requirements.
RUN apk add --no-cache \
    ca-certificates git curl patch

# Install composer using the latest installer.
# We compare the downloaded installer with a hash from a second source to reduce
# the risk of downloading a compromised installer.
RUN curl -o /tmp/composer-installer https://getcomposer.org/installer && \
  curl -o /tmp/composer-installer.sig https://composer.github.io/installer.sig &&  \
  /usr/local/bin/php -r "if (hash('SHA384', file_get_contents('/tmp/composer-installer')) !== trim(file_get_contents('/tmp/composer-installer.sig'))) { unlink('/tmp/composer-installer'); echo 'Invalid installer' . PHP_EOL; exit(1); }" && \
  /usr/local/bin/php /tmp/composer-installer --version=$COMPOSER_VER --filename=composer --install-dir=/usr/local/bin && \
  /usr/local/bin/php -r "unlink('/tmp/composer-installer');" && \
  /usr/local/bin/php -r "unlink('/tmp/composer-installer.sig');"

RUN composer global require hirak/prestissimo

# Clone source from Github, fetch minimal history.
RUN git clone --depth=1 --branch=$REVISION $GIT_REPOSITORY /var/www/release/
RUN rm -rf /var/www/release/.git

WORKDIR /var/www/release

RUN cd /var/www/release && \
    composer install --no-ansi --no-interaction --no-progress --no-suggest --prefer-dist --optimize-autoloader

# Set release info.
RUN echo "Build time: $(date -u)" > /var/www/release/.release && \
    echo "Repository: ${GIT_REPOSITORY}" >> /var/www/release/.release && \
    echo "Revision: ${REVISION}" >> /var/www/release/.release

# -----------------------------------------------------------------------------
# Copy to separate image.
FROM alpine:3.9

# These arguments are inherited from above.
ARG TARGET_DIR

RUN rm -rf $TARGET_DIR
RUN mkdir -p $TARGET_DIR
# Copy build directory into release dir.
COPY --chown=root:root --from=builder /var/www/release $TARGET_DIR

# Copy tools
COPY tools/init-copy-source.sh /opt/
RUN chmod +x /opt/init-copy-source.sh

WORKDIR $TARGET_DIR

CMD ["/bin/true"]

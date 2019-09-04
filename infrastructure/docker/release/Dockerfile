# Dir to put release into in target image.
ARG TARGET_DIR=/var/www/web

# -----------------------------------------------------------------------------
# Build a release using the offical composer image.
FROM composer:1.8 as builder

RUN composer global require hirak/prestissimo

# Clone source from Github, fetch minimal history.
COPY . /var/www/release/
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
COPY infrastructure/docker/release/tools/init-copy-source.sh /opt/
RUN chmod +x /opt/init-copy-source.sh

WORKDIR $TARGET_DIR

CMD ["/bin/true"]

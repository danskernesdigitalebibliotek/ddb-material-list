FROM uselagoon/php-8.2-cli:latest AS builder

# Copy composer files
COPY composer.* /app/

# Copy all files
COPY . /app

# Install composer dependencies
RUN composer install --no-dev --prefer-dist

FROM uselagoon/php-8.2-cli:latest
COPY --from=builder /app /app

ENV WEBROOT=public

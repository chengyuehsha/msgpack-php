FROM composer:2 AS builder

COPY . /app

WORKDIR /app

RUN composer install --no-dev

# Main stage
FROM php:8.1-cli-alpine

COPY . /app

WORKDIR /app

COPY --from=builder /app/vendor /app/vendor

ENTRYPOINT ["php", "packer.php"]

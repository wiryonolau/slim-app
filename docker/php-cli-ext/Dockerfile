ARG PHP_VERSION=7.3
FROM php:$PHP_VERSION-cli

# Compile dependency
RUN apt-get update \
    && apt-get install -y libicu-dev

# Enable PHP Extension
RUN docker-php-ext-install intl \
    && apt-get purge -y libicu-dev g++ \
    && apt-get -y --purge autoremove \
    && apt-get -y clean \
    && rm -rf /var/lib/apt/list/* /tmp/* /var/tmp/*

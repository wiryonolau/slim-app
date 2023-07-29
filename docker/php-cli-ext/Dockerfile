ARG PHP_VERSION=8.1
FROM php:$PHP_VERSION-cli

ARG IGBINARY_VERSION=3.2.13
ARG MEMCACHED_VERSION=3.2.0

# Compile dependency
RUN apt-get update \
    && apt-get install -y \
       unzip libpng-dev libonig-dev libjpeg-dev libc-client-dev \
       libkrb5-dev zlib1g-dev libicu-dev g++ libcurl4-gnutls-dev \
       libxml2-dev libsasl2-dev libldap2-dev libmemcached-dev libmcrypt-dev \
       libzip-dev cron procps inetutils-ping inetutils-telnet 

# Igbinary
RUN curl -L -o /tmp/igbinary.tar.gz "https://github.com/igbinary/igbinary/archive/refs/tags/${IGBINARY_VERSION}.tar.gz" \
    && mkdir -p /tmp/igbinary \
    && tar -C /tmp/igbinary -zxvf /tmp/igbinary.tar.gz --strip 1 \
    && ( \
        cd /tmp/igbinary \
        && phpize \
        && ./configure CFLAGS="-O2 -g" --enable-igbinary \
        && make -j$(nproc) \
        && make install \
    ) \
    && rm -r /tmp/igbinary  \
    && rm /tmp/igbinary.tar.gz

# Memcached client dependency
RUN curl -L -o /tmp/memcached.tar.gz "https://github.com/php-memcached-dev/php-memcached/archive/refs/tags/v${MEMCACHED_VERSION}.tar.gz" \
    && mkdir -p /tmp/memcached \
    && tar -C /tmp/memcached -zxvf /tmp/memcached.tar.gz --strip 1 \
    && ( \
        cd /tmp/memcached \
        && phpize \
        && ./configure \
           --enable-memcached-igbinary \
           --enable-memcached-session \
           --enable-memcached-json \
           --enable-memcached-sasl \
        && make -j$(nproc) \
        && make install \
    ) \
    && rm -r /tmp/memcached  \
    && rm /tmp/memcached.tar.gz

# Enable PHP Extension
# Some extension might return error if already installed 
RUN docker-php-ext-configure imap --with-kerberos --with-imap-ssl \
    && docker-php-ext-configure ldap \
    && docker-php-ext-install imap \
    && docker-php-ext-install mysqli pdo pdo_mysql \
    && docker-php-ext-install gd \
    && docker-php-ext-install opcache \
    && docker-php-ext-install mbstring \
    && docker-php-ext-install bcmath \
    && php -m | grep json || docker-php-ext-install json \
    && php -m | grep xml || docker-php-ext-install xml xmlrpc \
    && docker-php-ext-install intl \
    && php -m | grep curl || docker-php-ext-install curl \
    && php -m | grep zip || docker-php-ext-install zip \
    && docker-php-ext-install ldap \
    && php -m | grep iconv || docker-php-ext-install iconv \
    && docker-php-ext-install soap \
    && docker-php-ext-install sockets \
    && docker-php-ext-enable igbinary \
    && docker-php-ext-enable memcached \
    && apt-get purge -y libc-client-dev libkrb5-dev libicu-dev g++ libcurl4-gnutls-dev libxml2-dev libldap2-dev \
    && apt-get -y --purge autoremove \
    && apt-get -y clean \
    && rm -rf /var/lib/apt/list/* /tmp/* /var/tmp/*

# Enable docker logs by passing output to /dev/pts/0 when tty is used
RUN usermod -a -G tty www-data

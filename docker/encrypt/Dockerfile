FROM php:7.1-apache-jessie

MAINTAINER Dev2Choiz

WORKDIR /var/www/html/encrypt

ENV DEBIAN_FRONTEND noninteractive

RUN useradd -ms /bin/bash encrypt
ENV DEV_REMOTE_HOST 192.168.0.15

ENV APACHE_RUN_USER encrypt
ENV APACHE_RUN_GROUP encrypt
ENV APACHE_DOCUMENTROOT /var/www

RUN apt-get update \
    && buildDeps=" \
        libz-dev \
        git \
        libmemcached-dev \
        libsqlite3-dev \
    " \
    && apt-get install -y $buildDeps --no-install-recommends \
    && git config --global core.autocrlf input \
    \
    && docker-php-source extract \
        && git clone --branch php7 https://github.com/php-memcached-dev/php-memcached /usr/src/php/ext/memcached/ \
        && docker-php-ext-configure memcached \
        && docker-php-ext-install -j$(nproc) memcached mysqli pdo pdo_mysql pdo_sqlite zip bcmath sockets \
    && docker-php-source delete \
    \
    && apt-mark manual libmemcached11 libmemcachedutil2 \
    && apt-get autoremove --purge -y $buildDeps \
    && rm -r /var/lib/apt/lists/*

RUN apt-get update \
    && apt-get install -y curl supervisor \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && rm -rf /var/lib/apt/lists/*

RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

COPY ./docker/encrypt/php.ini /usr/local/etc/php/php.ini
COPY ./docker/encrypt/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini
COPY ./docker/encrypt/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

CMD ["/usr/bin/supervisord"]

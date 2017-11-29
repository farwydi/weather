FROM php:7.1-fpm

MAINTAINER DEX

RUN apt-get update \
    && apt-get install -y libpq-dev zlib1g-dev git \
    && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-install pdo pdo_pgsql \
    && docker-php-ext-install zip

RUN mkdir /artifact
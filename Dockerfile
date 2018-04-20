FROM php:7.2-apache

RUN curl -sS http://getcomposer.org/installer | php -- --install-dir=/usr/bin/ --filename=composer
RUN apt-get update
RUN apt-get install -y git
RUN pecl install mongodb && \
    docker-php-ext-enable mongodb

WORKDIR /var/app
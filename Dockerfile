FROM php:8.0-zts

ENV DEBIAN_FRONTEND=noninteractive

RUN apt update -yq && \
    apt upgrade -yq && \
    apt install -yq git

RUN docker-php-ext-install pcntl
RUN pecl install xdebug && docker-php-ext-enable xdebug

RUN git clone https://github.com/krakjoe/parallel.git && \
    cd parallel && \
    phpize && \
    ./configure --enable-parallel && \
    make && make install && \
    echo "extension=parallel.so" > /usr/local/etc/php/conf.d/php-ext-parallel.ini

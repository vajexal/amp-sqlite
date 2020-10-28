FROM php:7.4-zts

RUN docker-php-ext-install pcntl
RUN pecl install parallel xdebug && docker-php-ext-enable parallel xdebug

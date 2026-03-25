FROM php:8.5-cli
WORKDIR /usr/src/myapp

RUN apt-get update && apt-get install -y \
        curl \
        git \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libmagickwand-dev \
        libpng-dev \
        unzip \
        zip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd \
    && pecl install imagick xdebug \
    && docker-php-ext-enable imagick xdebug \
    && apt-get clean && rm -rf /var/lib/apt/lists/* \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer --2.7 \
    && mkdir /.composer && chmod 777 /.composer

USER nobody

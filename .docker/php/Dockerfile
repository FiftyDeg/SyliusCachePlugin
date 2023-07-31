#########################################
# PHP ###################################
#########################################
FROM php:8.1.12-fpm-bullseye AS php-fpm

RUN apt-get update && apt-get install -y \
    cron \
    file \
    git \
    gzip \
    libbz2-dev \
    libfreetype6-dev \
    libgmp3-dev \
    libicu-dev \
    libjpeg62-turbo-dev \
    libmcrypt-dev \
    libmhash-dev \
    libonig-dev \
    libpng-dev \
    libwebp-dev \
    libsodium-dev \
    libssh2-1-dev \
    libxslt1-dev \
    libzip-dev \
    lsof \
    default-mysql-client \
    re2c \
    vim \
    zip \
    procps \
    wget \
    jpegoptim \
    pngquant \
    optipng \
    imagemagick

# Install mozjpeg
RUN apt-get install -y \
    build-essential libboost-all-dev pkg-config \
    autoconf automake libtool nasm make cmake flex \
    libpng-tools libpng-dev zlib1g-dev \
    && git clone https://github.com/mozilla/mozjpeg.git \
    && cd mozjpeg \
    && cmake . \
    && make \
    && cd .. \
    && rm -rf mozjpeg

RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp
RUN docker-php-ext-configure gmp

RUN docker-php-ext-install \
    bcmath \
    bz2 \
    calendar \
    exif \
    gd \
    gettext \
    gmp \
    intl \
    mbstring \
    mysqli \
    opcache \
    pcntl \
    pdo_mysql \
    soap \
    sockets \
    sodium \
    sysvmsg \
    sysvsem \
    sysvshm \
    xsl \
    zip

RUN docker-php-ext-install gmp \
    && docker-php-ext-enable gmp

RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

RUN apt-get update && apt-get -y --no-install-recommends install git && \
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin/ --filename=composer && \
    rm -rf /var/lib/apt/lists/*

RUN apt-get update \
    && apt-get -y --no-install-recommends install \
    fontconfig \
    libxrender1 \
    xfonts-base \
    xfonts-75dpi

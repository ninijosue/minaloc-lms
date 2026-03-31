FROM php:8.1-apache

# Install system dependencies required for PHP extensions
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zlib1g-dev \
    libxml2-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libicu-dev \
    libonig-dev \
    && rm -rf /var/lib/apt/lists/*

# Configure GD extension with JPEG and FreeType support
RUN docker-php-ext-configure gd --with-freetype --with-jpeg

# Install PHP extensions
RUN docker-php-ext-install -j$(nproc) \
    mysqli \
    zip \
    soap \
    intl \
    gd \
    && docker-php-ext-enable mysqli zip soap intl gd

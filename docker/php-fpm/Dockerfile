FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libzip-dev \
    unzip \
    libonig-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libwebp-dev \
    libxpm-dev \
    libavif-dev \
    ffmpeg \
    poppler-utils \
    && docker-php-ext-install pdo_mysql zip

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Configure and install GD extension with necessary libraries
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp --with-xpm --with-avif \
    && docker-php-ext-install gd

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html/mail_system

RUN chown -R www-data:www-data /var/www

RUN composer global require "laravel/installer"

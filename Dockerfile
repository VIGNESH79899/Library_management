FROM php:8.2-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    zip \
    unzip \
    git \
    libzip-dev

# Install mysqli
RUN docker-php-ext-install mysqli

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy project files
COPY . .

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Set permissions
RUN chown -R www-data:www-data /var/www/html
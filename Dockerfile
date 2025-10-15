# Use PHP 8.3 with FPM
FROM php:8.3-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    nginx \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    libonig-dev \
    zip \
    unzip \
    git \
    curl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    gd \
    mysqli \
    pdo \
    pdo_mysql \
    mbstring \
    zip \
    exif \
    pcntl \
    bcmath

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /app

# Copy application files
COPY . /app

# Install composer dependencies for backup module
RUN if [ -f /app/modules/backup/composer.json ]; then \
    cd /app/modules/backup && composer install --no-dev --optimize-autoloader; \
    fi

# Copy nginx configuration
COPY nginx-site.conf /etc/nginx/sites-available/default

# Set permissions
RUN chown -R www-data:www-data /app \
    && chmod -R 755 /app \
    && chmod -R 777 /app/uploads \
    && chmod -R 777 /app/temp \
    && chmod -R 777 /app/backups \
    && chmod -R 777 /app/logs

# Create startup script
RUN echo '#!/bin/bash\n\
php-fpm -D\n\
nginx -g "daemon off;"' > /start.sh && chmod +x /start.sh

EXPOSE 80

CMD ["/start.sh"]
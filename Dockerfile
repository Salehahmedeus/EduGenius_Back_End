FROM php:8.2-apache

# 1. Install Linux Libraries + SUPERVISOR (Added supervisor here)
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    unzip \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    git \
    curl \
    supervisor 

# 2. Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# 3. Install PHP Extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# 4. Enable Apache Rewrite
RUN a2enmod rewrite

# 5. Set Document Root
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf.0

# 6. Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 7. Set Working Directory
WORKDIR /var/www/html

# 8. Copy Project Files
COPY . .

# 9. Install PHP Dependencies
RUN composer install --no-interaction --optimize-autoloader --no-dev

# 10. Set Permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# 11. Copy Supervisor Config (NEW STEP)
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# 12. Expose Port
EXPOSE 80

# 13. Start Supervisor (CHANGED FROM APACHE)
CMD ["/usr/bin/supervisord"]
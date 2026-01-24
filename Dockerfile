# 👇 CHANGE THIS LINE from 8.2 to 8.4
FROM php:8.4-apache

# 2. Install Linux Libraries + SUPERVISOR
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

# 3. Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# 4. Install PHP Extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# 5. Enable Apache Rewrite Module
RUN a2enmod rewrite

# 6. Set the Document Root to /public
ENV APACHE_DOCUMENT_ROOT /var/www/html/public

# (This is the fix from the previous error)
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# 7. Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 8. Set Working Directory
WORKDIR /var/www/html

# 9. Copy Project Files
COPY . .

# 10. Install PHP Dependencies
RUN composer install --no-interaction --optimize-autoloader --no-dev

# 11. Set Permissions (UPDATED)
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
RUN chmod -R 777 /var/www/html/storage /var/www/html/bootstrap/cache

# 12. Copy Supervisor Config
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# 13. Expose Port
EXPOSE 80

# 14. Start Supervisor
CMD ["/usr/bin/supervisord"]
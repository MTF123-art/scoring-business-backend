FROM php:8.2-apache

# Enable Apache modules
RUN a2enmod rewrite headers

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    nodejs \
    npm \
    cron \
    supervisor

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configure Apache DocumentRoot
ENV APACHE_DOCUMENT_ROOT /var/www/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Set working directory
WORKDIR /var/www

# Copy application files
COPY . /var/www

# Copy the cron wrapper script and make it executable
COPY run-cron.sh /usr/local/bin/run-cron.sh
RUN chmod +x /usr/local/bin/run-cron.sh

# Install dependencies
RUN composer install --no-interaction --no-dev --optimize-autoloader
RUN npm install
RUN npm run build

# Generate application key
# RUN php artisan key:generate

# Set permissions
RUN chown -R www-data:www-data /var/www
RUN chmod -R 755 /var/www/storage

# Copy Supervisor and Cron configuration files
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY laravel-cron /etc/cron.d/laravel-cron

# Give execution rights to the cron job and create log file
RUN chmod 0644 /etc/cron.d/laravel-cron
# RUN touch /var/log/cron.log && chmod 0644 /var/log/cron.log

# Expose port 80
EXPOSE 80

# Start Supervisor
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
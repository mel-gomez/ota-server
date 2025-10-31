FROM php:8.3-fpm

# Install system dependencies including Nginx
RUN apt-get update && apt-get install -y \
    nginx \
    libpng-dev libjpeg-dev libfreetype6-dev zip git unzip curl libonig-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql mbstring exif pcntl bcmath

# Install Composer
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy existing app
COPY . .

# Copy Nginx configuration (using hardcoded port 8080)
COPY nginx.conf /etc/nginx/sites-available/default
RUN ln -sf /etc/nginx/sites-available/default /etc/nginx/sites-enabled/

# Install Laravel dependencies
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Fix permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Generate key if not exists
RUN php artisan key:generate || true

# Start both Nginx and PHP-FPM
CMD sh -c "php-fpm -D && nginx -g 'daemon off;'"

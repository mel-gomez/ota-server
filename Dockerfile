FROM php:8.3-fpm

# Install system dependencies including Nginx and envsubst
RUN apt-get update && apt-get install -y \
    nginx \
    gettext-base \
    libpng-dev libjpeg-dev libfreetype6-dev zip git unzip curl libonig-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql mbstring exif pcntl bcmath

# Install Composer
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy existing app
COPY . .

# Copy Nginx configuration template
COPY nginx.conf.template /etc/nginx/templates/nginx.conf.template

# Install Laravel dependencies
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Fix permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Generate key if not exists
RUN php artisan key:generate || true

# Start both Nginx and PHP-FPM with environment variable substitution
CMD sh -c "php-fpm -D && envsubst '$$PORT' < /etc/nginx/templates/nginx.conf.template > /etc/nginx/sites-available/default && nginx -g 'daemon off;'"

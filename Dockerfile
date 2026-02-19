FROM php:8.4-fpm-alpine3.21

# Patch all known CVEs in base packages
RUN apk upgrade --no-cache

# Install nginx and required PHP extension dependencies
RUN apk add --no-cache \
    nginx \
    openssl \
    && docker-php-ext-install opcache

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Configure Nginx
RUN rm -f /etc/nginx/http.d/default.conf
COPY docker/nginx.conf /etc/nginx/http.d/default.conf

# Configure PHP-FPM
RUN sed -i 's/^listen = .*/listen = 127.0.0.1:9000/' /usr/local/etc/php-fpm.d/www.conf

# Entrypoint script
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . .

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Copy PHP ini settings
RUN cp .user.ini /usr/local/etc/php/conf.d/99-app.ini

# Remove build-only packages to reduce CVE surface
RUN apk del --no-cache tar

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 775 /var/www/html/fpx

EXPOSE 80

CMD ["/entrypoint.sh"]

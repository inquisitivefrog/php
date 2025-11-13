# Use official PHP-FPM image
FROM php:8.2-fpm

# Install any needed extensions (optional)
# RUN docker-php-ext-install pdo_mysql

# Set working directory
WORKDIR /var/www/html

# Copy PHP app
COPY php/ .

# Expose port (not required for docker-compose)
# EXPOSE 9000

CMD ["php-fpm"]

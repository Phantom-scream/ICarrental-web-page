# Use the official PHP image with Apache
FROM php:8.2-apache

# Enable mod_rewrite if needed (optional, but useful for URL rewriting)
RUN a2enmod rewrite

# Copy project files into the Apache document root
COPY . /var/www/html/

# Set permissions (adjust as needed)
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Expose port 80
EXPOSE 80
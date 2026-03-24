# Use the official PHP image with Apache
FROM php:8.2-apache

# Install mysqli and PDO MySQL extensions
RUN docker-php-ext-install mysqli pdo pdo_mysql && docker-php-ext-enable mysqli pdo_mysql

# Copy application files to the web server root
COPY . /var/www/html/


# Set working directory
WORKDIR /var/www/html

# Expose port 80 for web traffic
EXPOSE 80

# Give Apache permission to read/write files
RUN chown -R www-data:www-data /var/www/html

# Enable Apache mod_rewrite (optional, useful for routing)
RUN a2enmod rewrite

# Start Apache in the foreground
CMD ["apache2-foreground"]

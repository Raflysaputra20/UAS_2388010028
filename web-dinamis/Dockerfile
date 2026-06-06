# Base image using official PHP with Apache
FROM php:8.2-apache

# Install mysqli extension for MySQL/MariaDB connectivity
RUN docker-php-ext-install mysqli pdo pdo_mysql && docker-php-ext-enable mysqli

# Enable Apache rewrite module
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Copy the PHP application source code into the web server directory
COPY . /var/www/html/

# Adjust permissions for Apache
RUN chown -R www-data:www-data /var/www/html

# Expose port 80 for web traffic
EXPOSE 80
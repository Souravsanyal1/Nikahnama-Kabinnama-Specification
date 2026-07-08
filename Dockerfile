# Use the official PHP Apache image
FROM php:8.2-apache

# Set the working directory
WORKDIR /var/www/html

# Copy your application code
COPY . ./

# Update Apache configuration to listen on the $PORT environment variable
# Cloud Run sets the PORT environment variable to 8080 by default
RUN sed -i 's/80/${PORT}/g' /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf

# Ensure the webserver has correct permissions
RUN chown -R www-data:www-data /var/www/html

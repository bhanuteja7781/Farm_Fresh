FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    libpng-dev libjpeg-dev libfreetype6-dev libonig-dev libxml2-dev zip unzip git curl \
    && docker-php-ext-install mysqli pdo pdo_mysql gd \
    && a2enmod rewrite headers \
    && sed -i 's/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

WORKDIR /var/www/html

COPY backend/ /var/www/html/
COPY backend/apache.conf /etc/apache2/sites-available/000-default.conf

EXPOSE 80

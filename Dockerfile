FROM php:8.3-apache

# Install package yang dibutuhkan
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    zip \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libzip-dev

# Install extension PHP
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql zip

# Aktifkan rewrite
RUN a2enmod rewrite

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Folder kerja
WORKDIR /var/www/html

# Copy seluruh project Laravel
COPY . .

# Install dependency Laravel
RUN composer install \
    --no-dev \
    --prefer-dist \
    --optimize-autoloader \
    --no-interaction

# Permission
RUN chown -R www-data:www-data storage bootstrap/cache

# Set document root Apache
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

RUN sed -ri \
-e 's!/var/www/html!/var/www/html/public!g' \
/etc/apache2/sites-available/*.conf

EXPOSE 80

CMD ["apache2-foreground"]
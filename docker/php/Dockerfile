FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    zip \
    unzip \
    libzip-dev \
    libonig-dev \
    libxml2-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    curl \
    ca-certificates \
  && docker-php-ext-configure gd --with-freetype --with-jpeg \
  && docker-php-ext-install pdo_mysql mbstring zip exif pcntl bcmath gd \
  && rm -rf /var/lib/apt/lists/*

# Install composer (from official composer image)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy composer files first to leverage Docker cache
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist || true

# Copy the rest of the application
# Ensure i18n locale JSON files are explicitly copied (avoid missing files in build context)
COPY resources/js/i18n ./resources/js/i18n

# Copy remaining project files
COPY . .

# Fix permissions
RUN chown -R www-data:www-data /var/www/html || true

# Copy entrypoint and make executable
COPY docker/php/entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh || true

EXPOSE 9000
ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
CMD ["php-fpm"]

FROM php:8.3.6-cli-alpine

# Install system dependencies
RUN apk add --no-cache \
    postgresql-dev \
    zip \
    unzip \
    git \
    curl \
    libpng-dev \
    libxml2-dev \
    libzip-dev \
    linux-headers \
    rabbitmq-c-dev

# Install PHP extensions
RUN docker-php-ext-install \
    pdo \
    pdo_pgsql \
    zip \
    xml \
    sockets

# Install AMQP extension
RUN apk add --no-cache $PHPIZE_DEPS \
    && pecl install amqp \
    && docker-php-ext-enable amqp \
    && apk del $PHPIZE_DEPS

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy application code
COPY . .

# Install dependencies
RUN composer install --optimize-autoloader --no-interaction --prefer-dist --no-scripts

# Set proper permissions
RUN chmod +x start.sh

# Expose port
EXPOSE 8000

# Use our automation script
CMD ["./start.sh"]

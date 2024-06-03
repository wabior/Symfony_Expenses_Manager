FROM php:8.2-apache

# Install system dependencies
RUN echo "Updating apt-get and installing system dependencies..." && \
    apt-get update && apt-get install -y \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libonig-dev \
    libzip-dev \
    zip \
    unzip \
    curl

# node
RUN echo "Installing Node.js and npm..." && \
    apt install -y nodejs npm && \
    echo "Cleaning up unnecessary files..." && \
    apt-get clean && rm -rf /var/lib/apt/lists/*

# Configure PHP extensions
RUN echo "Configuring PHP extensions..." && \
    docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install -j$(nproc) gd mysqli pdo pdo_mysql && \
    docker-php-ext-enable pdo_mysql

# Install Composer
RUN echo "Installing Composer..." && \
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Add non-root user
RUN echo "Adding non-root user and setting permissions..." && \
    useradd -u 1000 -m myuser && \
    chown -R myuser:myuser /var/www/html && \
    chmod -R 775 /var/www/html

# Enable Apache mod_rewrite
RUN echo "Enabling Apache mod_rewrite..." && \
    a2enmod rewrite

# Switch to non-root user
USER myuser

# Set working directory
WORKDIR /var/www/html/symfony

# Default command
CMD echo "Starting Apache server..." && apache2-foreground

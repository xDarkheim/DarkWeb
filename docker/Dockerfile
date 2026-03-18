FROM php:8.4-apache

# ── Composer (copy binary from official image — always latest 2.x) ────────────
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

# Install system dependencies and FreeTDS for MS SQL Server connectivity
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    freetds-dev \
    freetds-bin \
    tdsodbc \
    unixodbc \
    unixodbc-dev \
    cron \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Configure FreeTDS
RUN echo "[global]\n\
    tds version = 7.4\n\
    client charset = UTF-8\n\
    text size = 20971520" > /etc/freetds/freetds.conf

# Install PHP extensions
# pdo_dblib: resolve the multiarch lib dir dynamically so the image builds on
# both x86_64 (amd64) and ARM64 (Apple Silicon, Raspberry Pi, etc.)
RUN MULTIARCH="$(dpkg-architecture -qDEB_HOST_MULTIARCH)" \
    && docker-php-ext-install pdo pdo_mysql mysqli mbstring exif pcntl bcmath gd zip \
    && docker-php-ext-configure pdo_dblib --with-libdir="lib/${MULTIARCH}" \
    && docker-php-ext-install pdo_dblib

# ── Xdebug (disabled by default — enable via XDEBUG_MODE env var) ─────────────
RUN pecl install xdebug && docker-php-ext-enable xdebug
COPY docker/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

# Enable Apache modules
RUN a2enmod rewrite headers remoteip

# Configure PHP
RUN cp /usr/local/etc/php/php.ini-production /usr/local/etc/php/php.ini \
    && sed -i 's/upload_max_filesize = 2M/upload_max_filesize = 20M/g' /usr/local/etc/php/php.ini \
    && sed -i 's/post_max_size = 8M/post_max_size = 20M/g' /usr/local/etc/php/php.ini \
    && sed -i 's/memory_limit = 128M/memory_limit = 256M/g' /usr/local/etc/php/php.ini

# OPcache — tuned for PHP 8.4 (JIT enabled, file-stat revalidation on)
RUN { \
    echo "opcache.enable=1"; \
    echo "opcache.enable_cli=0"; \
    echo "opcache.memory_consumption=128"; \
    echo "opcache.interned_strings_buffer=16"; \
    echo "opcache.max_accelerated_files=10000"; \
    echo "opcache.revalidate_freq=0"; \
    echo "opcache.validate_timestamps=1"; \
    echo "opcache.jit_buffer_size=64M"; \
    echo "opcache.jit=tracing"; \
} > /usr/local/etc/php/conf.d/opcache.ini

# Match www-data UID/GID to host user (UID 1000) so mounted volumes are writable
RUN usermod -u 1000 www-data && groupmod -g 1000 www-data

# Set working directory
WORKDIR /var/www/html

# Apache: listen on port 8081 (SSL terminated by upstream reverse proxy)
RUN sed -i 's/Listen 80/Listen 8081/' /etc/apache2/ports.conf

# Configure the default vhost (generic — domain is handled by upstream reverse proxy)
RUN echo '<VirtualHost *:8081>\n\
    ServerName ${DOCKER_SERVER_NAME}\n\
    ServerAdmin webmaster@localhost\n\
    DocumentRoot /var/www/html\n\
    \n\
    # Trust X-Forwarded-For from upstream reverse proxy\n\
    RemoteIPHeader X-Forwarded-For\n\
    RemoteIPInternalProxy 172.16.0.0/12\n\
    \n\
    <Directory /var/www/html>\n\
        Options -Indexes +FollowSymLinks\n\
        AllowOverride All\n\
        Require all granted\n\
    </Directory>\n\
    \n\
    ErrorLog ${APACHE_LOG_DIR}/error.log\n\
    CustomLog ${APACHE_LOG_DIR}/access.log combined\n\
</VirtualHost>' > /etc/apache2/sites-available/000-default.conf \
    && a2ensite 000-default.conf

# Setup cron log
RUN touch /var/log/cron.log && chmod 0666 /var/log/cron.log

# Copy entrypoint script and make executable in one layer
COPY docker/entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Expose internal port only (upstream reverse proxy targets this)
EXPOSE 8081

# Use custom entrypoint
ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]

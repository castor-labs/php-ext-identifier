FROM php:8.2-cli

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    wget \
    build-essential \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install Zig
RUN wget -q https://ziglang.org/download/0.13.0/zig-linux-x86_64-0.13.0.tar.xz \
    && tar -xf zig-linux-x86_64-0.13.0.tar.xz \
    && mv zig-linux-x86_64-0.13.0 /opt/zig \
    && ln -s /opt/zig/zig /usr/local/bin/zig \
    && rm zig-linux-x86_64-0.13.0.tar.xz

# Disable Xdebug and other debugging extensions for accurate benchmarks
RUN rm -f /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

# Configure PHP for optimal performance
RUN echo "opcache.enable_cli=1" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.jit_buffer_size=256M" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.jit=1255" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "memory_limit=1G" >> /usr/local/etc/php/conf.d/memory.ini

# Install optional UUID extension for comparison (if available)
RUN pecl install uuid || true \
    && echo "extension=uuid.so" > /usr/local/etc/php/conf.d/uuid.ini || true

# Set working directory
WORKDIR /app

# Copy project files
COPY . .

# Build the identifier extension
RUN zig build

# Install benchmark dependencies
WORKDIR /app/bench
RUN composer install --no-dev --optimize-autoloader

# Create results directory
RUN mkdir -p results

# Set the working directory back to bench
WORKDIR /app/bench

# Default command
CMD ["php", "-d", "extension=/app/modules/identifier.so", "simple/quick_comparison.php"]

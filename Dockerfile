FROM php:8.3-cli

# Install dependencies yang dibutuhkan Laravel
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    unzip \
    git \
    libpq-dev \
    libsqlite3-dev \
    curl

# Install ekstensi PHP (MySQL, PostgreSQL, SQLite, Zip)
RUN docker-php-ext-install pdo_mysql pdo_pgsql pdo_sqlite zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /app

# Copy seluruh file project
COPY . .

# Install dependency PHP/Laravel
RUN composer install --optimize-autoloader --no-dev

# Buat file database SQLite bawaan jika belum ada (hanya untuk fallback)
RUN touch database/database.sqlite

# Berikan hak akses ke folder storage dan cache
RUN chown -R www-data:www-data /app \
    && chmod -R 777 /app/storage \
    && chmod -R 777 /app/bootstrap/cache \
    && chmod -R 777 /app/database

# Jalankan migrasi dan server saat container di-start
CMD php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=${PORT:-8000}

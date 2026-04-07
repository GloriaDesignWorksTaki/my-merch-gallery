FROM php:8.3-cli

# System packages + PHP extensions required by Laravel/PostgreSQL
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpq-dev \
    libzip-dev \
    zip \
    && docker-php-ext-install pdo pdo_pgsql zip \
    && rm -rf /var/lib/apt/lists/*

# Install Node.js 22 and npm from official Node image
COPY --from=node:22-bookworm-slim /usr/local/bin/node /usr/local/bin/node
COPY --from=node:22-bookworm-slim /usr/local/bin/npm /usr/local/bin/npm
COPY --from=node:22-bookworm-slim /usr/local/bin/npx /usr/local/bin/npx
COPY --from=node:22-bookworm-slim /usr/local/lib/node_modules /usr/local/lib/node_modules

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Copy source first (Laravel composer scripts need app files)
COPY . .

# Install PHP and frontend dependencies, then build assets
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist \
    && npm ci \
    && npm run build

EXPOSE 10000

# NOTE: Run "php artisan migrate --force" via Render Post-Deploy Command.
CMD ["sh", "-c", "php artisan serve --host=0.0.0.0 --port=${PORT:-10000}"]

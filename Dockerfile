FROM php:8.3-cli

# System packages + PHP extensions required by Laravel/PostgreSQL
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    curl \
    ca-certificates \
    gnupg \
    libpq-dev \
    libzip-dev \
    zip \
    && docker-php-ext-install pdo pdo_pgsql zip \
    && rm -rf /var/lib/apt/lists/*

# 画像アップロード（複数枚・multipart）で 8MB 既定を超えると 413 / post size 警告になるため
RUN { \
    echo 'upload_max_filesize = 32M'; \
    echo 'post_max_size = 48M'; \
    echo 'max_file_uploads = 20'; \
    } > /usr/local/etc/php/conf.d/uploads.ini

# 本番レスポンス向上のための OPcache
RUN { \
    echo 'opcache.enable=1'; \
    echo 'opcache.enable_cli=1'; \
    echo 'opcache.memory_consumption=192'; \
    echo 'opcache.interned_strings_buffer=16'; \
    echo 'opcache.max_accelerated_files=20000'; \
    echo 'opcache.validate_timestamps=0'; \
    } > /usr/local/etc/php/conf.d/opcache-prod.ini

# Install Node.js 22 + npm
RUN curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
    && apt-get update \
    && apt-get install -y nodejs \
    && rm -rf /var/lib/apt/lists/*

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

# 起動前にマイグレーション・storage:link・キャッシュ最適化を実行
CMD ["sh", "-c", "php artisan migrate --force && (php artisan storage:link || true) && php artisan config:cache && php artisan view:cache && PHP_CLI_SERVER_WORKERS=${PHP_CLI_SERVER_WORKERS:-4} php artisan serve --host=0.0.0.0 --port=${PORT:-10000}"]
# --- フロント（Vite）: 最終イメージに Node を残さない
FROM node:22-bookworm-slim AS frontend
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY . .
RUN npm run build

# --- 本番: PHP-FPM + nginx + Supervisor（php artisan serve は使わない）
FROM php:8.3-fpm-bookworm

RUN apt-get update && apt-get install -y \
  git \
  unzip \
  curl \
  ca-certificates \
  gnupg \
  libpq-dev \
  libzip-dev \
  zip \
  nginx \
  supervisor \
  gettext-base \
  && docker-php-ext-install pdo pdo_pgsql zip \
  && rm -rf /var/lib/apt/lists/*

RUN { \
  echo 'upload_max_filesize = 32M'; \
  echo 'post_max_size = 48M'; \
  echo 'max_file_uploads = 20'; \
  } > /usr/local/etc/php/conf.d/uploads.ini

RUN { \
  echo 'opcache.enable=1'; \
  echo 'opcache.enable_cli=0'; \
  echo 'opcache.memory_consumption=192'; \
  echo 'opcache.interned_strings_buffer=16'; \
  echo 'opcache.max_accelerated_files=20000'; \
  echo 'opcache.validate_timestamps=0'; \
  } > /usr/local/etc/php/conf.d/opcache-prod.ini

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist --no-scripts

COPY . .
COPY --from=frontend /app/public/build ./public/build

RUN composer dump-autoload --optimize --no-interaction \
  && chown -R www-data:www-data /app/storage /app/bootstrap/cache \
  && chmod -R ug+rwx /app/storage /app/bootstrap/cache \
  && mkdir -p /etc/nginx/templates \
  && cp /app/docker/nginx/site.conf.envsubst /etc/nginx/templates/site.conf.envsubst \
  && cp /app/docker/supervisor/laravel.conf /etc/supervisor/conf.d/laravel.conf \
  && cp /app/docker/entrypoint.sh /entrypoint.sh \
  && chmod +x /entrypoint.sh \
  && rm -f /etc/nginx/sites-enabled/default /etc/nginx/sites-available/default /etc/nginx/conf.d/default.conf 2>/dev/null || true

EXPOSE 10000

ENTRYPOINT ["/entrypoint.sh"]

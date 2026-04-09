#!/bin/sh
set -e

PORT="${PORT:-10000}"
export PORT

envsubst '${PORT}' < /etc/nginx/templates/site.conf.envsubst > /etc/nginx/conf.d/default.conf

cd /app

# public/storage は root でリンク（www-data では public に書けないことがある）
php artisan storage:link 2>/dev/null || true

# キャッシュは実行時の環境変数を反映するためコンテナ起動時に生成（www-data で書き込み）
su www-data -s /bin/sh -c "php artisan optimize:clear"
su www-data -s /bin/sh -c "php artisan config:cache"
su www-data -s /bin/sh -c "php artisan route:cache"
su www-data -s /bin/sh -c "php artisan view:cache"

exec /usr/bin/supervisord -n -c /etc/supervisor/supervisord.conf

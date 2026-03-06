#!/usr/bin/env bash
set -euo pipefail

cd /var/www/html

if [[ ! -f .env ]]; then
  cp .env.example .env
fi

if [[ -z "${APP_KEY:-}" ]]; then
  php artisan key:generate --force
fi

php artisan storage:link || true
php artisan config:cache
php artisan route:cache
php artisan view:cache

if [[ "${RUN_MIGRATIONS:-false}" == "true" ]]; then
  php artisan migrate --force
fi

exec "$@"

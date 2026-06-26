#!/bin/sh
set -e

# Render (и любой Docker-хост) прокидывает переменные в окружение контейнера,
# но Laravel по умолчанию читает конфиг из .env. Поэтому при старте переносим
# значения из окружения в .env, чтобы APP_KEY, APP_DEBUG и прочее точно применились.

: "${APP_ENV:=production}"
: "${APP_DEBUG:=false}"
: "${APP_URL:=http://localhost}"

set_env() {
  key="$1"; value="$2"
  if grep -q "^${key}=" .env; then
    # | как разделитель: в base64-ключе встречаются / и +, но не |
    sed -i "s|^${key}=.*|${key}=${value}|" .env
  else
    echo "${key}=${value}" >> .env
  fi
}

# APP_KEY: берём из окружения, если задан; иначе генерируем разовый.
if [ -n "$APP_KEY" ]; then
  set_env "APP_KEY" "$APP_KEY"
else
  php artisan key:generate --force
fi

set_env "APP_ENV" "$APP_ENV"
set_env "APP_DEBUG" "$APP_DEBUG"
set_env "APP_URL" "$APP_URL"

php artisan config:clear

# База на free-плане эфемерна. migrate:fresh гарантирует чистые данные при любом старте
# (пересоздаёт таблицы и наполняет заново, без задвоения при тёплом рестарте).
php artisan migrate:fresh --force --seed

exec php artisan serve --host=0.0.0.0 --port="${PORT:-8000}"

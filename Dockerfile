# --- Сборка фронтенда ---
FROM node:22-alpine AS assets
WORKDIR /app
COPY package*.json vite.config.js ./
RUN npm ci
COPY resources ./resources
RUN npm run build

# --- Приложение ---
FROM php:8.3-cli-alpine
RUN apk add --no-cache libzip-dev oniguruma-dev sqlite-dev \
    && docker-php-ext-install pdo pdo_sqlite bcmath mbstring zip
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --no-scripts --prefer-dist

COPY . .
COPY --from=assets /app/public/build ./public/build

# Подготовка окружения и БД
RUN cp -n .env.example .env || true \
    && php artisan key:generate --force \
    && mkdir -p database && touch database/database.sqlite \
    && php artisan migrate --seed --force \
    && chmod -R 777 storage bootstrap/cache database

EXPOSE 8000
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]

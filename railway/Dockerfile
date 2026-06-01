FROM node:22-alpine AS frontend

WORKDIR /app

COPY frontend/package.json frontend/package-lock.json ./
RUN npm ci

COPY frontend/ ./
RUN npm run build

FROM composer:2 AS vendor

WORKDIR /app

COPY api/composer.json api/composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

COPY api/ ./
RUN composer dump-autoload --optimize --no-dev

FROM php:8.4-fpm-bookworm AS runtime

RUN apt-get update && apt-get install -y \
    nginx \
    supervisor \
    gettext-base \
    libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql pgsql pcntl bcmath \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/api

COPY --from=vendor /app /var/www/api
COPY --from=frontend /app/dist /var/www/frontend/dist

COPY railway/nginx.conf /etc/nginx/templates/default.conf.template
COPY railway/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY railway/start-web.sh /usr/local/bin/start-web.sh
COPY railway/run-worker.sh railway/run-worker-domain-checks.sh railway/run-scheduler.sh /usr/local/bin/

RUN chmod +x /usr/local/bin/start-web.sh /usr/local/bin/run-worker.sh \
    /usr/local/bin/run-worker-domain-checks.sh /usr/local/bin/run-scheduler.sh \
    && rm -f /etc/nginx/sites-enabled/default \
    && mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

ENV PORT=8080

EXPOSE 8080

CMD ["/usr/local/bin/start-web.sh"]

# syntax=docker/dockerfile:1

# ============================================================
# Build arguments
# ============================================================
# https://hub.docker.com/r/serversideup/php/tags?name=frankenphp
ARG SERVERSIDEUP_PHP_VERSION=8.5-frankenphp-trixie
# https://www.postgresql.org/support/versioning/
ARG POSTGRES_VERSION=17
ARG USER_ID=9999
ARG GROUP_ID=9999

# ============================================================
# Stage: vendor — composer deps + Wayfinder generation
# ============================================================
FROM serversideup/php:${SERVERSIDEUP_PHP_VERSION} AS vendor

USER root
ARG USER_ID
ARG GROUP_ID
RUN docker-php-serversideup-set-id www-data ${USER_ID}:${GROUP_ID} \
    && docker-php-serversideup-set-file-permissions --owner ${USER_ID}:${GROUP_ID}

WORKDIR /var/www/html

COPY --chown=www-data:www-data composer.json composer.lock ./
# Source needed to boot artisan for `wayfinder:generate`
COPY --chown=www-data:www-data artisan ./
COPY --chown=www-data:www-data bootstrap ./bootstrap
COPY --chown=www-data:www-data config ./config
COPY --chown=www-data:www-data routes ./routes
COPY --chown=www-data:www-data app ./app
COPY --chown=www-data:www-data database ./database
COPY --chown=www-data:www-data storage ./storage

# Full install (with dev) so Wayfinder/artisan can run
RUN composer install --no-interaction --no-plugins --no-scripts --prefer-dist
RUN mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views
RUN php artisan wayfinder:generate --with-form
# Re-install without dev for the production vendor dir
RUN composer install --no-dev --no-interaction --no-plugins --no-scripts --prefer-dist

USER www-data

# ============================================================
# Stage: assets — frontend build (client + SSR bundles)
# ============================================================
FROM oven/bun:latest AS assets

WORKDIR /app
COPY package.json bun.lock vite.config.ts ./
RUN bun install --frozen-lockfile

# App source (minus .dockerignore'd paths)
COPY . .
# Vendor (some vite plugins resolve from it) + generated Wayfinder files
COPY --from=vendor /var/www/html/vendor ./vendor
COPY --from=vendor /var/www/html/resources/js/actions ./resources/js/actions
COPY --from=vendor /var/www/html/resources/js/routes ./resources/js/routes
COPY --from=vendor /var/www/html/resources/js/wayfinder ./resources/js/wayfinder

# Skip the wayfinder vite plugin (no PHP here; files already generated)
ENV SKIP_WAYFINDER_GENERATE=true
# Builds client assets AND the SSR bundle (bootstrap/ssr/ssr.mjs)
RUN bun run build:ssr

# ============================================================
# Stage: standalone — production image, single entry point
# ============================================================
FROM serversideup/php:${SERVERSIDEUP_PHP_VERSION} AS standalone

ARG USER_ID
ARG GROUP_ID
ARG POSTGRES_VERSION

WORKDIR /var/www/html
USER root

RUN docker-php-serversideup-set-id www-data ${USER_ID}:${GROUP_ID} \
    && docker-php-serversideup-set-file-permissions --owner ${USER_ID}:${GROUP_ID}

# Redis extension for optional Redis cache/queue (pdo_pgsql ships in the image)
RUN install-php-extensions redis

# System packages + Bun (needed when SSR is toggled on: inertia:start-ssr --runtime=bun)
RUN apt-get update && apt-get install -y --no-install-recommends \
        postgresql-client-${POSTGRES_VERSION} \
        git \
        unzip \
        curl \
        jq \
    && rm -rf /var/lib/apt/lists/*
RUN curl -fsSL https://github.com/oven-sh/bun/releases/latest/download/bun-linux-x64.zip -o /tmp/bun.zip \
    && unzip /tmp/bun.zip -d /tmp \
    && mv /tmp/bun-linux-x64/bun /usr/local/bin/bun \
    && chmod 755 /usr/local/bin/bun \
    && rm -rf /tmp/bun.zip /tmp/bun-linux-x64

# serversideup runtime configuration knobs
ARG AUTORUN_ENABLED=true
ARG AUTORUN_LARAVEL_CONFIG_CACHE=true
ARG AUTORUN_LARAVEL_EVENT_CACHE=true
ARG AUTORUN_LARAVEL_ROUTE_CACHE=true
ARG AUTORUN_LARAVEL_VIEW_CACHE=true
ARG AUTORUN_LARAVEL_STORAGE_LINK=true
ARG PHP_OPCACHE_ENABLE=1
ARG SSL_MODE=off

ENV PHP_OPCACHE_ENABLE=${PHP_OPCACHE_ENABLE} \
    AUTORUN_ENABLED=${AUTORUN_ENABLED} \
    AUTORUN_LARAVEL_CONFIG_CACHE=${AUTORUN_LARAVEL_CONFIG_CACHE} \
    AUTORUN_LARAVEL_EVENT_CACHE=${AUTORUN_LARAVEL_EVENT_CACHE} \
    AUTORUN_LARAVEL_ROUTE_CACHE=${AUTORUN_LARAVEL_ROUTE_CACHE} \
    AUTORUN_LARAVEL_VIEW_CACHE=${AUTORUN_LARAVEL_VIEW_CACHE} \
    AUTORUN_LARAVEL_STORAGE_LINK=${AUTORUN_LARAVEL_STORAGE_LINK} \
    APP_BASE_DIR=/var/www/html \
    SSL_MODE=${SSL_MODE} \
    OCTANE_SERVER=frankenphp

# Entrypoint scripts (shared init runs first, then target-specific)
COPY --chmod=755 docker/shared/entrypoint.d/ /etc/entrypoint.d/
COPY --chmod=755 docker/standalone/entrypoint.d/ /etc/entrypoint.d/

# Application source
COPY --chown=www-data:www-data . .
# Production vendor + built assets on top (so source copies don't clobber them)
COPY --from=vendor --chown=www-data:www-data /var/www/html/vendor ./vendor
COPY --from=assets --chown=www-data:www-data /app/public/build ./public/build
COPY --from=assets --chown=www-data:www-data /app/bootstrap/ssr ./bootstrap/ssr
# node_modules needed for the SSR runtime when toggled on
COPY --from=assets --chown=www-data:www-data /app/node_modules ./node_modules

# Directory for the optional SQLite database (lives on a named volume at runtime)
RUN mkdir -p database/sqlite && chown -R www-data:www-data database/sqlite

RUN composer dump-autoload --no-plugins --no-scripts \
    && php artisan package:discover --ansi

USER www-data

# Default entry point: Octane (FrankenPHP worker mode) on serversideup's 8080
CMD ["php", "artisan", "octane:start", "--server=frankenphp", "--host=0.0.0.0", "--port=8080"]

# ============================================================
# Stage: all-in-one — supervisord runs web + worker + schedule + ssr
# ============================================================
# NOTE: serversideup's frankenphp image ships NO s6-overlay (s6 only exists in
# the fpm-nginx/fpm-apache variations). So single-container supervision uses
# supervisord, installed here. The serversideup ENTRYPOINT still runs the
# /etc/entrypoint.d/* init scripts before exec'ing this CMD.
FROM standalone AS all-in-one

USER root
RUN apt-get update && apt-get install -y --no-install-recommends supervisor \
    && rm -rf /var/lib/apt/lists/*
COPY docker/all-in-one/supervisord.conf /etc/supervisor/laravel.conf
USER www-data

CMD ["supervisord", "-c", "/etc/supervisor/laravel.conf"]

# docker/php/Dockerfile
# Clean, consistent multi-stage Laravel build

ARG PHP_VERSION=8.3
ARG INSTALL_XDEBUG=false

############################################
# 1. Base PHP Image
############################################
FROM php:${PHP_VERSION}-fpm AS php-base
WORKDIR /var/www/html

RUN apt-get update \
    && DEBIAN_FRONTEND=noninteractive apt-get install -y --no-install-recommends \
        ca-certificates curl git unzip gnupg2 \
        zlib1g-dev libzip-dev libpq-dev libpng-dev \
        libjpeg-dev libfreetype6-dev libonig-dev g++ \
    && rm -rf /var/lib/apt/lists/*

############################################
# 2. PHP Extensions
############################################
FROM php-base AS php-extensions
ARG INSTALL_XDEBUG

RUN apt-get update && apt-get install -y --no-install-recommends \
        libicu-dev libxml2-dev libgmp-dev libssl-dev uuid-dev \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install -j$(nproc) \
        pdo pdo_pgsql bcmath mbstring exif pcntl zip intl gmp soap dom xml xmlwriter opcache

RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd

RUN pecl channel-update pecl.php.net \
    && pecl install redis apcu \
    && docker-php-ext-enable redis apcu

RUN if [ "$INSTALL_XDEBUG" = "true" ]; then \
        pecl install xdebug && docker-php-ext-enable xdebug; \
    else echo "Skipping Xdebug installation"; fi

RUN apt-get purge -y --auto-remove g++ \
    && rm -rf /tmp/* /var/tmp/*

############################################
# 3. Composer Dependencies (cached)
############################################
FROM php-extensions AS composer-deps

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY src/composer.json src/composer.lock ./
RUN composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader --no-scripts

############################################
# 4. Node Dependencies (cached)
############################################
FROM php-extensions AS node-deps

# Install Node 20 + npm
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && node -v && npm -v

WORKDIR /var/www/html

# Copy package.json + package-lock.json from source
COPY src/package.json src/package-lock.json ./

# Install all devDependencies locally (includes Vite)
RUN npm install --silent --no-audit

# Ensure Vite CLI is globally available
RUN npm install -g vite@^7.0.7 --silent \
    && ln -sf $(npm root -g)/vite/bin/vite.js /usr/local/bin/vite

############################################
# 5. Application Build (Composer + Node)
############################################
FROM node-deps AS app-build
WORKDIR /var/www/html

# Copy Composer deps
COPY --from=composer-deps /var/www/html/vendor ./vendor

# Copy Node deps
COPY --from=node-deps /var/www/html/node_modules ./node_modules

# Copy full Laravel source
COPY src/ ./

# Frontend build (Vite)
RUN if [ -f package.json ] && grep -q "build" package.json; then \
        npm run build --if-present; \
    else echo "No frontend build needed"; fi

# Optimize autoload + cached config (for production)
RUN composer dump-autoload --optimize --classmap-authoritative || true
RUN php artisan package:discover --ansi || true

ARG APP_ENV=production
RUN if [ "$APP_ENV" = "production" ]; then \
        php artisan config:cache && php artisan route:cache && php artisan view:cache || true; \
    else echo "Skipping artisan cache generation"; fi

############################################
# 6. Production Runtime
############################################
FROM php:${PHP_VERSION}-fpm AS laravel-production
ARG UID=1000
ARG GID=1000

# Create app user
RUN groupadd -g ${GID} laravel \
    && useradd -u ${UID} -g ${GID} -m -s /bin/bash laravel

WORKDIR /var/www/html

# Bring in built application
COPY --from=app-build /var/www/html /var/www/html

# Fix permissions
RUN chown -R laravel:laravel /var/www/html \
    && find /var/www/html -type f -exec chmod 0640 {} \; \
    && find /var/www/html -type d -exec chmod 0750 {} \; \
    && chmod -R 0755 /var/www/html/public

COPY docker/php/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

USER laravel

EXPOSE 9000
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm"]


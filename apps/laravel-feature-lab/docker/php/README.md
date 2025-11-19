# PHP Docker Setup for Laravel Feature Lab

This folder contains the Docker setup for the Laravel application, designed with **multi-stage builds and layered caching** for fast rebuilds, modular development, and clear separation of production vs development artifacts.

## Layer Overview

The Dockerfile is structured in layers to maximize caching and speed:

1. **PHP Base Layer (`php-base`)**
   - Starts from the official PHP FPM image.
   - Installs essential system dependencies: `curl`, `git`, `unzip`, `gnupg2`, `libpng-dev`, `libjpeg-dev`, `libfreetype6-dev`, `libzip-dev`, `libonig-dev`, `zlib1g-dev`, `libpq-dev`, `g++`.
   - Cached independently, rebuilt only when base packages or PHP version changes.

2. **PHP Extensions Layer (`php-extensions`)**
   - Installs all required PHP extensions: `pdo`, `pdo_pgsql`, `bcmath`, `mbstring`, `exif`, `pcntl`, `zip`, `intl`, `gmp`, `soap`, `dom`, `xml`, `xmlwriter`, `opcache`, `gd`.
   - Installs PECL extensions: `redis`, `apcu`.
   - Optional Xdebug support via build ARG `INSTALL_XDEBUG=true`.
   - Cleans up build dependencies to keep image lean.

3. **Composer Dependencies Layer (`composer-deps`)**
   - Copies `composer.json` and `composer.lock`.
   - Installs PHP packages with `composer install --no-dev --optimize-autoloader --no-scripts`.
   - Cached independently; rebuilt only when PHP package dependencies change.
   - **Useful for debugging**: rebuild this layer only if PHP packages change without touching Node modules or app source.

4. **Node.js Dependencies Layer (`node-deps`)**
   - Uses `package.json` and `package-lock.json` to install frontend dependencies via `npm ci`.
   - Cached independently to avoid reinstalling node modules on unrelated changes.
   - **Useful for debugging**: rebuild this layer only if frontend dependencies change, without affecting PHP layers.

5. **App Build Layer (`app-build`)**
   - Installs Node + npm inside PHP image (for debugging and frontend builds).
   - Copies Composer `vendor/` and Node `node_modules/` from their respective layers.
   - Copies application source code.
   - Runs frontend build (`npm run build`) if applicable.
   - Optimizes Composer autoloader and runs `php artisan package:discover`.
   - Prepares Laravel caches (`config:cache`, `route:cache`, `view:cache`) in production mode (`APP_ENV=production`).
   - Combines both cached dependency layers for a fully built application.

6. **Production Runtime (`laravel-production`)**
   - Copies only the built artifacts from `app-build`.
   - Creates a `laravel` user for container runtime.
   - Sets secure file and directory permissions.
   - Entrypoint is set to `/usr/local/bin/entrypoint.sh`.

## Debugging / Development Notes

- Enable Xdebug in development via `ARG INSTALL_XDEBUG=true` in Docker build.
- `DEBUG=true` can be used in `.env` or build arguments to include dev tools and verbose logs.
- Production images remain small and lean without dev/debug tools installed.
- **Layered caching strategy for debugging:**
  - To rebuild **only PHP packages**:  
    ```bash
    docker build --target composer-deps -t laravel-composer-deps -f docker/php/Dockerfile .
    ```
  - To rebuild **only Node modules**:  
    ```bash
    docker build --target node-deps -t laravel-node-deps -f docker/php/Dockerfile .
    ```
  - To rebuild the full app with updated source but using cached dependencies:  
    ```bash
    docker build --target app-build -t laravel-app-build -f docker/php/Dockerfile .
    ```

## Benefits

- **Incremental rebuilds**: Only layers affected by changes are rebuilt.
- **Separation of concerns**: Production images contain only runtime artifacts; dev tools remain in separate build layers.
- **Caching**: Composer, Node modules, and PHP extensions are cached independently to reduce rebuild times.
- **Debugging-friendly**: Node is installed in `app-build`, allowing `npm run build` or other frontend scripts for development/debugging.
- **Security**: Production layer has restricted permissions and only necessary artifacts.
- **Rapid deployment**: Prebuilt artifacts reduce downtime when scaling or replacing containers.

## Rebuild Rules

- Rebuild `php-base` when system dependencies or PHP version change.
- Rebuild `php-extensions` when PHP extensions or PECL packages change.
- Rebuild `composer-deps` when PHP package dependencies change.
- Rebuild `node-deps` when frontend packages change.
- Rebuild `app-build` when application code or frontend build scripts change.


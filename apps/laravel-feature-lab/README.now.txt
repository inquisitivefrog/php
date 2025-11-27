
tools
1. pecl
2. gd
3. intl
4. pdo_pgsql
5. zip
6. exif
7. bcmath
8. gmp
9. pcntl
10. redis
11. opcache
---------------------------------------------------
docker compose down -v
docker compose build --no-cache
docker compose up -d app nginx db
docker compose build --no-cache app
docker compose run --rm npm-builder
tail -f logs/laravel.log
docker compose exec app bash
# In .env or export
export DEBUG=true
docker compose build --no-cache app
docker compose up -d
---------------------------------------------------
# 1. PHP base + system dependencies
docker build --target builder -t laravel-base -f docker/php/Dockerfile .

# 2. PHP extensions only
docker build --target builder -t laravel-php-ext -f docker/php/Dockerfile .

# 3. Composer dependencies
docker build --target builder -t laravel-composer -f docker/php/Dockerfile .

# 4. Node vendor packages
docker build --target builder -t laravel-node-vendor -f docker/php/Dockerfile .

# 5. Node modules / final build
docker build --target builder -t laravel-builder -f docker/php/Dockerfile .

# 6. Production image
docker build --target production -t laravel-production -f docker/php/Dockerfile .

---------------------------------------------------
docker build --no-cache --target php-base -t laravel-php-base -f docker/php/Dockerfile .
docker build --no-cache --target php-extensions -t laravel-php-ext -f docker/php/Dockerfile .
docker build --no-cache --target composer-deps -t laravel-composer -f docker/php/Dockerfile .
docker build --no-cache --target node-deps -t laravel-node-deps -f docker/php/Dockerfile .
docker build --no-cache --target app-build -t laravel-app-build -f docker/php/Dockerfile .
docker build --no-cache --target laravel-prod -f docker/php/Dockerfile .
docker build --no-cache --target production -t laravel-production -f docker/php/Dockerfile .

---------------------------------------------------
docker build --target php-base \
  -t laravel-php-base \
  -f docker/php/Dockerfile .

docker build --target php-extensions \
  -t laravel-php-extensions \
  -f docker/php/Dockerfile .

docker build --target composer-deps \
  -t laravel-composer-deps \
  -f docker/php/Dockerfile .

docker build --target node-deps \
  -t laravel-node-deps \
  -f docker/php/Dockerfile .

docker build --target app-build \
  -t laravel-app-build \
  -f docker/php/Dockerfile .

docker build --target laravel-production \
  -t laravel-production \
  -f docker/php/Dockerfile .

---------------------------------------------------
make up
make down
make artisan migrate
make npm-dev
make prod-up
make clean
----------------------------

# save
cp docker/php/Dockerfile docker/php/Dockerfile.bak
cp docker/php/entrypoint.sh docker/php/entrypoint.sh.bak
cp docker/healthcheck/check.sh docker/healthcheck/check.sh.bak
cp src/routes/web.php src/routes/web.php.bak

# rebuild
docker compose down -v
docker compose build --no-cache
docker compose up -d

# verify
docker compose ps
docker compose logs -f app
docker compose exec app php artisan migrate:status
docker compose exec app php artisan route:list
docker compose exec app curl http://localhost/health

su laravel -c "php /var/www/html/artisan config:clear"
    su laravel -c "php /var/www/html/artisan cache:clear"
    su laravel -c "php /var/www/html/artisan route:clear"
    su laravel -c "php /var/www/html/artisan view:clear"

    su laravel -c "php /var/www/html/artisan config:cache"
    su laravel -c "php /var/www/html/artisan route:cache"
    su laravel -c "php /var/www/html/artisan view:cache"

# undo
mv docker/php/Dockerfile.bak docker/php/Dockerfile
mv docker/php/entrypoint.sh.bak docker/php/entrypoint.sh
mv docker/healthcheck/check.sh.bak docker/healthcheck/check.sh
mv src/routes/web.php.bak src/routes/web.php
docker compose build --no-cache
docker compose up -d

----------------------
# Test
nc -vz localhost 8080
nc -vz localhost 5173
curl -I http://localhost:5173/@vite/client
curl -I http://localhost:5173/resources/js/app.js


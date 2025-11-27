
Linux PHP splits configuration into many "modular" .ini files
/usr/local/etc/php/php.ini                  (main file)
/usr/local/etc/php/conf.d/*.ini             (module configs)
Each extension installed via docker-php-ext-install automatically generates its own .ini.

PHP ini file examples
10-mysqli.ini
20-opcache.ini
25-render.ini
docker-php-ext-bcmath.ini
docker-php-ext-intl.ini

if supply your own, <my_project>/docker/php/custom.ini, docker copies them into /usr/local/etc/php/conf.d/
<my_project>/docker/php/php.ini mandatory to ensure PHP is version controlled

% docker compose -f docker-compose.yml -f docker-compose.prod.yml up -d --build

This file overrides development settings
It does not duplicate full configuration — only overrides or additions needed for production.

% docker compose -f docker-compose.yml -f docker-compose.xdebug.yml up -d --build

This file overrides development settings
only overrides the app (PHP) service to enable Xdebug and attach to your IDE
used for local debugging and profiling
Using a separate override file is a best practice so you avoid accidentally deploying Xdebug.

% docker compose --profile build up --build

In production, you want Vite to compile static assets
assets end up in public/build/

% cp .env.example .env
% vi .env (add secrets manually)

This file must never be committed to Git.
used by `php artisan key:generate --env-file=.env`

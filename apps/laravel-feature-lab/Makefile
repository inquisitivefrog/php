# Makefile root

# --- Paths ---
ENV_FILE=.env
DB_PASS_FILE=secrets/postgres_password.txt
APP_KEY_FILE=secrets/app_key.txt

# --- Core Commands ---

.PHONY: up down restart logs up-xdebug down-xdebug logs-xdebug

up:
	docker compose up -d

down:
	docker compose down

restart:
	docker compose down && docker compose up -d

logs:
	docker compose logs -f

up-xdebug:
	docker compose -f docker-compose.yml -f docker-compose.xdebug.yml up -d --build

down-xdebug:
	docker compose -f docker-compose.yml -f docker-compose.xdebug.yml down

logs-xdebug:
	docker compose -f docker-compose.yml -f docker-compose.xdebug.yml logs -f

# --- Secret Sync (Option C) ---

.PHONY: db-password app-key

db-password:
	@if [ ! -f $(DB_PASS_FILE) ]; then \
		echo "ERROR: $(DB_PASS_FILE) not found!"; exit 1; \
	fi
	@if [ ! -f $(ENV_FILE) ]; then \
		echo "Creating $(ENV_FILE)..."; cp .env.example $(ENV_FILE); \
	fi
	@echo "Updating DB_PASSWORD in $(ENV_FILE) using $(DB_PASS_FILE)"
	@sed -i "s/^DB_PASSWORD=.*/DB_PASSWORD=$$(cat $(DB_PASS_FILE))/" $(ENV_FILE)

app-key:
	@if [ ! -f $(ENV_FILE) ]; then \
		echo "$(ENV_FILE) missing, creating from example..."; cp .env.example $(ENV_FILE); \
	fi
	@if ! grep -q '^APP_KEY=' $(ENV_FILE); then \
		echo "APP_KEY missing in $(ENV_FILE), injecting secret file..."; \
		echo "APP_KEY=$$(cat $(APP_KEY_FILE))" >> $(ENV_FILE); \
	else echo "APP_KEY already present."; fi

# --- Laravel Helpers ---

.PHONY: artisan migrate migrate-fresh tinker queue

artisan:
	docker compose exec app php artisan $(cmd)

migrate:
	docker compose exec app php artisan migrate

migrate-fresh:
	docker compose exec app php artisan migrate:fresh --seed

tinker:
	docker compose exec app php artisan tinker

queue:
	docker compose exec app php artisan queue:work

# --- Composer / NPM inside container ---

.PHONY: composer npm build-frontend

composer:
	docker compose exec app composer $(cmd)

npm:
	docker compose exec app npm $(cmd)

build-frontend:
	docker compose exec app npm run build

# --- Utility ---

.PHONY: bash
bash:
	docker compose exec app bash

.PHONY: test stan fix cs pcf sail-up sail-down

# Run PHPStan
stan:
	vendor/bin/phpstan analyse

# Run PHPUnit
test:
	vendor/bin/phpunit

# Pint auto-formatting
pint:
	vendor/bin/pint

# PHP-CS-Fixer
pcf:
	vendor/bin/php-cs-fixer fix

# PHP CodeSniffer
cs:
	vendor/bin/phpcs

# Docker up/down via Sail
sail-up:
	./vendor/bin/sail up -d

sail-down:
	./vendor/bin/sail down

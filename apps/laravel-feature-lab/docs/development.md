# Development Workflow

## Recommended Container
Use the **workspace** container for all development commands.

### Features
- PHP, Composer, Node, npm, Vite installed
- Bind-mounted project directory (`./src:/var/www/html`) keeps host and container in sync
- Safe to stop/recreate container without losing generated files

### Enter Workspace
```bash
docker compose exec workspace bash

Common Commands

Artisan
php artisan migrate
php artisan make:model Cow -m
php artisan make:controller CowController --api
php artisan make:request CowStoreRequest
php artisan make:request CowUpdateRequest
php artisan make:resource CowResource
php artisan make:seeder CowSeeder

Composer
composer install
composer require package/name

Node/Vite
npm install
npm run dev
vite

Notes
All generated files appear on host immediately.
Stopping or recreating containers does not erase generated files.


---

### **`docs/SCRUD-example.md`**
```markdown
# SCRUD Example Module

Demonstrates **Search / Create / Read / Update / Delete (SCRUD)** functionality using Laravel 11 standards.

## Generated Files Structure

src/
└── app/
├── Http/
│ ├── Controllers/
│ │ └── CowController.php
│ └── Requests/
│ ├── CowStoreRequest.php
│ └── CowUpdateRequest.php
├── Models/
│ └── Cow.php
└── Http/
└── Resources/
└── CowResource.php
database/
└── migrations/
└── xxxx_xx_xx_xxxxxx_create_cows_table.php
└── seeders/
└── CowSeeder.php


## Component Responsibilities
- **Model (Cow.php):** Eloquent ORM database access
- **Migration:** Creates `cows` table
- **Controller:** SCRUD endpoints (`index`, `store`, `show`, `update`, `destroy`)
- **Form Requests:** Validate POST/PATCH data
- **API Resource:** Shape JSON responses
- **Seeder:** Load sample data

## Running Commands
All commands run inside the **workspace container**:

```bash
docker compose exec workspace bash
php artisan make:model Cow -m
php artisan make:controller CowController --api
php artisan make:request CowStoreRequest
php artisan make:request CowUpdateRequest
php artisan make:resource CowResource
php artisan make:seeder CowSeeder


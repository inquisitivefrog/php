# PHP Artisan Commands Guide

## What is `php artisan`?

`php artisan` is Laravel's command-line interface (CLI) tool. It provides hundreds of commands to help you:
- Manage your application
- Run migrations
- Clear caches
- Generate code
- Publish configuration files
- And much more!

Think of it like `npm` for Node.js or `rails` for Ruby on Rails.

---

## Understanding `config:publish`

### What It Does

The `config:publish` command copies configuration files from Laravel packages into your `config/` directory so you can customize them.

**Why?** 
- Laravel packages come with default configuration files
- These defaults are stored inside the `vendor/` directory (not editable)
- Publishing copies them to `config/` where you can modify them

### Command Syntax

```bash
php artisan config:publish [name]
```

**Options:**
- `--all` - Publish all available configuration files
- `--force` - Overwrite existing files (use with caution!)

---

## Understanding the Results

### ✅ `INFO Published 'broadcasting' configuration file.`

**Meaning:** Success! The file was copied from the package to your `config/` directory.

**What happened:**
- The `broadcasting.php` file didn't exist in your `config/` directory
- Laravel copied it from the package's default configuration
- You can now edit `config/broadcasting.php` to customize broadcasting settings

**File created:** `src/config/broadcasting.php`

### ❌ `ERROR The 'app' configuration file already exists.`

**Meaning:** The file already exists in your `config/` directory, so Laravel won't overwrite it.

**Why this happens:**
- `app.php` is a core Laravel config file that's always created during installation
- It already exists, so there's nothing to publish
- This is **normal and expected** for core config files

**What to do:**
- Nothing! The file is already there and working
- If you want to see the default version, use `--force` (but this will overwrite your customizations!)

### ✅ `INFO Published 'concurrency' configuration file.`

**Meaning:** Success! A new configuration file was published.

**What happened:**
- The `concurrency.php` file didn't exist before
- Laravel copied it from a package (likely Laravel 11's concurrency feature)
- You can now customize concurrency settings

**File created:** `src/config/concurrency.php`

---

## Your Current Config Files

Based on your `config/` directory, you have these configuration files:

### Core Laravel Configs (Always Present)
- ✅ `app.php` - Application settings
- ✅ `auth.php` - Authentication configuration
- ✅ `cache.php` - Cache settings
- ✅ `database.php` - Database connections
- ✅ `filesystems.php` - File storage
- ✅ `logging.php` - Logging configuration
- ✅ `mail.php` - Email settings
- ✅ `queue.php` - Queue configuration
- ✅ `session.php` - Session settings
- ✅ `services.php` - Third-party service configs

### Package-Specific Configs (Published)
- ✅ `broadcasting.php` - **Just published!**
- ✅ `cashier.php` - Stripe/Cashier settings
- ✅ `concurrency.php` - **Just published!**
- ✅ `cors.php` - CORS settings
- ✅ `horizon.php` - Horizon queue dashboard
- ✅ `pennant.php` - Feature flags
- ✅ `sanctum.php` - API authentication
- ✅ `scout.php` - Search engine settings
- ✅ `telescope.php` - Debugging/monitoring
- ✅ `tinker.php` - REPL settings

---

## Common Artisan Commands

Here are some useful commands to explore:

### View All Commands
```bash
php artisan list
```

### Get Help on Any Command
```bash
php artisan help [command-name]
```

### Configuration Commands
```bash
php artisan config:clear      # Clear config cache
php artisan config:cache       # Cache config for performance
php artisan config:publish     # Publish package configs
```

### Cache Commands
```bash
php artisan cache:clear        # Clear application cache
php artisan cache:table       # Create cache table migration
```

### Database Commands
```bash
php artisan migrate            # Run migrations
php artisan migrate:status    # Check migration status
php artisan db:seed           # Seed database
```

### Queue Commands
```bash
php artisan queue:work        # Process queued jobs
php artisan queue:listen      # Listen for queued jobs
php artisan queue:failed     # List failed jobs
```

### Schedule Commands
```bash
php artisan schedule:list     # List scheduled tasks
php artisan schedule:work     # Run scheduler
php artisan schedule:test     # Test scheduled tasks
```

---

## Best Practices

1. **Don't use `--force` unless necessary** - It will overwrite your customizations
2. **Review published configs** - Check what changed after publishing
3. **Version control your configs** - Commit `config/` files to git
4. **Use environment variables** - Override configs via `.env` file when possible

---

## Understanding `package:discover`

### What It Does

The `package:discover` command scans all installed Composer packages and discovers their **service providers**. It then caches this information so Laravel knows which packages are installed and which providers to load.

**Why?**
- When you install a package via Composer, Laravel needs to know about it
- Packages register themselves through service providers
- This command rebuilds the cache of discovered packages
- Laravel uses this cache to automatically load package features

### Command Syntax

```bash
php artisan package:discover
```

**What it does:**
1. Scans `vendor/` directory for installed packages
2. Looks for package service providers (defined in `composer.json`)
3. Caches the list in `bootstrap/cache/packages.php`
4. Shows you which packages were discovered

### Understanding the Results

When you run `package:discover`, you'll see output like:

```
INFO  Discovering packages.

laravel/breeze ........................................................ DONE
laravel/cashier ........................................................ DONE
laravel/horizon ........................................................ DONE
...
```

**What this means:**
- Each package listed was **successfully discovered**
- `DONE` means Laravel found and registered the package's service provider
- These packages are now available to your application

### Packages Discovered in Your Project

Based on your output, these packages are installed and registered:

#### Core Laravel Packages
- ✅ **laravel/breeze** - Authentication scaffolding
- ✅ **laravel/cashier** - Stripe subscription management
- ✅ **laravel/horizon** - Queue dashboard
- ✅ **laravel/pail** - Log viewer
- ✅ **laravel/pennant** - Feature flags
- ✅ **laravel/sail** - Docker development environment
- ✅ **laravel/sanctum** - API authentication
- ✅ **laravel/scout** - Full-text search
- ✅ **laravel/telescope** - Application debugging
- ✅ **laravel/tinker** - REPL (interactive shell)

#### Supporting Packages
- ✅ **nesbot/carbon** - Date/time handling
- ✅ **nunomaduro/collision** - Error handler
- ✅ **nunomaduro/termwind** - Terminal styling

### When to Run This Command

**You typically DON'T need to run this manually** because:
- Laravel runs it automatically after `composer install` or `composer update`
- It's part of the normal package installation process

**Run it manually if:**
- You manually edited `composer.json` and want to refresh the cache
- You suspect packages aren't being discovered
- You're debugging package registration issues
- You want to see what packages are currently discovered

### What Gets Cached

The command creates/updates:
- `bootstrap/cache/packages.php` - Cached list of discovered packages

You can view this file to see what Laravel knows about your packages, but you typically don't need to edit it manually.

### Related Commands

```bash
# Clear all caches (including package discovery cache)
php artisan optimize:clear

# Rebuild all caches (including package discovery)
php artisan optimize

# View installed packages
composer show
```

---

## Understanding `route:list`

### What It Does

The `route:list` command displays all registered routes in your Laravel application. It shows:
- HTTP methods (GET, POST, PUT, DELETE, etc.)
- Route URIs (paths)
- Route names
- Controller actions
- Middleware (if verbose)

### Command Syntax

```bash
php artisan route:list
```

**Useful Options:**
- `--method=GET` - Show only GET routes
- `--path=api` - Show only routes matching a path pattern
- `--except-vendor` - Hide vendor package routes (Horizon, Telescope, etc.)
- `--json` - Output as JSON
- `--sort=method` - Sort by method, name, uri, etc.

### Understanding Your Route List

Your application has **138 routes** total. Here's a breakdown:

#### Your Application Routes (~50 routes)

**Cow CRUD (5 routes):**
- `GET|HEAD api/cows` - List cows
- `POST api/cows` - Create cow
- `GET|HEAD api/cows/{cow}` - Show cow
- `PUT|PATCH api/cows/{cow}` - Update cow
- `DELETE api/cows/{cow}` - Delete cow

**Feature Flags (9 routes):**
- `GET api/demo/*` - Various feature flag demos

**Notifications (7 routes):**
- `POST api/notifications/*` - Send various notifications
- `GET api/notifications/stats` - Notification statistics

**Queue/Horizon (6 routes):**
- `POST api/queue/*` - Dispatch various job types
- `GET api/queue/stats` - Queue statistics

**Scout/Search (8 routes):**
- `POST api/scout-demo/search/*` - Various search operations
- `GET api/scout-demo/stats` - Search statistics

**Subscriptions/Cashier (5 routes):**
- `GET|POST api/subscription/*` - Subscription management

**Telescope Demo (10 routes):**
- `GET|POST api/telescope-demo/*` - Various Telescope demos

**Authentication (8 routes):**
- `POST api/register`, `api/login`, `api/logout`
- `POST api/forgot-password`, `api/reset-password`
- Email verification routes

#### Vendor Package Routes (~88 routes)

**Horizon (~30 routes):**
- `GET horizon/{view?}` - Horizon dashboard
- `GET|POST horizon/api/*` - Horizon API endpoints

**Telescope (~50 routes):**
- `GET telescope/{view?}` - Telescope dashboard
- `POST telescope/telescope-api/*` - Telescope API endpoints

**Cashier (2 routes):**
- `GET stripe/payment/{id}` - Payment page
- `POST stripe/webhook` - Stripe webhook

**Sanctum (1 route):**
- `GET sanctum/csrf-cookie` - CSRF cookie

### Useful Filtering Examples

```bash
# Show only API routes (your application)
php artisan route:list --path=api

# Show only GET routes
php artisan route:list --method=GET

# Hide vendor routes (cleaner view of your routes)
php artisan route:list --except-vendor

# Show only your application routes
php artisan route:list --except-vendor --path=api

# Output as JSON
php artisan route:list --json
```

### Route Information Format

Each route shows:
```
METHOD  URI  NAME  ACTION
```

**Example:**
```
POST  api/cows  cows.store  CowController@store
```

- **POST** - HTTP method
- **api/cows** - Route URI
- **cows.store** - Route name (for `route('cows.store')`)
- **CowController@store** - Controller and method

---

## Next Steps

Try these commands to explore:

```bash
# See all available commands
docker compose run --rm workspace php artisan list

# Get help on a specific command
docker compose run --rm workspace php artisan help migrate

# View your published configs
ls -la src/config/

# See what packages are installed
docker compose run --rm workspace composer show

# List routes with filters
docker compose run --rm workspace php artisan route:list --except-vendor
docker compose run --rm workspace php artisan route:list --method=GET
docker compose run --rm workspace php artisan route:list --path=api
```


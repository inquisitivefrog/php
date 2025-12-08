# Understanding `schema:dump` Command

## What It Does

The `schema:dump` command creates a SQL file containing your database schema (structure) without the data. This is useful for:

- **Version Control**: Store your database structure in git
- **Faster Migrations**: Load schema directly instead of running all migrations
- **Documentation**: See the complete database structure in one file
- **Testing**: Quick database setup for tests

### Command Syntax

```bash
php artisan schema:dump
```

**Options:**
- `--database=[name]` - Specify which database connection to use
- `--path=[path]` - Custom path for the dump file
- `--prune` - Delete all existing migration files (use with caution!)

**Output:**
- Creates `database/schema/pgsql-schema.sql` (for PostgreSQL)
- Contains CREATE TABLE, CREATE INDEX, etc. statements
- No data, just structure

---

## Why It Failed

### Error Message

```
sh: 1: pg_dump: not found
```

### Root Cause

The `schema:dump` command uses PostgreSQL's `pg_dump` utility to extract the schema. However, your workspace container doesn't have PostgreSQL client tools installed.

**What's installed:**
- ✅ PostgreSQL PHP extension (`pdo_pgsql`) - for connecting to database
- ✅ PostgreSQL development libraries (`libpq-dev`) - for compiling PHP extension
- ❌ PostgreSQL client tools (`pg_dump`, `psql`, etc.) - **NOT installed**

**Why this matters:**
- PHP extensions let your **application** connect to PostgreSQL
- Client tools let you **dump/restore** databases from the command line
- `schema:dump` needs the client tools, not just the PHP extension

---

## Solutions

### Option 1: Install PostgreSQL Client Tools (Recommended)

Add PostgreSQL client tools to your Docker image.

**Services that will be updated:**
- ✅ **workspace** - Development container (where you run artisan commands) - **PRIMARY TARGET**
- ✅ **queue** - Queue worker container
- ✅ **scheduler** - Scheduler container
- ⚠️ **app** - PHP-FPM container (uses `laravel-production` target - will include it, but typically not needed in production)

**Edit `docker/php/Dockerfile`:**

Find the section where packages are installed (around line 13-18) and add `postgresql-client`:

```dockerfile
RUN apt-get update \
    && DEBIAN_FRONTEND=noninteractive apt-get install -y --no-install-recommends \
        ca-certificates curl git unzip gnupg2 \
        zlib1g-dev libzip-dev libpq-dev libpng-dev \
        libjpeg-dev libfreetype6-dev libonig-dev g++ \
        postgresql-client \  # <-- Add this line
    && rm -rf /var/lib/apt/lists/*
```

**Then rebuild the affected services:**
```bash
# Rebuild all services using the PHP Dockerfile
docker compose build workspace queue scheduler

# Or rebuild everything (slower but ensures consistency)
docker compose build
```

**Now the command will work:**
```bash
docker compose run --rm workspace php artisan schema:dump
```

**Important Notes:**
- Since the Dockerfile uses multi-stage builds, adding `postgresql-client` to the base stage (line 13-18) will make it available to **all** build targets, including `laravel-production`
- This means the `app` service will also have `pg_dump`, even though it's typically not needed in production
- If you want to exclude it from production, you'd need a more complex Dockerfile structure (not recommended for this use case)
- For development purposes, having it in all containers is fine and harmless

### Option 2: Run `pg_dump` from Postgres Container

Use the PostgreSQL container which already has `pg_dump`:

```bash
# Connect to postgres container and dump schema
docker compose exec postgres pg_dump \
    --no-owner \
    --no-acl \
    --schema-only \
    -U laravel \
    -d laravel \
    > database/schema/pgsql-schema.sql
```

**Note:** This creates the file on your host machine, not inside the container.

### Option 3: Use Migrations Instead

If you don't need schema dumps, you can continue using migrations:

```bash
# View migration status
php artisan migrate:status

# Run migrations
php artisan migrate

# Rollback migrations
php artisan migrate:rollback
```

---

## What Gets Created

After running `schema:dump` successfully, you'll have:

**File:** `database/schema/pgsql-schema.sql`

**Contents:**
```sql
-- PostgreSQL database dump
-- Schema only (no data)

CREATE TABLE users (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    ...
);

CREATE INDEX users_email_index ON users(email);
-- ... etc
```

**Benefits:**
- Fast database setup: `php artisan schema:load` loads this instantly
- No need to run all migrations from scratch
- Version controlled database structure

---

## Related Commands

```bash
# Dump schema
php artisan schema:dump

# Load schema (faster than running migrations)
php artisan schema:load

# View migration status
php artisan migrate:status

# Run migrations normally
php artisan migrate
```

---

## Recommendation

**For your project:** Install `postgresql-client` in the Docker image (Option 1) so you can use `schema:dump` when needed. It's a small addition that enables useful database management features.

